@extends('layouts.app')

@section('header', 'Alert Management')

@section('content')
<div class="page-stack mx-auto max-w-5xl">
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back
        </a>
        
        <a href="{{ route('reports.alert', $alert) }}" target="_blank" class="btn-secondary py-1.5 text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Download PDF Report
        </a>
    </div>

    <!-- Alert Header Banner -->
    <div class="glass-panel overflow-hidden border-t-4 border-{{ $alert->color_class }}-500">
        <div class="flex flex-col justify-between gap-4 p-4 sm:p-6 md:flex-row md:items-center">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="badge badge-{{ $alert->alert_level }} px-3 py-1 text-sm font-bold tracking-wider uppercase">
                        {{ str_replace('_', ' ', $alert->alert_level) }} ALERT
                    </span>
                    <span class="text-sm text-gray-500">Generated on {{ $alert->created_at->format('M d, Y H:i') }}</span>
                </div>
                <h2 class="mb-1 text-xl font-bold text-gray-900 sm:text-2xl">
                    <a href="{{ route('detainees.show', $alert->detainee) }}" class="hover:text-taya-accent transition-colors">
                        {{ $alert->detainee->full_name }}
                    </a>
                </h2>
                <p class="text-gray-600">{{ $alert->detainee->facility->name }}</p>
            </div>
            
            <div class="w-full rounded-lg border border-gray-100 bg-gray-50 p-4 md:w-auto md:min-w-[230px]">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Current Assignment</p>
                @if($alert->assigned_to)
                    <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">{{ $alert->assignedUser?->name ?? 'Unassigned' }}</span>
                            <span class="text-xs text-gray-500">{{ $alert->assignedUser?->role ? ucwords(str_replace('_', ' ', $alert->assignedUser->role)) : '' }}</span>
                    </div>
                @else
                    <span class="text-sm text-red-500 font-medium flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Unassigned
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-4 md:col-span-2">
            
            <!-- Recommended Action -->
            <div class="glass-panel border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 sm:p-5">
                <h3 class="text-lg font-bold text-blue-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Recommended Legal Action
                </h3>
                <p class="text-blue-800 text-lg">{{ $alert->recommended_action ?: 'No recommendation available.' }}</p>

                @php
                    $completedPhases = $alert->detainee->phases->where('completed', true)->count();
                    $totalPhases = $alert->detainee->phases->count();
                    $allPhasesComplete = $totalPhases > 0 && $completedPhases === $totalPhases;
                @endphp

                <div class="mt-4 rounded-xl border border-blue-200 bg-white/70 p-4 text-sm text-blue-900">
                    <p class="font-semibold">Phase status</p>
                    <p class="mt-1 text-blue-800">
                        {{ $allPhasesComplete ? 'All phases are completed.' : "{$completedPhases}/{$totalPhases} phases completed." }}
                        @if($allPhasesComplete)
                            Admins may resolve this alert directly.
                        @else
                            Resolution requires override from the detainee record.
                        @endif
                    </p>
                </div>
                
                @if(!$alert->resolved_at && auth()->user()->can('resolve', $alert))
                    <div class="mt-6 pt-4 border-t border-blue-200">
                        @if($allPhasesComplete)
                            <form action="{{ route('alerts.resolve', $alert) }}" method="POST" onsubmit="return confirm('Mark this alert as completely resolved? This means legal action has succeeded or the detainee was released.');" class="flex items-center gap-3">
                                @csrf
                                <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700 shadow-green-500/30 flex justify-center items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Mark Alert as Resolved
                                </button>
                            </form>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                <p class="font-semibold">This alert cannot be resolved from the queue.</p>
                                <p class="mt-2">The detainee still has incomplete phases. Please resolve this alert from the detainee profile if you need to override resolution.</p>
                            </div>
                        @endif
                    </div>
                @elseif($alert->resolved_at)
                    <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Resolved on {{ $alert->resolved_at->format('M d, Y H:i') }}
                    </div>
                @endif
            </div>

            <!-- Log Legal Action -->
            @if(!$alert->resolved_at && auth()->user()->can('assign', $alert))
                <div class="glass-panel p-4 sm:p-5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Log New Legal Action</h3>
                    <form action="{{ route('detainees.legal-actions.store', $alert->detainee) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="alert_id" value="{{ $alert->id }}">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Action Type</label>
                                <select name="action_type" required class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent">
                                    <option value="">Select action taken...</option>
                                    <option value="motion_for_release">Motion for Release</option>
                                    <option value="habeas_corpus">Habeas Corpus Petition</option>
                                    <option value="pao_referral">Referred to PAO</option>
                                    <option value="ngo_referral">Referred to NGO</option>
                                    <option value="case_review">Case Review / Hearing Scheduled</option>
                                    <option value="other">Other Intervention</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes / Remarks</label>
                            <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent" placeholder="Add specific details, dates, or outcomes..."></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">Save Legal Action</button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Legal Action History -->
            <div class="glass-panel overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-semibold text-gray-900">Intervention History</h3>
                </div>
                <div class="p-4 sm:p-5">
                    @forelse($alert->legalActions as $action)
                        <div class="mb-5 last:mb-0 relative pl-6 border-l-2 border-gray-200 pb-5 last:pb-0">
                            <div class="absolute w-3 h-3 bg-white border-2 border-taya-accent rounded-full -left-[7px] top-1.5"></div>
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-gray-900">{{ ucwords(str_replace('_', ' ', $action->action_type)) }}</h4>
                                <span class="text-xs font-medium text-gray-500">{{ $action->filed_at->format('M d, Y') }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mb-2">Filed by: {{ $action->filedByUser->name }}</p>
                            @if($action->notes)
                                <div class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700 border border-gray-100 shadow-sm">{{ $action->notes }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500">
                            <p>No legal interventions have been recorded for this alert yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Sidebar (Actions & Overstay Details) -->
        <div class="space-y-4">
            
            <!-- Assignment (Admin only) -->
            @if(!$alert->resolved_at && (auth()->user()->canManageOperations() || auth()->user()->isLawyer()))
                <div class="glass-panel p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Assign Case</h3>
                    <form action="{{ route('alerts.assign', $alert) }}" method="POST" class="flex flex-col gap-3">
                        @csrf
                        <select name="assigned_to" required class="block w-full rounded-md border-gray-300 text-sm focus:ring-taya-accent focus:border-taya-accent">
                            <option value="">Select a lawyer...</option>
                            @foreach($lawyers as $lawyer)
                                <option value="{{ $lawyer->id }}" {{ $alert->assigned_to == $lawyer->id ? 'selected' : '' }}>
                                    {{ $lawyer->name }} ({{ str_replace('_', ' ', $lawyer->role) }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-secondary w-full py-2">Update Assignment</button>
                    </form>
                </div>
            @endif

            <!-- Overstay Breakdown -->
            <div class="glass-panel p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Computation Breakdown</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Primary Charge</dt>
                        <dd class="font-medium text-gray-900 text-right w-1/2">{{ $alert->detainee->penaltyReference ? $alert->detainee->penaltyReference->rpc_code.' - '.$alert->detainee->penaltyReference->charge_name : ($alert->detainee->charge_description ?? 'N/A') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Max Penalty</dt>
                        <dd class="font-medium text-gray-900">{{ $alert->computation?->max_penalty_days !== null ? $alert->computation->max_penalty_display : 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Time Served</dt>
                        <dd class="font-medium text-gray-900">{{ $alert->computation?->days_detained !== null ? $alert->computation->days_detained_display : 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-gray-100">
                        <dt class="font-semibold text-gray-900">Overstay</dt>
                        <dd class="font-bold {{ ($alert->computation?->overstay_days ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $alert->computation?->overstay_days !== null ? $alert->computation->overstay_days_display : 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Admin Override -->
            @can('override', $alert)
                <div class="glass-panel p-5" x-data="{ open: false }">
                    <button @click="open = !open" class="text-sm font-semibold text-gray-900 w-full text-left flex justify-between items-center">
                        Admin Tools: Override Alert
                        <svg class="w-4 h-4 transform transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open" class="mt-4 pt-4 border-t border-gray-100">
                        <form action="{{ route('alerts.override', $alert) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Force Alert Level</label>
                                <select name="alert_level" required class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                    <option value="critical">Critical</option>
                                    <option value="at_risk">At Risk</option>
                                    <option value="flagged">Flagged</option>
                                    <option value="monitored">Monitored</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Override Reason (Required)</label>
                                <textarea name="override_note" required rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm text-red-900 focus:ring-red-500 focus:border-red-500"></textarea>
                            </div>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white text-sm rounded-lg font-medium shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to manually override the computed alert level?');">
                                Apply Override
                            </button>
                        </form>
                    </div>
                </div>
            @endcan

        </div>
    </div>
</div>
@endsection
