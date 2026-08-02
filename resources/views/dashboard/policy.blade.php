@extends('layouts.app')

@section('header', 'System Analytics')

@section('content')
<div class="page-stack">
    
    <div class="page-heading">
        <div>
            <h2 class="page-title">System Analytics</h2>
            <p class="page-subtitle">Monitor active alerts, case resolutions, bail barriers, and facility load.</p>
        </div>
        <div class="mobile-actions sm:justify-end">
        <button type="button" onclick="window.print()" class="btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Report
        </button>
        <a href="{{ route('reports.analytics', ['export' => 'json']) }}" target="_blank" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export JSON Data
        </a>
        </div>
    </div>

    <div class="stat-grid lg:grid-cols-3">
        <div class="stat-card">
            <div class="stat-icon bg-blue-100 text-blue-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a5 5 0 00-10 0v2m10 0H7m0 0H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <div><p class="stat-label">Active Detainees</p><p class="stat-value">{{ number_format(array_sum($detaineesByFacility)) }}</p></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-red-100 text-red-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <div><p class="stat-label">Active Alerts</p><p class="stat-value">{{ number_format(array_sum($alertsByLevel)) }}</p></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green-100 text-green-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div><p class="stat-label">Resolved in 6 Months</p><p class="stat-value">{{ number_format(array_sum($resolutionsOverTime)) }}</p></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-orange-100 text-orange-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM6.84 4.34L4.22 6.96A9.956 9.956 0 002 12c0 2.76 1.12 5.26 2.93 7.07C6.74 21.88 9.24 23 12 23s5.26-1.12 7.07-2.93C21.88 17.26 23 14.76 23 12c0-2.76-1.12-5.26-2.93-7.07L17.16 4.34A9.748 9.748 0 0012 2c-1.94 0-3.76.63-5.16 1.74z"></path></svg>
            </div>
            <div><p class="stat-label">Unable to Pay Bail</p><p class="stat-value">{{ number_format($unableToPayBail) }}</p></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-yellow-100 text-yellow-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19.5l9-6 9 6M3 7.5l9 6 9-6"/></svg>
            </div>
            <div><p class="stat-label">Overcrowded Facilities</p><p class="stat-value">{{ number_format($overcrowdedFacilities) }}</p></div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-indigo-100 text-indigo-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
            </div>
            <div><p class="stat-label">Facilities Reporting</p><p class="stat-value">{{ number_format(count($detaineesByFacility)) }}</p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <!-- Alerts by Level -->
        <div class="glass-panel p-4 sm:p-5">
            <h3 class="mb-4 text-base font-semibold text-gray-900 sm:text-lg">Active Alerts by Severity</h3>
            @if(count($alertsByLevel) > 0)
                <div class="relative flex h-56 w-full justify-center sm:h-64">
                    <canvas id="alertsLevelChart"></canvas>
                </div>
            @else
                <div class="empty-state flex h-56 flex-col items-center justify-center rounded-lg bg-gray-50">
                    <svg class="mb-3 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="font-medium text-gray-700">No active alerts</p>
                    <p class="mt-1 text-xs text-gray-500">Severity data will appear when an alert is generated.</p>
                </div>
            @endif
        </div>

        <!-- Resolutions Over Time -->
        <div class="glass-panel p-4 sm:p-5">
            <h3 class="mb-4 text-base font-semibold text-gray-900 sm:text-lg">Cases Resolved (Last 6 Months)</h3>
            <div class="relative h-56 w-full sm:h-64">
                <canvas id="resolutionsChart"></canvas>
            </div>
        </div>

        <!-- Detainees by Facility -->
        <div class="glass-panel p-4 sm:p-5 lg:col-span-2">
            <h3 class="mb-4 text-base font-semibold text-gray-900 sm:text-lg">Detainee Distribution by Facility</h3>
            @if(count($detaineesByFacility) > 0)
                <div class="relative h-64 w-full sm:h-80">
                    <canvas id="facilityChart"></canvas>
                </div>
            @else
                <div class="empty-state flex h-64 flex-col items-center justify-center rounded-lg bg-gray-50">
                    <svg class="mb-3 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                    <p class="font-medium text-gray-700">No active detainee data</p>
                    <p class="mt-1 text-xs text-gray-500">Facility distribution will appear when active records exist.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
                const alertsData = @json($alertsByLevel);
                const resolutionsData = @json($resolutionsOverTime);
                const facilityData = @json($detaineesByFacility);
                
                // Color mapping for alert levels
                const alertColors = {
                    'critical': '#ef4444', // red-500
                    'at_risk': '#f97316', // orange-500
                    'flagged': '#eab308', // yellow-500
                    'monitored': '#3b82f6', // blue-500
                };
                
                const formatLabel = (str) => str.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

                // 1. Alerts by Level Doughnut Chart
                if(Object.keys(alertsData).length > 0) {
                    new Chart(document.getElementById('alertsLevelChart'), {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(alertsData).map(formatLabel),
                            datasets: [{
                                data: Object.values(alertsData),
                                backgroundColor: Object.keys(alertsData).map(k => alertColors[k] || '#9ca3af'),
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: window.matchMedia('(max-width: 639px)').matches ? 'bottom' : 'right' }
                            },
                            cutout: '70%'
                        }
                    });
                }

                // 2. Resolutions Line Chart
                if(Object.keys(resolutionsData).length > 0) {
                    new Chart(document.getElementById('resolutionsChart'), {
                        type: 'line',
                        data: {
                            labels: Object.keys(resolutionsData),
                            datasets: [{
                                label: 'Cases Resolved',
                                data: Object.values(resolutionsData),
                                borderColor: '#10b981', // green-500
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                }

                // 3. Facility Bar Chart
                if(Object.keys(facilityData).length > 0) {
                    new Chart(document.getElementById('facilityChart'), {
                        type: 'bar',
                        data: {
                            labels: Object.keys(facilityData),
                            datasets: [{
                                label: 'Active Detainees',
                                data: Object.values(facilityData),
                                backgroundColor: '#3b82f6', // blue-500
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                }
    });
</script>
@endsection
