@extends('layouts.app')

@section('header', 'Admin Dashboard')

@section('content')
<div class="page-stack">
    <!-- Stats Row -->
    <div class="stat-grid lg:grid-cols-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue-100 text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div><p class="stat-label">Total Facilities</p><p class="stat-value">{{ number_format($stats['total_facilities']) }}</p></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-indigo-100 text-indigo-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div><p class="stat-label">Active Detainees</p><p class="stat-value">{{ number_format($stats['total_detainees']) }}</p></div>
        </div>
        
        <div class="stat-card border-red-100 bg-red-50/40">
            <div class="stat-icon bg-red-100 text-red-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div><p class="stat-label text-red-600">Critical Alerts</p><p class="stat-value text-red-700">{{ number_format($stats['critical_alerts']) }}</p></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-green-100 text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div><p class="stat-label">Resolved This Month</p><p class="stat-value">{{ number_format($stats['resolved_this_month']) }}</p></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-orange-100 text-orange-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM6.84 4.34L4.22 6.96A9.956 9.956 0 002 12c0 2.76 1.12 5.26 2.93 7.07C6.74 21.88 9.24 23 12 23s5.26-1.12 7.07-2.93C21.88 17.26 23 14.76 23 12c0-2.76-1.12-5.26-2.93-7.07L17.16 4.34A9.748 9.748 0 0012 2c-1.94 0-3.76.63-5.16 1.74z"></path></svg>
            </div>
            <div><p class="stat-label">Unable to Pay Bail</p><p class="stat-value">{{ number_format($stats['unable_to_pay_bail']) }}</p></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-yellow-100 text-yellow-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19.5l9-6 9 6M3 7.5l9 6 9-6"/></svg>
            </div>
            <div><p class="stat-label">Overcrowded Facilities</p><p class="stat-value">{{ number_format($stats['overcrowded_facilities']) }}</p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <!-- Quick Links -->
        <div class="space-y-4 xl:col-span-1">
            <div class="glass-panel overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-semibold text-gray-900">Administration</h3>
                </div>
                <div class="p-2">
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors group">
                        <div class="p-2.5 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-taya-accent group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">User Management</p>
                            <p class="text-sm text-gray-500">Manage system users and roles</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.facilities.index') }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors group">
                        <div class="p-2.5 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-taya-accent group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Facilities</p>
                            <p class="text-sm text-gray-500">Manage BJMP facilities</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.penalties.index') }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-xl transition-colors group">
                        <div class="p-2.5 bg-gray-100 text-gray-600 rounded-lg group-hover:bg-taya-accent group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Penalty References</p>
                            <p class="text-sm text-gray-500">Update RPC/RA mappings</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Audit Logs -->
        <div class="xl:col-span-2 glass-panel overflow-hidden flex flex-col h-full">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Recent Audit Logs</h3>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm font-medium text-taya-accent hover:text-taya-accent-dark">View all &rarr;</a>
            </div>
            <div class="table-scroll flex-1">
                <table class="data-table responsive-table">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Time</th>
                            <th scope="col" class="px-6 py-3">User</th>
                            <th scope="col" class="px-6 py-3">Action</th>
                            <th scope="col" class="px-6 py-3">Target</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentAuditLogs as $log)
                            <tr class="data-row">
                                <td data-label="Time" class="whitespace-nowrap text-xs text-gray-500">
                                    {{ $log->created_at->format('M d, H:i') }}
                                </td>
                                <td data-label="User" class="font-medium text-gray-900">
                                    {{ $log->user ? $log->user->name : 'System' }}
                                </td>
                                <td data-label="Action">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td data-label="Target" class="max-w-xs truncate text-gray-600">
                                    @if($log->detainee)
                                        <a href="{{ route('detainees.show', $log->detainee_id) }}" class="text-taya-accent hover:underline">
                                            {{ $log->detainee->full_name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    No recent activity found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
