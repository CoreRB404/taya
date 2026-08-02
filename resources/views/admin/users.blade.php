@extends('layouts.app')

@section('header', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-900">System Users</h2>
        <div class="flex flex-wrap gap-2">
            <form id="bulk-reset-form" action="{{ route('admin.users.bulk-reset-passwords') }}" method="POST" class="flex items-center">
                @csrf
                <div id="bulk-reset-user-inputs"></div>
                <button id="bulk-reset-button" type="submit" disabled class="btn-secondary flex items-center gap-2 border-orange-200 text-orange-700 hover:bg-orange-50 hover:text-orange-800 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400 disabled:opacity-70 disabled:hover:bg-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Bulk Reset Passwords (<span id="bulk-reset-count">0</span>)
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
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                @if($user->id !== auth()->id())
                                <input type="checkbox" value="{{ $user->id }}" data-user-checkbox class="user-checkbox rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
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
                                <button
                                    type="button"
                                    data-change-password
                                    data-action="{{ route('admin.users.change-password', $user) }}"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    class="mr-2 inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
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

                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive" class="rounded border-gray-300 text-taya-accent focus:ring-taya-accent">
                            Active account
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

<!-- Change Password Dialog (native browser dialog, initialized in app.js) -->
<dialog
    id="change-password-dialog"
    data-open-on-load="{{ $errors->changePassword->any() ? 'true' : 'false' }}"
    class="rounded-2xl bg-transparent p-0 shadow-2xl backdrop:bg-gray-900/75 backdrop:backdrop-blur-sm"
    style="position: fixed; left: 50%; top: 50%; width: min(32rem, calc(100vw - 2rem)); max-height: calc(100vh - 2rem); margin: 0; transform: translate(-50%, -50%);"
    aria-labelledby="change-password-title"
>
    <form
        id="change-password-form"
        action="{{ old('change_password_user_id') ? url('/admin/users/'.old('change_password_user_id').'/change-password') : '#' }}"
        method="POST"
        class="overflow-hidden rounded-2xl bg-white"
    >
        @csrf
        <input id="change-password-user-id" type="hidden" name="change_password_user_id" value="{{ old('change_password_user_id') }}">
        <input id="change-password-user-name-input" type="hidden" name="change_password_user_name" value="{{ old('change_password_user_name') }}">

        <div class="px-6 py-5">
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
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-sm text-gray-900 shadow-sm focus:border-taya-accent focus:ring-taya-accent">
            </div>

            <div class="mt-4">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New password</label>
                <input type="password" id="new_password" name="password" required minlength="12" autocomplete="new-password" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-sm text-gray-900 shadow-sm focus:border-taya-accent focus:ring-taya-accent">
            </div>

            <div class="mt-4">
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm new password</label>
                <input type="password" id="new_password_confirmation" name="password_confirmation" required minlength="12" autocomplete="new-password" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-sm text-gray-900 shadow-sm focus:border-taya-accent focus:ring-taya-accent">
            </div>

            <p class="mt-3 text-xs text-gray-500">Use at least 12 characters with upper/lowercase letters, a number, and a symbol.</p>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:justify-end">
            <button type="button" data-close-change-password class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="inline-flex justify-center rounded-lg bg-taya-accent px-4 py-2 text-sm font-medium text-white hover:bg-taya-accent-dark">Update Password</button>
        </div>
    </form>
</dialog>
@endsection
