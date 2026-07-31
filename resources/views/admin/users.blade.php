@extends('layouts.app')

@section('header', 'User Management')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedUsers: [], 
    selectAll: false,
    toggleAll() {
        if (this.selectAll) {
            this.selectedUsers = Array.from(document.querySelectorAll('.user-checkbox')).map(cb => cb.value);
        } else {
            this.selectedUsers = [];
        }
    }
}">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-900">System Users</h2>
        <div class="flex flex-wrap gap-2">
            <form action="{{ route('admin.users.bulk-reset-passwords') }}" method="POST" x-show="selectedUsers.length > 0" x-transition class="flex items-center">
                @csrf
                <template x-for="id in selectedUsers">
                    <input type="hidden" name="user_ids[]" :value="id">
                </template>
                <button type="submit" onclick="return confirm('Are you sure you want to reset passwords to default for ' + selectedUsers.length + ' users?');" class="btn-secondary flex items-center gap-2 border-orange-200 text-orange-700 hover:bg-orange-50 hover:text-orange-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Bulk Reset Passwords (<span x-text="selectedUsers.length"></span>)
                </button>
            </form>
            <button @click="$dispatch('open-modal', 'create-user')" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New User
            </button>
        </div>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll" class="rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
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
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                @if($user->id !== auth()->id())
                                <input type="checkbox" value="{{ $user->id }}" x-model="selectedUsers" class="user-checkbox rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ $user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $user->facility ? $user->facility->name : 'System Wide' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
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
                                         
                                        <button @click="$dispatch('open-edit-user-modal', {{ Illuminate\Support\Js::from(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'facility_id' => $user->facility_id, 'is_active' => $user->is_active, 'mfa_enabled' => $user->mfa_enabled]) }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Edit User
                                        </button>
                                        <button @click="$dispatch('open-change-password-modal', {{ Illuminate\Support\Js::from(['id' => $user->id, 'name' => $user->name]) }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Change Password
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

<!-- Create User Modal (AlpineJS driven) -->
<div x-data="{ show: false, showPassword: false }"
     x-show="show"
     x-transition.opacity.duration.300ms
     x-on:open-modal.window="if ($event.detail === 'create-user') $nextTick(() => { show = true })"
     x-on:keydown.escape.window="if (show) { show = false; $event.stopPropagation(); }"
     style="display: none; z-index: 9999;"
     class="fixed inset-0 flex items-center justify-center p-4 bg-gray-900/75 backdrop-blur-sm overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     @mousedown.self="show = false">
    
    <!-- Modal Panel -->
    <div style="width: 100%; max-width: 32rem;" class="relative bg-white rounded-2xl text-left shadow-2xl overflow-hidden transform transition-all my-8">
            
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-5">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Provision New User</h3>
                        <p class="text-sm text-gray-500 mt-1">Create a new system account and set its initial password.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <div class="relative mt-1">
                                <input :type="showPassword ? 'text' : 'password'" id="create_user_password" name="password" required minlength="12" autocomplete="new-password" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 pr-16 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-medium text-gray-500 hover:text-gray-700" :aria-label="showPassword ? 'Hide password' : 'Show password'" x-text="showPassword ? 'Hide' : 'Show'"></button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Use 12+ characters with upper/lowercase letters, a number, and a symbol.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" required minlength="12" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                                @foreach(\App\Enums\UserRole::assignable() as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Facility Assignment (Optional for non-BJMP)</label>
                            <select name="facility_id" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                                <option value="">System Wide / None</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-taya-accent text-base font-medium text-white hover:bg-taya-accent-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Create Account
                    </button>
                    <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
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
        mfaEnabled: true,
        openEdit(user) {
            this.userId = user.id;
            this.userName = user.name;
            this.userEmail = user.email;
            this.userRole = user.role;
            this.userFacilityId = user.facility_id;
            this.isActive = user.is_active;
            this.mfaEnabled = user.mfa_enabled;
            this.show = true;
        }
     }"
     x-show="show"
     x-transition.opacity.duration.300ms
     x-on:open-edit-user-modal.window="if ($event.detail) { $nextTick(() => { openEdit($event.detail); }) }"
     x-on:keydown.escape.window="if (show) { show = false; $event.stopPropagation(); }"
     style="display: none; z-index: 9999;"
     class="fixed inset-0 flex items-center justify-center p-4 bg-gray-900/75 backdrop-blur-sm overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     @mousedown.self="show = false">
    <div style="width: 100%; max-width: 32rem;" class="relative bg-white rounded-2xl text-left shadow-2xl overflow-hidden transform transition-all my-8">
        <form :action="'/admin/users/' + userId" method="POST">
            @csrf
            @method('PUT')
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mb-5">
                    <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Edit User</h3>
                    <p class="text-sm text-gray-500 mt-1">Update name, email, role, or facility assignment.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" x-model="userName" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" x-model="userEmail" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role" x-model="userRole" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                            @foreach(\App\Enums\UserRole::assignable() as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">The final active admin and your own role are protected by the server.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Facility Assignment (Optional)</label>
                        <select name="facility_id" x-model="userFacilityId" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                            <option value="">System Wide / None</option>
                            @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive" class="rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                            Active account
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="mfa_enabled" value="0">
                            <input type="checkbox" name="mfa_enabled" value="1" x-model="mfaEnabled" class="rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                            MFA enabled
                        </label>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-taya-accent text-base font-medium text-white hover:bg-taya-accent-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal (AlpineJS driven) -->
