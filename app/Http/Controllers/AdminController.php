<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\Registered;

class AdminController extends Controller
{
    // ── User Management ──────────────────────────────

    public function usersIndex()
    {
        $this->authorize('manage-users');
        $users = User::with('facility')->paginate(20);
        $facilities = Facility::all();

        return view('admin.users', compact('users', 'facilities'));
    }

    public function usersStore(Request $request)
    {
        $this->authorize('manage-users');
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'role' => ['required', Rule::in(UserRole::assignableValues())],
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id', Rule::requiredIf($request->input('role') === UserRole::STAFF->value)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'facility_id' => $validated['facility_id'] ?? null,
            'is_active' => true,
        ]);

        AuditService::log('user_created', "User #{$user->id} created with role {$user->role}");
        event(new Registered($user));

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function usersUpdate(Request $request, User $user)
    {
        $this->authorize('manage-users');
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(UserRole::assignableValues())],
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id', Rule::requiredIf($request->input('role') === UserRole::STAFF->value)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $user->name;
        $emailChanged = $user->email !== $validated['email'];

        // Prevent changing the role of existing system admins.
        $data = $validated;
        if ($user->id === $request->user()->id || ($user->role === 'admin' && User::where('role', 'admin')->where('is_active', true)->count() === 1)) {
            // Ensure role remains 'admin' regardless of submitted value
            $data['role'] = $user->role;
            $data['is_active'] = true;
        }

        $user->update($data);

        if ($user->wasChanged('is_active') && ! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null, 'remember_token' => null])->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            event(new Registered($user));
        }

        AuditService::log('user_updated', "User {$oldName} updated to {$user->name} ({$user->email}) and role {$user->role}");

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function usersDestroy(User $user)
    {
        $this->authorize('manage-users');
        abort_if($user->id === auth()->id(), 422, 'You cannot deactivate your own account.');
        abort_if($user->role === 'admin' && User::where('role', 'admin')->where('is_active', true)->count() === 1, 422, 'The last active admin cannot be deactivated.');
        $name = $user->name;
        $user->update(['is_active' => false, 'remember_token' => null]);
        DB::table('sessions')->where('user_id', $user->id)->delete();

        AuditService::log('user_deactivated', "User {$name} deactivated");

        return redirect()->back()->with('success', 'User deactivated successfully.');
    }

    public function usersResetPassword(User $user)
    {
        $this->authorize('manage-users');

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->back()->with('error', 'The reset link could not be sent. Check the mail configuration and try again.');
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()->back()->with('error', 'The reset link could not be sent. Please try again.');
        }

        AuditService::log('user_password_reset_requested', "Password reset link requested for user #{$user->id}");

        return redirect()->back()->with('success', 'If mail delivery is configured, a password reset link has been sent.');
    }

    public function usersChangePassword(Request $request, User $user)
    {
        $this->authorize('manage-users');
        $validated = $request->validateWithBag('changePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'change_password_user_id' => ['required', 'integer', Rule::in([$user->id])],
            'change_password_user_name' => ['required', 'string', 'max:255'],
        ]);

        $isOwnPassword = $user->is($request->user());
        $currentSessionId = $request->session()->getId();

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => null,
        ])->save();

        $sessions = DB::table('sessions')->where('user_id', $user->id);
        if ($isOwnPassword) {
            $sessions->where('id', '!=', $currentSessionId);
        }
        $sessions->delete();

        if ($isOwnPassword) {
            $request->session()->regenerate();
        }

        AuditService::log(
            $isOwnPassword ? 'admin_own_password_changed' : 'user_password_changed',
            $isOwnPassword
                ? 'Administrator changed their own password from user management.'
                : "Administrator changed the password for user #{$user->id}."
        );

        return redirect()->back()->with('success', "Password updated successfully for {$user->name}.");
    }

    public function usersBulkResetPasswords(Request $request)
    {
        $this->authorize('manage-users');
        $validated = $request->validate([
            'user_ids' => 'required|array|max:50',
            'user_ids.*' => 'integer|distinct|exists:users,id'
        ]);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($validated['user_ids'] as $id) {
            $user = User::find($id);

            if (! $user || $user->id === auth()->id()) {
                continue;
            }

            try {
                $status = Password::sendResetLink(['email' => $user->email]);
                if ($status === Password::RESET_LINK_SENT) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $exception) {
                report($exception);
                $failedCount++;
            }
        }

        if ($sentCount > 0) {
            AuditService::log('bulk_password_reset_requested', "Bulk password reset links requested for {$sentCount} users");

            $message = "Password reset links sent to {$sentCount} users.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} could not be sent.";
            }

            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with(
            'error',
            $failedCount > 0
                ? 'No reset links could be sent. Check the mail configuration and try again.'
                : 'No valid users selected for password reset.'
        );
    }

    // ── Facility Management ──────────────────────────

    public function facilitiesIndex()
    {
        $this->authorize('manage-facilities');
        $facilities = Facility::withCount(['detainees as active_detainees_count' => function ($query) {
            $query->where('status', 'active');
        }])->paginate(20);

        return view('admin.facilities', compact('facilities'));
    }

    public function facilitiesStore(Request $request)
    {
        $this->authorize('manage-facilities');
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'capacity' => 'required|integer|min:1|max:1000000',
        ]);

        Facility::create($request->only(['name', 'region', 'address', 'capacity']));

        AuditService::log('facility_created', "Facility {$request->input('name')} created");

        return redirect()->back()->with('success', 'Facility created successfully.');
    }

    public function facilitiesUpdate(Request $request, Facility $facility)
    {
        $this->authorize('manage-facilities');
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'capacity' => 'required|integer|min:1|max:1000000',
        ]);

        $facility->update($request->only(['name', 'region', 'address', 'capacity']));

        AuditService::log('facility_updated', "Facility {$facility->name} updated");

        return redirect()->back()->with('success', 'Facility updated successfully.');
    }

    public function facilitiesDestroy(Facility $facility)
    {
        $this->authorize('manage-facilities');
        abort_if($facility->detainees()->exists() || $facility->users()->exists(), 422, 'A facility with associated records cannot be deleted.');
        $name = $facility->name;
        $facility->delete();

        AuditService::log('facility_deleted', "Facility {$name} deleted");

        return redirect()->back()->with('success', 'Facility deleted successfully.');
    }

    // ── Penalty Reference Management ─────────────────

    public function penaltiesIndex()
    {
        $this->authorize('manage-penalties');
        $penalties = PenaltyReference::paginate(20);

        return view('admin.penalties', compact('penalties'));
    }

    public function penaltiesStore(Request $request)
    {
        $this->authorize('manage-penalties');
        $request->validate([
            'rpc_code' => 'required|string|max:50|unique:penalty_references,rpc_code',
            'charge_name' => 'required|string|max:255',
            'max_penalty_years' => 'required|numeric|min:0|max:100',
            'max_penalty_months' => 'nullable|integer|between:0,11',
            'law_source' => 'required|in:RPC,RA,PD,EO,OTHER',
        ]);

        PenaltyReference::create([
            ...$request->only(['rpc_code', 'charge_name', 'max_penalty_years', 'max_penalty_months', 'law_source']),
            'last_validated' => now(),
        ]);

        AuditService::log('penalty_created', "Penalty reference {$request->input('charge_name')} created");

        return redirect()->back()->with('success', 'Penalty reference created successfully.');
    }

    public function penaltiesUpdate(Request $request, PenaltyReference $penalty)
    {
        $this->authorize('manage-penalties');
        $request->validate([
            'rpc_code' => ['required', 'string', 'max:50', Rule::unique('penalty_references', 'rpc_code')->ignore($penalty)],
            'charge_name' => 'required|string|max:255',
            'max_penalty_years' => 'required|numeric|min:0|max:100',
            'max_penalty_months' => 'nullable|integer|between:0,11',
            'law_source' => 'required|in:RPC,RA,PD,EO,OTHER',
        ]);

        $penalty->update([
            ...$request->only(['rpc_code', 'charge_name', 'max_penalty_years', 'max_penalty_months', 'law_source']),
            'last_validated' => now(),
        ]);

        AuditService::log('penalty_updated', "Penalty reference {$penalty->charge_name} updated");

        return redirect()->back()->with('success', 'Penalty reference updated successfully.');
    }

    public function penaltiesDestroy(PenaltyReference $penalty)
    {
        $this->authorize('manage-penalties');
        abort_if($penalty->detainees()->exists(), 422, 'A penalty reference in use cannot be deleted.');
        $name = $penalty->charge_name;
        $penalty->delete();

        AuditService::log('penalty_deleted', "Penalty reference {$name} deleted");

        return redirect()->back()->with('success', 'Penalty reference deleted successfully.');
    }
}
