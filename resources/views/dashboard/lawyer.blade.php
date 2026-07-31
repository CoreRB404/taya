@extends('layouts.app')

@section('header', 'Lawyer Dashboard - Priority Alert Queue')

@section('content')
<div class="space-y-6">
    <!-- Filter Bar -->
    <div class="glass-panel p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-2 text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            <span class="font-medium text-sm">Filter Queue</span>
        </div>
        
        <form action="{{ route('dashboard') }}" method="GET" class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <select name="alert_level" class="rounded-lg border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent">
                <option value="">All Alert Levels</option>
                <option value="critical" {{ request('alert_level') === 'critical' ? 'selected' : '' }}>Critical</option>
                <option value="at_risk" {{ request('alert_level') === 'at_risk' ? 'selected' : '' }}>At Risk</option>
                <option value="flagged" {{ request('alert_level') === 'flagged' ? 'selected' : '' }}>Flagged</option>
                <option value="monitored" {{ request('alert_level') === 'monitored' ? 'selected' : '' }}>Monitored</option>
            </select>
            
            <select name="facility_id" class="rounded-lg border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent">
                <option value="">All Facilities</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="btn-primary py-2 px-4 text-sm">
                Apply Filters
            </button>
            
            @if(request()->hasAny(['alert_level', 'facility_id']))
                <a href="{{ route('dashboard') }}" class="btn-secondary py-2 px-4 text-sm">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Alert Queue -->
    <div class="glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Detainee</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Charge & Penalty</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Time Served</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Assignment</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <a href="{{ route('alerts.show', $alert) }}" class="font-bold text-gray-900 hover:text-taya-accent transition-colors text-base">
                                        {{ $alert->detainee->full_name }}
                                    </a>
                                    <span class="text-xs text-gray-500">{{ $alert->detainee->facility->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-gray-900 font-medium">{{ $alert->detainee->penaltyReference->charge_name }}</span>
                                    <span class="text-xs text-gray-500">Max Penalty: {{ $alert->detainee->penaltyReference->penalty_duration_display }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-gray-900 font-medium">{{ $alert->detainee->days_detained_display }}</span>
                                    <span class="text-xs text-gray-500">Since: {{ $alert->detainee->commitment_date->format('M d, Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-{{ $alert->alert_level }}">
                                    {{ strtoupper(str_replace('_', ' ', $alert->alert_level)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($alert->assigned_to)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-taya-accent text-white flex items-center justify-center text-xs font-bold">
                                            {{ substr($alert->assignedUser->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm text-gray-700">{{ $alert->assignedUser->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('alerts.show', $alert) }}" class="btn-secondary py-1.5 px-3 text-xs">
                                    Review Case
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 mb-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">Queue is empty</h3>
                                <p class="text-gray-500 mt-1">There are no active alerts matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($alerts->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
