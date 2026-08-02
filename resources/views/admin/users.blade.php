@extends('layouts.app')

@section('header', 'User Management')

@section('content')
<input id="create-user-toggle" type="checkbox" class="peer sr-only"
       aria-label="Toggle new user form"
       {{ $errors->createUser->any() ? 'checked' : '' }}>
<div class="page-stack">
    <div class="page-heading">
        <div><h2 class="page-title">System Users</h2><p class="page-subtitle">Manage access, assignments, and account security.</p></div>
        <div class="mobile-actions sm:w-auto sm:flex-row">
            <form id="bulk-reset-form" action="{{ route('admin.users.bulk-reset-passwords') }}" method="POST" class="flex w-full sm:w-auto">
                @csrf
                <button id="bulk-reset-button" type="submit" disabled class="btn-secondary flex w-full items-center justify-center gap-2 border-orange-200 text-orange-700 hover:bg-orange-50 hover:text-orange-800 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 disabled:opacity-70 disabled:hover:bg-white sm:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Bulk Reset Passwords (<span id="bulk-reset-count">0</span>)
                </button>
            </form>
            <label for="create-user-toggle" class="btn-primary flex w-full cursor-pointer items-center justify-center gap-2 sm:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New User
            </label>
        </div>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="table-scroll">
            <table class="data-table responsive-table">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12">
                            <input type="checkbox" data-select-all-users class="rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name & Email</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Role</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Facility Assignment</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Joined</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="data-row">
                            <td data-label="Select">
                                @if($user->id !== auth()->id())
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" form="bulk-reset-form" data-user-checkbox class="user-checkbox rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                                @endif
                            </td>
                            <td data-label="User">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ $user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td data-label="Role" class="whitespace-nowrap">
                                @php
                                    $roleColor = match($user->role) {
                                        'admin' => 'purple',
                                        'staff' => 'blue',
                                        'lawyer' => 'indigo',
                                        'auditor' => 'amber',
                                        'authorized_user' => 'indigo',
                                        default => 'gray'
                                    };
                                @endphp
                                <span class="badge bg-{{ $roleColor }}-100 text-{{ $roleColor }}-800">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td data-label="Facility" class="whitespace-nowrap text-gray-600">
                                {{ $user->facility ? $user->facility->name : 'System Wide' }}
                            </td>
                            <td data-label="Joined" class="whitespace-nowrap text-gray-600">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td data-label="Action" class="whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    data-change-password
                                    data-action="{{ route('admin.users.change-password', $user) }}"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    class="inline-flex min-h-9 items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                >
                                    Change Password
                                </button>
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button @click="open = !open" @click.away="open = false" type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none p-1 rounded-md hover:bg-gray-100">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                                    </button>
                                    
                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20 py-1"
                                         style="display: none;">
                                         
                                        <button @click="$dispatch('open-edit-user-modal', {{ Illuminate\Support\Js::from(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'facility_id' => $user->facility_id, 'is_active' => $user->is_active]) }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Edit User
                                        </button>
                                        @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" onsubmit="return confirm('Send this user a password reset link?');">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Send Reset Link
                                            </button>
                                        </form>
                                        
                                        <div class="border-t border-gray-100 my-1"></div>
                                        
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Remove this user from the system?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                Revoke Access
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create User Modal (CSS controlled so it remains reliable without JavaScript) -->
<div class="fixed inset-0 z-[9999] hidden items-center justify-center overflow-y-auto bg-gray-900/75 p-3 backdrop-blur-sm peer-checked:flex sm:p-4"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
>
    
    <!-- Modal Panel -->
    <div class="modal-panel">
            
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-5">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Provision New User</h3>
                        <p class="text-sm text-gray-500 mt-1">Create a new system account and set its initial password.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="form-control mt-1">
                            @error('name', 'createUser')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="form-control mt-1">
                            @error('email', 'createUser')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <div class="relative mt-1">
                                <input type="password" id="create_user_password" name="password" required minlength="12" autocomplete="new-password" class="form-control">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Use 12+ characters with upper/lowercase letters, a number, and a symbol.</p>
                            @error('password', 'createUser')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" required minlength="12" class="form-control mt-1">
                            @error('password_confirmation', 'createUser')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" required class="form-control mt-1">
                                @foreach(\App\Enums\UserRole::assignable() as $role)
                                    <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                            @error('role', 'createUser')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Facility Assignment (Optional for non-BJMP)</label>
                            <select name="facility_id" class="form-control mt-1">
                                <option value="">System Wide / None</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ (string) old('facility_id') === (string) $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                                @endforeach
                            </select>
                            @error('facility_id', 'createUser')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3 sm:flex-row sm:justify-end">
                    <button type="submit" class="btn-primary w-full justify-center sm:w-auto">
                        Create Account
                    </button>
                    <label for="create-user-toggle" class="btn-secondary w-full cursor-pointer justify-center sm:w-auto">
                        Cancel
                    </label>
                </div>
            </form>
        </div>
