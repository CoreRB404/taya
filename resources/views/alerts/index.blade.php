@extends('layouts.app')

@section('header', 'System Alert Queue')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Alert Queue</h2>
    </div>

    <!-- Filter Bar -->
    <div class="glass-panel p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <form action="{{ route('alerts.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 w-full">
            <input type="search" name="search" placeholder="Search detainee, charge, or alert id..." value="{{ request('search') }}" class="block w-full sm:w-80 rounded-lg border-gray-300 text-sm px-3 py-2" />
            <select name="record_filter" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent">
                <option value="" {{ request('record_filter') === null || request('record_filter') === '' ? 'selected' : '' }}>All Statuses and Alerts</option>
                <optgroup label="Detainee status">
                    <option value="status:active" {{ request('record_filter') === 'status:active' ? 'selected' : '' }}>Active</option>
                    <option value="status:released" {{ request('record_filter') === 'status:released' ? 'selected' : '' }}>Released</option>
                    <option value="status:archived" {{ request('record_filter') === 'status:archived' ? 'selected' : '' }}>Archived</option>
                </optgroup>
                <optgroup label="Alert level">
                    <option value="alert:critical" {{ request('record_filter') === 'alert:critical' ? 'selected' : '' }}>Critical</option>
                    <option value="alert:at_risk" {{ request('record_filter') === 'alert:at_risk' ? 'selected' : '' }}>At Risk</option>
                    <option value="alert:flagged" {{ request('record_filter') === 'alert:flagged' ? 'selected' : '' }}>Flagged</option>
                    <option value="alert:monitored" {{ request('record_filter') === 'alert:monitored' ? 'selected' : '' }}>Monitored</option>
                    <option value="alert:resolved" {{ request('record_filter') === 'alert:resolved' ? 'selected' : '' }}>Resolved</option>
                </optgroup>
            </select>
            
            <select name="facility_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent">
                <option value="">All Facilities</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                @endforeach
            </select>

            @if(request()->hasAny(['record_filter', 'facility_id', 'search']))
                <a href="{{ route('alerts.index') }}" class="btn-secondary py-2 px-4 text-sm whitespace-nowrap">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Alert Table -->
    <div class="glass-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Severity</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Detainee</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Facility</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Generated</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Assigned To</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-{{ $alert->alert_level }}">
                                    {{ strtoupper(str_replace('_', ' ', $alert->alert_level)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <a href="{{ route('alerts.show', $alert) }}" class="font-bold text-gray-900 hover:text-taya-accent transition-colors">
                                        {{ $alert->detainee->full_name }}
                                    </a>
                                    <span class="text-xs text-gray-500">Overstay: {{ $alert->computation ? $alert->computation->overstay_days_display : '0 days' }}</span>
                                    @php
                                        $completedPhases = $alert->detainee->phases->where('completed', true)->count();
                                        $totalPhases = $alert->detainee->phases->count();
                                        $phaseLabel = $totalPhases > 0 && $completedPhases === $totalPhases
                                            ? 'All phases completed'
                                            : "Phase progress: {$completedPhases}/{$totalPhases} complete";
                                    @endphp
                                    <span class="mt-1 inline-flex w-fit items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                                        {{ $phaseLabel }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $alert->detainee->facility->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-900">{{ $alert->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($alert->assigned_to)
                                    <span class="text-gray-900 font-medium">{{ $alert->assignedUser->name }}</span>
                                @else
                                    <span class="text-gray-400 italic text-xs">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('alerts.show', $alert) }}" class="btn-secondary py-1.5 px-3 text-xs">
                                    Manage Alert
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No alerts found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($alerts->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
