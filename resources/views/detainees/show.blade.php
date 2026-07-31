@extends('layouts.app')

@section('header', 'Detainee Profile')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header with Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('detainees.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 text-sm font-medium transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Database
            </a>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                {{ $detainee->full_name }}
                @if($detainee->status === 'active')
                    <span class="badge bg-green-100 text-green-800">Active</span>
                @else
                    <span class="badge bg-gray-100 text-gray-800">{{ ucfirst($detainee->status) }}</span>
                @endif
            </h2>
            <p class="text-sm text-gray-500 mt-1">ID: {{ str_pad($detainee->id, 6, '0', STR_PAD_LEFT) }} | {{ $detainee->facility->name }}</p>
            @if($detainee->tracking_code)
                <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-taya-accent/20 bg-taya-accent/10 px-3 py-1 text-sm font-medium text-taya-accent">
                    <span>Tracking Code:</span>
                    <span class="font-semibold">{{ $detainee->tracking_code }}</span>
                </div>
            @endif
        </div>
        
        @can('update', $detainee)
            <div class="flex gap-2">
                <a href="{{ route('reports.detainee', $detainee) }}" target="_blank" class="btn-secondary">
                    Download PDF
                </a>
                <a href="{{ route('detainees.edit', $detainee) }}" class="btn-secondary">
                    Edit Details
                </a>
                @if($detainee->status === 'active')
                    @php
                        $totalPhases = $detainee->phases()->count();
                        $completedPhases = $detainee->phases()->where('completed', true)->count();
                        $allPhasesComplete = $totalPhases > 0 && $completedPhases === $totalPhases;
                        $hasUnresolvedAlerts = $detainee->alerts()->whereNull('resolved_at')->exists();
                        $requiresOverride = !$allPhasesComplete || $hasUnresolvedAlerts;
                    @endphp
                    <div x-data="{ showReleaseForm: false }" class="space-y-2">
                        @if(!$requiresOverride)
                            <form action="{{ route('detainees.release', $detainee) }}" method="POST" onsubmit="return confirm('Mark this detainee as released? This will resolve any open alerts automatically.');">
                                @csrf
                                <button type="submit" class="btn-secondary text-amber-600 hover:text-amber-700 hover:bg-amber-50">
                                    Mark as Released
                                </button>
                            </form>
                        @else
                            <div class="space-y-2">
                                <button type="button" @click="showReleaseForm = true" class="btn-secondary text-amber-600 hover:text-amber-700 hover:bg-amber-50">
                                    Mark as Released (Override)
                                </button>
                                <form action="{{ route('detainees.release', $detainee) }}" method="POST" x-show="showReleaseForm" x-cloak class="rounded-xl border border-amber-200 bg-amber-50 p-4 transition duration-150">
                                    @csrf
                                    <p class="text-sm font-semibold text-amber-900">Override release requires password confirmation.</p>
                                    @if($hasUnresolvedAlerts && !$allPhasesComplete)
                                        <p class="text-xs text-amber-700 mt-1">Detainee has completed {{ $completedPhases }} of {{ $totalPhases }} phases and has unresolved alerts.</p>
                                    @elseif($hasUnresolvedAlerts)
                                        <p class="text-xs text-amber-700 mt-1">Detainee has unresolved alerts.</p>
                                    @else
                                        <p class="text-xs text-amber-700 mt-1">Detainee has completed {{ $completedPhases }} of {{ $totalPhases }} phases.</p>
                                    @endif
                                    <div class="mt-4">
                                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                        <input id="current_password" name="current_password" type="password" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-taya-accent focus:ring-taya-accent text-sm" autocomplete="current-password">
                                        @error('current_password')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="mt-4 flex gap-2">
                                        <button type="submit" class="btn-secondary bg-amber-600 hover:bg-amber-700 text-white py-2 px-4">Confirm Release</button>
                                        <button type="button" @click="showReleaseForm = false" class="btn-secondary bg-gray-100 text-gray-700 py-2 px-4">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                    <form action="{{ route('detainees.destroy', $detainee) }}" method="POST" onsubmit="return confirm('Archive this record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary text-red-600 hover:text-red-700 hover:bg-red-50">
                            Archive
                        </button>
                    </form>
                @endif
            </div>
        @endcan
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Left Column: Primary Info & Overstay -->
        <div class="xl:col-span-1 space-y-6">
            
            <!-- Overstay Alert Card -->
            <div class="glass-panel overflow-hidden border-2 @if($latestAlert && $latestAlert->alert_level === 'critical') border-red-500 @elseif($latestAlert && $latestAlert->alert_level === 'at_risk') border-orange-500 @else border-transparent @endif">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Overstay Computation</h3>
                    
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Alert Level</span>
                        @if($latestAlert)
                            <span class="badge badge-{{ $latestAlert->alert_level }} text-sm px-3 py-1 uppercase font-bold tracking-wider">
                                {{ str_replace('_', ' ', $latestAlert->alert_level) }}
                            </span>
                        @else
                            <span class="badge badge-monitored">MONITORED</span>
                        @endif
                    </div>
                    
                    <div class="space-y-4 mt-6">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500">Days Detained</span>
                                <span class="font-medium text-gray-900">{{ $latestComputation ? $latestComputation->days_detained_display : $detainee->days_detained_display }}</span>
                            </div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500">Max Penalty</span>
                                <span class="font-medium text-gray-900">{{ $latestComputation ? $latestComputation->max_penalty_display : $detainee->penaltyReference->penalty_duration_display }}</span>
                            </div>
                            <div class="flex justify-between text-sm mb-1 pt-2 border-t border-gray-100">
                                <span class="font-medium text-gray-900">Overstay Days</span>
                                <span class="font-bold {{ ($latestComputation && $latestComputation->overstay_days > 0) ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $latestComputation ? $latestComputation->overstay_days_display : '0 days' }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        @php
                            $days = $latestComputation ? $latestComputation->days_detained : $detainee->days_detained;
                            $max = $latestComputation ? $latestComputation->max_penalty_days : ($detainee->penaltyReference->max_penalty_years * 365);
                            $pct = $max > 0 ? min(100, ($days / $max) * 100) : 100;
                            
                            $colorClass = 'bg-blue-500';
                            if($pct >= 100) $colorClass = 'bg-red-500';
                            elseif($pct >= 75) $colorClass = 'bg-orange-500';
                            elseif($pct >= 50) $colorClass = 'bg-yellow-500';
                        @endphp
                        <div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="{{ $colorClass }} h-2.5 rounded-full transition-all duration-1000" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="text-xs text-right mt-1 text-gray-500">{{ round($pct) }}% of max penalty served</p>
                        </div>
                    </div>

                    @if($latestAlert && $latestAlert->recommended_action)
                    <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Recommended Action</h4>
                        <p class="text-sm text-gray-800">{{ $latestAlert->recommended_action }}</p>
                        
                        @if($latestAlert->assigned_to)
                            <div class="mt-3 flex items-center gap-2 pt-3 border-t border-gray-200">
                                <span class="text-xs text-gray-500">Assigned to:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $latestAlert->assignedUser->name }}</span>
                            </div>
                        @else
                            <div class="mt-3 pt-3 border-t border-gray-200 text-center">
                                <a href="{{ route('alerts.show', $latestAlert) }}" class="text-xs font-medium text-taya-accent hover:underline">View Alert Details & Assign &rarr;</a>
                            </div>
                        @endif
                    </div>
                    @endif

                    @if($latestAlert)
                        @php
                            $totalPhases = $detainee->phases->count();
                            $completedPhases = $detainee->phases->where('completed', true)->count();
                            $allPhasesComplete = $totalPhases > 0 && $totalPhases === $completedPhases;
                        @endphp
                        
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            @if(!$latestAlert->resolved_at)
                                <div x-data="{ showOverrideForm: false }">
                                    <form action="{{ route('alerts.resolve', $latestAlert) }}" method="POST" onsubmit="return confirm('Resolve this alert?');">
                                        @csrf
                                        @if($allPhasesComplete)
                                            <button type="submit" class="w-full btn-primary bg-green-600 hover:bg-green-700 shadow-green-500/30 py-2 flex justify-center items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Mark Alert as Resolved
                                            </button>
                                        @else
                                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 mb-4">
                                                <p class="font-semibold">Override resolution requires confirmation.</p>
                                                <p class="mt-2">The detainee has only completed {{ $completedPhases }} of {{ $totalPhases }} phases.</p>
                                            </div>

                                            <button type="button" @click="showOverrideForm = true" class="w-full btn-primary bg-amber-600 hover:bg-amber-700 shadow-amber-500/30 py-2 flex justify-center items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Override and Resolve Alert
                                            </button>

                                            <div x-show="showOverrideForm" x-cloak class="mt-4 rounded-xl border border-amber-200 bg-white p-4 shadow-sm">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                                        <input id="current_password" name="current_password" type="password" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-taya-accent focus:border-taya-accent text-sm" autocomplete="current-password">
                                                        @error('current_password')
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button type="submit" class="w-full btn-primary bg-amber-600 hover:bg-amber-700 shadow-amber-500/30 py-2 flex justify-center items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            Confirm Override Resolve
                                                        </button>
                                                        <button type="button" @click="showOverrideForm = false" class="w-full btn-secondary bg-gray-100 text-gray-700 border-gray-300 py-2">Cancel</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            @else
                                <div class="w-full text-center px-3 py-2 rounded-lg bg-green-50 text-green-800 text-sm font-medium border border-green-100">
                                    <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Resolved on {{ $latestAlert->resolved_at->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Case Details -->
            <div class="glass-panel p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Case Details</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Commitment Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $detainee->commitment_date->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Bail Amount</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $detainee->bail_amount_display }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Bail Status</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $detainee->bail_status_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Bail Posted Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ optional($detainee->bail_posted_at)->format('F d, Y') ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Primary Charge (RPC/RA)</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">[{{ $detainee->penaltyReference->rpc_code }}] {{ $detainee->penaltyReference->charge_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Specific Charge Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $detainee->charge_description }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Max Imposable Penalty</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $detainee->penaltyReference->max_penalty_years }} years</dd>
                    </div>
                </dl>
            </div>
            
        </div>

        <!-- Middle Column: Phase Tracker & Legal Actions -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- Phase Compliance Tracker Widget -->
            <div class="glass-panel overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Phase Compliance Tracker</h3>
                    <span class="badge bg-indigo-100 text-indigo-800">4 Stages</span>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @foreach($detainee->phases as $phase)
                        @php
                            $isOverdue = $phase->is_overdue;
                            $isUpcoming = !$phase->completed && !$isOverdue;
                            $statusColor = $phase->completed ? 'green' : ($isOverdue ? 'red' : 'blue');
                            $daysOverdue = $isOverdue ? (int) $phase->due_date->diffInDays(now()) : 0;
                            $daysLeft = !$phase->completed && !$isOverdue ? (int) now()->diffInDays($phase->due_date) : 0;
                        @endphp
                        <div class="p-5" x-data="{ showReasonForm: false, showDetails: false }">
                            {{-- Phase Header Row --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    {{-- Status Icon --}}
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-600 shrink-0">
                                        @if($phase->completed)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        @elseif($isOverdue)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                        @else
                                            <span class="font-bold text-sm">{{ $phase->phase_number }}</span>
                                        @endif
                                    </div>
                                    {{-- Phase Title --}}
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-base">Phase {{ $phase->phase_number }}: {{ $phase->phase_name }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Due: {{ $phase->due_date->format('F d, Y') }}
                                            @if($phase->completed)
                                                · Completed {{ $phase->completed_at->format('M d, Y') }}
                                                @if($phase->completed_at->startOfDay() > $phase->due_date->startOfDay())
                                                    <span class="text-amber-600 font-semibold ml-1">(Late by {{ $phase->due_date->startOfDay()->diffInDays($phase->completed_at->startOfDay()) }} days)</span>
                                                @endif
                                            @elseif($isOverdue)
                                                · <span class="text-red-600 font-semibold">{{ $daysOverdue }} days overdue</span>
                                            @else
                                                · <span class="text-blue-600">{{ $daysLeft }} days remaining</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                {{-- Status Badge + View Details Button --}}
                                <div class="flex items-center gap-2 ml-13 sm:ml-0">
                                    @if($phase->completed)
                                        <span class="badge bg-green-100 text-green-800 text-xs">✓ Completed</span>
                                    @elseif($isOverdue)
                                        <span class="badge bg-red-100 text-red-800 text-xs animate-pulse">⚠ Overdue {{ $daysOverdue }}d</span>
                                    @else
                                        <span class="badge bg-blue-100 text-blue-800 text-xs">◷ Upcoming</span>
                                    @endif
                                    <button @click="showDetails = !showDetails" 
                                            class="btn-secondary py-1.5 px-3 text-xs flex items-center gap-1">
                                        <svg class="w-4 h-4 transition-transform" :class="showDetails && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        <span x-text="showDetails ? 'Hide Details' : 'View Details'">View Details</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Expandable Details Panel --}}
                            <div x-show="showDetails" x-collapse x-cloak class="mt-4 ml-0 sm:ml-13">
                                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                                    {{-- Phase Info Grid --}}
                                    <div class="p-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-b border-gray-100 bg-gray-50/50">
                                        <div>
                                            <span class="block text-xs text-gray-500 uppercase tracking-wider font-medium">Allowed Time</span>
                                            <span class="font-bold text-gray-900">{{ $phase->day_count }} days</span>
                                        </div>
                                        <div>
                                            <span class="block text-xs text-gray-500 uppercase tracking-wider font-medium">Due Date</span>
                                            <span class="font-bold {{ $isOverdue ? 'text-red-600' : 'text-gray-900' }}">{{ $phase->due_date->format('M d, Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-xs text-gray-500 uppercase tracking-wider font-medium">Status</span>
                                            @if($phase->completed)
                                                @if($phase->completed_at->startOfDay() > $phase->due_date->startOfDay())
                                                    <span class="font-bold text-amber-600">Completed Late</span>
                                                @else
                                                    <span class="font-bold text-green-600">Completed on Time</span>
                                                @endif
                                            @elseif($isOverdue)
                                                <span class="font-bold text-red-600">{{ $daysOverdue }} days overdue</span>
                                            @else
                                                <span class="font-bold text-blue-600">{{ $daysLeft }} days left</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="block text-xs text-gray-500 uppercase tracking-wider font-medium">
                                                @if($phase->completed) Completed On @else Started From @endif
                                            </span>
                                            @if($phase->completed)
                                                <span class="font-bold text-green-600">{{ $phase->completed_at->format('M d, Y') }}</span>
                                            @else
                                                <span class="font-bold text-gray-900">{{ $detainee->commitment_date->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Delay Justification (always visible when exists, even if completed) --}}
                                    @if($phase->flag_reason)
                                        <div class="p-4 bg-amber-50 border-b border-amber-100">
                                            <div class="flex items-start gap-3">
                                                <div class="p-1.5 bg-amber-100 rounded-lg shrink-0">
                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-1">Delay Justification / Reason</p>
                                                    <p class="text-sm text-amber-900 leading-relaxed">{{ $phase->flag_reason }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($isOverdue && !$phase->flag_reason)
                                        <div class="p-4 bg-red-50 border-b border-red-100">
                                            <div class="flex items-center gap-2 text-sm text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                                                <span class="font-semibold">No justification provided yet.</span> This phase is overdue and requires a reason for the delay.
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    @if(!$phase->completed && auth()->user()->can('complete', $phase))
                                        <div class="p-4 space-y-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if($isOverdue)
                                                    <button @click="showReasonForm = !showReasonForm" 
                                                            class="inline-flex items-center gap-1.5 py-2 px-3 text-xs font-medium rounded-lg border transition-colors
                                                            {{ $phase->flag_reason 
                                                                ? 'border-gray-300 text-gray-700 hover:bg-gray-50' 
                                                                : 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                        {{ $phase->flag_reason ? 'Update Reason' : 'State Reason for Delay' }}
                                                    </button>
                                                @endif

                                                <form action="{{ route('phases.complete', $phase) }}" method="POST" onsubmit="return confirm('Mark Phase {{ $phase->phase_number }} as complete?');">
                                                    @csrf
                                                    <button type="submit" class="btn-primary py-2 px-3 text-xs">
                                                        ✓ Mark as Complete
                                                    </button>
                                                </form>
                                            </div>
                                            
                                            {{-- Reason Form --}}
                                            <div x-show="showReasonForm" x-collapse x-cloak>
                                                <form action="{{ route('phases.flag', $phase) }}" method="POST" class="p-4 rounded-lg border border-amber-200 bg-amber-50/50 space-y-3">
                                                    @csrf
                                                    <label class="block text-sm font-semibold text-gray-800">
                                                        Why is this phase delayed? <span class="text-red-500">*</span>
                                                    </label>
                                                    <textarea name="flag_reason" rows="3" required
                                                              placeholder="e.g. Waiting for prosecutor's resolution, court calendar is full, pending witness availability, respondent not located..."
                                                              class="w-full rounded-lg border-gray-300 text-sm focus:ring-amber-500 focus:border-amber-500 placeholder:text-gray-400">{{ $phase->flag_reason }}</textarea>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" @click="showReasonForm = false" class="btn-secondary py-2 px-4 text-xs">Cancel</button>
                                                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white py-2 px-4 text-xs rounded-lg font-semibold transition-colors shadow-sm">
                                                            Save Reason
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Documents -->
            <div class="glass-panel overflow-hidden" x-data="{ openUpload: {{ $errors->hasAny(['file', 'doc_type', 'phase_number']) ? 'true' : 'false' }} }">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Court Documents</h3>
                    @can('create', [\App\Models\Document::class, $detainee])
                        <button @click="openUpload = !openUpload" class="btn-secondary text-sm py-1.5">
                            Upload File
                        </button>
                    @endcan
                </div>

                <!-- Upload Form -->
                <div x-show="openUpload" x-collapse class="border-b border-gray-100 bg-gray-50 p-5">
                    <form action="{{ route('detainees.documents.store', $detainee) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @if(session('error'))
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                                <p class="font-semibold">{{ session('error') }}</p>
                            </div>
                        @endif
                        @if($errors->hasAny(['file', 'doc_type', 'phase_number']))
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                                <p class="font-semibold">Upload failed. Please fix the errors below.</p>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Document Type</label>
                                <select name="doc_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taya-accent focus:ring-taya-accent text-sm">
                                    <option value="commitment_order" {{ old('doc_type') === 'commitment_order' ? 'selected' : '' }}>Commitment Order</option>
                                    <option value="charge_sheet" {{ old('doc_type') === 'charge_sheet' ? 'selected' : '' }}>Charge Sheet / Information</option>
                                    <option value="court_record" {{ old('doc_type') === 'court_record' ? 'selected' : '' }}>Court Order / Resolution</option>
                                    <option value="other" {{ old('doc_type') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('doc_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Related Phase (Optional)</label>
                                <select name="phase_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taya-accent focus:ring-taya-accent text-sm">
                                    <option value="" {{ old('phase_number') === null ? 'selected' : '' }}>None</option>
                                    <option value="1" {{ old('phase_number') === '1' ? 'selected' : '' }}>Phase 1: Prelim Investigation</option>
                                    <option value="2" {{ old('phase_number') === '2' ? 'selected' : '' }}>Phase 2: Filing of Info</option>
                                    <option value="3" {{ old('phase_number') === '3' ? 'selected' : '' }}>Phase 3: Arraignment</option>
                                    <option value="4" {{ old('phase_number') === '4' ? 'selected' : '' }}>Phase 4: Pre-Trial</option>
                                </select>
                                @error('phase_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">File (PDF/Image, Max 10MB)</label>
                                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-taya-accent hover:file:bg-blue-100">
                                @error('file')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="openUpload = false" class="btn-secondary py-1.5 px-3 text-sm">Cancel</button>
                            <button type="submit" class="btn-primary py-1.5 px-3 text-sm">Upload</button>
                        </div>
                    </form>
                </div>

                <!-- Document List -->
                <ul class="divide-y divide-gray-100">
                    @forelse($detainee->documents as $doc)
                        <li class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 uppercase">{{ str_replace('_', ' ', $doc->doc_type) }}</p>
                                    <p class="text-xs text-gray-500">
                                        Uploaded by {{ $doc->uploadedByUser->name }} on {{ $doc->uploaded_at->format('M d, Y') }}
                                        @if($doc->phase_number) &bull; Phase {{ $doc->phase_number }} @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('detainees.documents.show', [$detainee, $doc]) }}" target="_blank" class="text-taya-accent hover:text-taya-accent-dark text-sm font-medium">Download</a>
                                @can('delete', $doc)
                                    <form action="{{ route('detainees.documents.destroy', [$detainee, $doc]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 ml-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </li>
                    @empty
                        <li class="p-6 text-center text-gray-500 text-sm">No documents uploaded yet.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Legal Actions Log -->
            <div class="glass-panel overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-semibold text-gray-900">Legal Actions History</h3>
                </div>
                <div class="p-5">
                    @forelse($detainee->legalActions as $action)
                        <div class="mb-4 last:mb-0 relative pl-4 border-l-2 border-taya-accent">
                            <div class="absolute w-2.5 h-2.5 bg-taya-accent rounded-full -left-[5px] top-1"></div>
                            <p class="text-sm font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $action->action_type)) }}</p>
                            <p class="text-xs text-gray-500 mb-1">Filed by {{ $action->filedByUser->name }} on {{ $action->filed_at->format('M d, Y') }}</p>
                            @if($action->notes)
                                <div class="text-sm text-gray-700 bg-gray-50 p-2 rounded border border-gray-100 mt-2">{{ $action->notes }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-gray-500 text-sm">No legal actions have been recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