</div>

<!-- Edit User Modal (AlpineJS driven) -->
<div x-data="{
        show: false,
        userId: null,
        userName: '',
        userEmail: '',
        userRole: '',
        userFacilityId: '',
        isActive: true,
        openEdit(user) {
            this.userId = user.id;
            this.userName = user.name;
            this.userEmail = user.email;
            this.userRole = user.role;
            this.userFacilityId = user.facility_id;
            this.isActive = user.is_active;
            this.show = true;
        }
     }"
     x-show="show"
     x-transition.opacity.duration.300ms
     x-on:open-edit-user-modal.window="if ($event.detail) { $nextTick(() => { openEdit($event.detail); }) }"
     x-on:keydown.escape.window="if (show) { show = false; $event.stopPropagation(); }"
     style="display: none; z-index: 9999;"
     class="fixed inset-0 flex items-center justify-center overflow-y-auto bg-gray-900/75 p-3 backdrop-blur-sm sm:p-4"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     @mousedown.self="show = false">
    <div class="modal-panel">
        <form :action="'/admin/users/' + userId" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="mb-5">
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Edit User</h3>
                    <p class="text-sm text-gray-500 mt-1">Update name, email, role, or facility assignment.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" x-model="userName" required class="form-control mt-1">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" x-model="userEmail" required class="form-control mt-1">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role" x-model="userRole" required class="form-control mt-1">
                            @foreach(\App\Enums\UserRole::assignable() as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">The final active admin and your own role are protected by the server.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Facility Assignment (Optional)</label>
                        <select name="facility_id" x-model="userFacilityId" class="form-control mt-1">
                            <option value="">System Wide / None</option>
                            @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive" class="rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                            Active account
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3 sm:flex-row sm:justify-end">
                <button type="submit" class="btn-primary w-full justify-center sm:w-auto">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="btn-secondary w-full justify-center sm:w-auto">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Dialog (native browser dialog, initialized in app.js) -->
<dialog
    id="change-password-dialog"
    data-open-on-load="{{ $errors->changePassword->any() ? 'true' : 'false' }}"
    class="rounded-xl bg-transparent p-0 shadow-2xl backdrop:bg-gray-900/75 backdrop:backdrop-blur-sm"
    style="position: fixed; left: 50%; top: 50%; width: min(30rem, calc(100vw - 1.5rem)); max-height: calc(100dvh - 1.5rem); margin: 0; transform: translate(-50%, -50%);"
    aria-labelledby="change-password-title"
>
    <form
        id="change-password-form"
        action="{{ old('change_password_user_id') ? url('/admin/users/'.old('change_password_user_id').'/change-password') : '#' }}"
        method="POST"
        class="max-h-[calc(100dvh-1.5rem)] overflow-y-auto rounded-xl bg-white"
    >
        @csrf
        <input id="change-password-user-id" type="hidden" name="change_password_user_id" value="{{ old('change_password_user_id') }}">
        <input id="change-password-user-name-input" type="hidden" name="change_password_user_name" value="{{ old('change_password_user_name') }}">

        <div class="p-5 sm:p-6">
            <h3 id="change-password-title" class="text-lg font-bold text-gray-900">Change Password</h3>
            <p class="mt-1 text-sm text-gray-500">User: <span id="change-password-user-name" class="font-semibold text-gray-700">{{ old('change_password_user_name') }}</span></p>

            @if ($errors->changePassword->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <p class="font-semibold">Password was not changed:</p>
                    <ul class="mt-1 list-disc space-y-1 pl-5">
                        @foreach ($errors->changePassword->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-5">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Your admin password</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="form-control mt-1">
            </div>

            <div class="mt-4">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New password</label>
                <input type="password" id="new_password" name="password" required minlength="12" autocomplete="new-password" class="form-control mt-1">
            </div>

            <div class="mt-4">
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm new password</label>
                <input type="password" id="new_password_confirmation" name="password_confirmation" required minlength="12" autocomplete="new-password" class="form-control mt-1">
            </div>

            <p class="mt-3 text-xs text-gray-500">Use at least 12 characters with upper/lowercase letters, a number, and a symbol.</p>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3 sm:flex-row sm:justify-end">
            <button type="button" data-close-change-password class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="inline-flex justify-center rounded-lg bg-taya-accent px-4 py-2 text-sm font-medium text-white hover:bg-taya-accent-dark">Update Password</button>
        </div>
    </form>
</dialog>
@endsection