<div x-data="{ 
        show: false, 
        userId: null, 
        userName: '',
        showPass: false, 
        password: '', 
        strength: 0, 
        strengthLabel: ''
     }"
     x-effect="
        if (password.length === 0) { strength = 0; strengthLabel = ''; }
        else if (password.length < 8) { strength = 1; strengthLabel = 'Too short'; }
        else if (password.length < 10 && !/[A-Z]/.test(password)) { strength = 2; strengthLabel = 'Weak'; }
        else if (/[A-Z]/.test(password) && /[0-9]/.test(password) && password.length >= 10) { strength = 4; strengthLabel = 'Strong'; }
        else { strength = 3; strengthLabel = 'Fair'; }
     "
     x-show="show"
     x-transition.opacity.duration.300ms
     x-on:open-change-password-modal.window="if ($event.detail) { $nextTick(() => { userId = $event.detail.id; userName = $event.detail.name; password = ''; showPass = false; show = true; }) }"
     x-on:keydown.escape.window="if (show) { show = false; $event.stopPropagation(); }"
     style="display: none; z-index: 9999;"
     class="fixed inset-0 flex items-center justify-center p-4 bg-gray-900/75 backdrop-blur-sm overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     @mousedown.self="show = false">
    
    <!-- Modal Panel -->
    <div style="width: 100%; max-width: 32rem;" class="relative bg-white rounded-2xl text-left shadow-2xl overflow-hidden transform transition-all my-8">
        
        <form :action="'/admin/users/' + userId + '/change-password'" method="POST">
            @csrf
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mb-5">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <svg style="width: 1.25rem; height: 1.25rem; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                            Set New Password <br><span class="text-gray-500 font-normal text-sm">for <span x-text="userName" class="font-semibold text-gray-700"></span></span>
                        </h3>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Your admin password</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent">
                </div>

                <!-- Password Input -->
                <div style="margin-bottom: 1.25rem;">
                    <label for="new_password" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">New Password</label>
                    <div style="position: relative;">
                        <div style="position: absolute; inset: 0; right: auto; display: flex; align-items: center; padding-left: 1rem; pointer-events: none;">
                            <svg style="width: 1.25rem; height: 1.25rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" 
                               id="new_password" 
                               name="new_password" 
                               x-model="password"
                               required 
                               minlength="12"
                               placeholder="Enter a strong password (12+ characters)"
                               style="padding-left: 2.75rem; padding-right: 2.75rem;"
                               class="block w-full rounded-lg border border-gray-300 bg-white py-3 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent transition-colors">
                        <button type="button" @click="showPass = !showPass" style="position: absolute; inset: 0; left: auto; display: flex; align-items: center; padding-right: 1rem; background: none; border: none; cursor: pointer; color: #9ca3af; transition: color 0.2s;" onmouseover="this.style.color='#3b82f6'" onmouseout="this.style.color='#9ca3af'">
                            <svg x-show="!showPass" style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" style="width: 1.25rem; height: 1.25rem; display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm new password</label>
                    <input :type="showPass ? 'text' : 'password'" id="new_password_confirmation" name="new_password_confirmation" required minlength="12" autocomplete="new-password" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-sm text-gray-900 shadow-sm outline-none focus:border-taya-accent focus:ring-1 focus:ring-taya-accent">
                </div>

                <!-- Password Strength Meter -->
                <div x-show="password.length > 0" x-transition style="margin-bottom: 1.5rem;">
                    <div style="display: flex; gap: 0.375rem; margin-bottom: 0.5rem;">
                        <div style="height: 0.3rem; flex: 1; border-radius: 9999px; background: #e5e7eb; overflow: hidden;">
                            <div style="height: 100%; border-radius: 9999px; transition: all 0.4s ease;" :style="{ width: strength >= 1 ? '100%' : '0%', background: strength === 1 ? '#ef4444' : strength === 2 ? '#f97316' : strength === 3 ? '#eab308' : strength >= 4 ? '#22c55e' : '#e5e7eb' }"></div>
                        </div>
                        <div style="height: 0.3rem; flex: 1; border-radius: 9999px; background: #e5e7eb; overflow: hidden;">
                            <div style="height: 100%; border-radius: 9999px; transition: all 0.4s ease;" :style="{ width: strength >= 2 ? '100%' : '0%', background: strength === 2 ? '#f97316' : strength === 3 ? '#eab308' : strength >= 4 ? '#22c55e' : '#e5e7eb' }"></div>
                        </div>
                        <div style="height: 0.3rem; flex: 1; border-radius: 9999px; background: #e5e7eb; overflow: hidden;">
                            <div style="height: 100%; border-radius: 9999px; transition: all 0.4s ease;" :style="{ width: strength >= 3 ? '100%' : '0%', background: strength === 3 ? '#eab308' : strength >= 4 ? '#22c55e' : '#e5e7eb' }"></div>
                        </div>
                        <div style="height: 0.3rem; flex: 1; border-radius: 9999px; background: #e5e7eb; overflow: hidden;">
                            <div style="height: 100%; border-radius: 9999px; transition: all 0.4s ease;" :style="{ width: strength >= 4 ? '100%' : '0%', background: strength >= 4 ? '#22c55e' : '#e5e7eb' }"></div>
                        </div>
                    </div>
                    <p style="font-size: 0.75rem; font-weight: 600; margin: 0;" :style="{ color: strength === 1 ? '#ef4444' : strength === 2 ? '#f97316' : strength === 3 ? '#ca8a04' : strength >= 4 ? '#16a34a' : '#9ca3af' }" x-text="strengthLabel"></p>
                </div>

                <!-- Password Requirements -->
                <div style="background: linear-gradient(135deg, #eff6ff, #eef2ff); border: 1px solid rgba(59,130,246,0.12); border-radius: 0.875rem; padding: 1.25rem 1.5rem; margin-bottom: 1rem;">
                    <p style="font-size: 0.6875rem; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 0.75rem 0;">Password Requirements</p>
                    <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.625rem;">
                        <li style="display: flex; align-items: center; gap: 0.625rem; font-size: 0.8125rem;" :style="{ color: password.length >= 12 ? '#16a34a' : '#6b7280' }">
                            <div style="width: 1.25rem; height: 1.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s;" :style="{ background: password.length >= 12 ? '#dcfce7' : '#f3f4f6', border: '1.5px solid ' + (password.length >= 12 ? '#22c55e' : '#d1d5db') }">
                                <svg style="width: 0.75rem; height: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: password.length >= 12 ? '#22c55e' : '#d1d5db' }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            At least 12 characters, including upper/lowercase, a number, and a symbol
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.625rem; font-size: 0.8125rem;" :style="{ color: /[A-Z]/.test(password) ? '#16a34a' : '#6b7280' }">
                            <div style="width: 1.25rem; height: 1.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s;" :style="{ background: /[A-Z]/.test(password) ? '#dcfce7' : '#f3f4f6', border: '1.5px solid ' + (/[A-Z]/.test(password) ? '#22c55e' : '#d1d5db') }">
                                <svg style="width: 0.75rem; height: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: /[A-Z]/.test(password) ? '#22c55e' : '#d1d5db' }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            Include an uppercase letter
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.625rem; font-size: 0.8125rem;" :style="{ color: /[0-9]/.test(password) ? '#16a34a' : '#6b7280' }">
                            <div style="width: 1.25rem; height: 1.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s;" :style="{ background: /[0-9]/.test(password) ? '#dcfce7' : '#f3f4f6', border: '1.5px solid ' + (/[0-9]/.test(password) ? '#22c55e' : '#d1d5db') }">
                                <svg style="width: 0.75rem; height: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: /[0-9]/.test(password) ? '#22c55e' : '#d1d5db' }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            Include a number
                        </li>
                    </ul>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-taya-accent text-base font-medium text-white hover:bg-taya-accent-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Update Password
                </button>
                <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
