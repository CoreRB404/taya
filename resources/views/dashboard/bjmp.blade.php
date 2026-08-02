@extends('layouts.app')

@section('header', 'BJMP Dashboard')

@section('content')
<div class="page-stack">
    <!-- Stats Row -->
    <div class="stat-grid lg:grid-cols-4">
        <div class="stat-card">
            <div class="stat-icon bg-blue-100 text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
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
            <div class="stat-icon bg-orange-100 text-orange-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div><p class="stat-label text-orange-600">At Risk Alerts</p><p class="stat-value text-orange-700">{{ number_format($stats['at_risk']) }}</p></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-yellow-100 text-yellow-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div><p class="stat-label text-yellow-600">Phases Overdue</p><p class="stat-value text-yellow-700">{{ number_format($stats['phases_overdue_today']) }}</p></div>
        </div>
    </div>

    <!-- Overdue Phases Table -->
    <div class="glass-panel overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Overdue Compliance Phases (Action Required)</h3>
            <a href="{{ route('detainees.index') }}" class="btn-secondary text-sm py-1.5">View All Detainees</a>
        </div>
        
        <div class="table-scroll">
            <table class="data-table responsive-table">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Detainee</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Facility</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Phase</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Due Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Overdue By</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($overduePhases as $phase)
                        <tr class="data-row">
                            <td data-label="Detainee" class="whitespace-nowrap">
                                <a href="{{ route('detainees.show', $phase->detainee_id) }}" class="font-medium text-gray-900 hover:text-taya-accent transition-colors">
                                    {{ $phase->detainee->full_name }}
                                </a>
                            </td>
                            <td data-label="Facility" class="whitespace-nowrap text-gray-600">
                                {{ $phase->detainee->facility->name }}
                            </td>
                            <td data-label="Phase" class="whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                    Phase {{ $phase->phase_number }}: {{ $phase->phase_name }}
                                </span>
                            </td>
                            <td data-label="Due date" class="whitespace-nowrap text-gray-600">
                                {{ $phase->due_date->format('M d, Y') }}
                            </td>
                            <td data-label="Overdue by" class="whitespace-nowrap">
                                <span class="font-medium text-red-600 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $phase->days_overdue }} days
                                </span>
                            </td>
                            <td data-label="Action" class="whitespace-nowrap text-right">
                                <form action="{{ route('phases.complete', $phase) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to mark this phase as complete?');">
                                    @csrf
                                    <button type="submit" class="btn-primary py-1.5 px-3 text-xs flex items-center gap-1.5 inline-flex ml-auto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Mark Complete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 mb-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">All caught up!</h3>
                                <p class="text-gray-500 mt-1">There are no overdue compliance phases at this time.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
