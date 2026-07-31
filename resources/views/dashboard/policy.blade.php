@extends('layouts.app')

@section('header', 'Policy Advocate Dashboard')

@section('content')
<div class="space-y-6" x-data="policyDashboard()">
    
    <div class="flex justify-end gap-3 mb-6">
        <button @click="printReport()" class="btn-secondary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Report
        </button>
        <a href="{{ route('reports.analytics', ['export' => 'json']) }}" target="_blank" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export JSON Data
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="glass-panel p-6 flex flex-col items-center text-center">
            <div class="p-3 bg-orange-100 text-orange-600 rounded-xl mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM6.84 4.34L4.22 6.96A9.956 9.956 0 002 12c0 2.76 1.12 5.26 2.93 7.07C6.74 21.88 9.24 23 12 23s5.26-1.12 7.07-2.93C21.88 17.26 23 14.76 23 12c0-2.76-1.12-5.26-2.93-7.07L17.16 4.34A9.748 9.748 0 0012 2c-1.94 0-3.76.63-5.16 1.74z"></path></svg>
            </div>
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Unable to Pay Bail</p>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($unableToPayBail) }}</p>
        </div>

        <div class="glass-panel p-6 flex flex-col items-center text-center">
            <div class="p-3 bg-yellow-100 text-yellow-600 rounded-xl mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19.5l9-6 9 6M3 7.5l9 6 9-6"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Overcrowded Facilities</p>
            <p class="text-4xl font-bold text-gray-900 mt-2">{{ number_format($overcrowdedFacilities) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Alerts by Level -->
        <div class="glass-panel p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Active Alerts by Severity</h3>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="alertsLevelChart"></canvas>
            </div>
        </div>

        <!-- Resolutions Over Time -->
        <div class="glass-panel p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Cases Resolved (Last 6 Months)</h3>
            <div class="relative h-64 w-full">
                <canvas id="resolutionsChart"></canvas>
            </div>
        </div>

        <!-- Detainees by Facility -->
        <div class="glass-panel p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Detainee Distribution by Facility</h3>
            <div class="relative h-80 w-full">
                <canvas id="facilityChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('policyDashboard', () => ({
            init() {
                this.renderCharts();
            },
            
            printReport() {
                window.print();
            },
            
            renderCharts() {
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
                                legend: { position: 'right' }
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
            }
        }))
    });
</script>
@endsection
