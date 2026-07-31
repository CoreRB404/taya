@extends('layouts.app')

@section('header', 'System Audit Logs')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Audit Trail</h2>
    </div>

    <!-- Filter Bar -->
    <div class="glass-panel p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 w-full">
            <select name="action" class="rounded-lg border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent">
                <option value="">All Actions</option>
                <option value="detainee_created" {{ request('action') === 'detainee_created' ? 'selected' : '' }}>Detainee Created</option>
                <option value="phase_completed" {{ request('action') === 'phase_completed' ? 'selected' : '' }}>Phase Completed</option>
                <option value="alert_generated" {{ request('action') === 'alert_generated' ? 'selected' : '' }}>Alert Generated</option>
                <option value="legal_action_filed" {{ request('action') === 'legal_action_filed' ? 'selected' : '' }}>Legal Action</option>
                <option value="alert_resolved" {{ request('action') === 'alert_resolved' ? 'selected' : '' }}>Alert Resolved</option>
            </select>
            
            <button type="submit" class="btn-primary py-2 px-4 text-sm whitespace-nowrap">
                Filter Logs
            </button>
            
            @if(request()->has('action'))
                <a href="{{ route('admin.audit-logs.index') }}" class="btn-secondary py-2 px-4 text-sm whitespace-nowrap">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Timestamp</th>
                        <th scope="col" class="px-6 py-4 font-semibold">User</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Action</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Details</th>
                        <th scope="col" class="px-6 py-4 font-semibold">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-medium">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->user)
                                    <span class="font-medium text-gray-900">{{ $log->user->name }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $log->user->role }}</span>
                                @else
                                    <span class="text-gray-400 italic">System</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge bg-gray-100 text-gray-800 font-mono text-[10px]">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $log->description }}
                                @if($log->detainee_id)
                                    <br>
                                    <a href="{{ route('detainees.show', $log->detainee_id) }}" class="text-xs text-taya-accent hover:underline">
                                        View Detainee Record &rarr;
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 font-mono">
                                {{ $log->ip_address ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No audit logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
