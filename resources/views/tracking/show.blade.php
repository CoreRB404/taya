@extends('layouts.public')

@push('styles')
<style>
  .page {
    max-width: 1120px;
    margin: 0 auto;
    padding: 44px 24px 80px;
  }

  .case-head {
    background: rgba(20, 26, 53, 0.98);
    border: 1px solid rgba(42, 51, 99, 1);
    border-radius: 18px;
    padding: 32px 36px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 28px;
  }

  .case-head h1 {
    font-size: 26px;
    margin-bottom: 8px;
  }

  .case-head p {
    font-size: 14px;
    color: rgba(162, 173, 224, 1);
    margin: 0;
    max-width: 520px;
    line-height: 1.55;
  }

  .code-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #1b2244;
    border: 1px solid rgba(42, 51, 99, 1);
    border-radius: 11px;
    padding: 12px 18px;
    white-space: nowrap;
  }

  .code-chip .lbl {
    font-size: 13px;
    color: rgba(107, 118, 168, 1);
  }

  .code-chip .val {
    font-size: 14.5px;
    color: #22d3ee;
    font-weight: 600;
  }

  .alert-banner {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: linear-gradient(135deg, rgba(248, 113, 113, 0.1), rgba(248, 113, 113, 0.03));
    border: 1px solid rgba(248, 113, 113, 0.35);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 28px;
  }

  .alert-banner svg {
    flex-shrink: 0;
    color: #f87171;
    margin-top: 2px;
  }

  .alert-banner h3 {
    font-size: 15px;
    color: #ffb4b4;
    margin-bottom: 4px;
  }

  .alert-banner p {
    font-size: 13.5px;
    color: rgba(162, 173, 224, 1);
    margin: 0;
    line-height: 1.55;
  }

  .grid {
    display: grid;
    grid-template-columns: minmax(280px, 340px) minmax(340px, 1fr);
    grid-auto-rows: auto;
    gap: 28px;
    align-items: start;
  }

  .timeline-panel {
    grid-column: 1 / -1;
  }

  .panel {
    background: rgba(20, 26, 53, 0.98);
    border: 1px solid rgba(42, 51, 99, 1);
    border-radius: 18px;
    padding: 30px 32px;
  }

  .panel h2 {
    font-size: 17px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 9px;
  }

  .panel h2 svg {
    color: #22d3ee;
  }

  .field {
    margin-bottom: 22px;
  }

  .field:last-child {
    margin-bottom: 0;
  }

  .field .k {
    font-size: 11.5px;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(107, 118, 168, 1);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 7px;
  }

  .field .v {
    font-size: 15px;
    font-weight: 500;
  }

  .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 100px;
  }

  .status-pill.active {
    background: rgba(52, 211, 153, 0.12);
    color: #34d399;
  }

  .status-pill.active::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #34d399;
  }

  .timeline {
    position: relative;
    padding-left: 8px;
  }

  .t-item {
    position: relative;
    padding-left: 34px;
    padding-bottom: 26px;
  }

  .t-item:last-child {
    padding-bottom: 0;
  }

  .t-item::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 26px;
    bottom: -4px;
    width: 2px;
    background: rgba(42, 51, 99, 1);
  }

  .t-item:last-child::before {
    display: none;
  }

  .t-dot {
    position: absolute;
    left: 0;
    top: 2px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(42, 51, 99, 1);
    background: rgba(20, 26, 53, 0.98);
    z-index: 2;
  }

  .t-item.completed .t-dot {
    background: #34d399;
    border-color: #34d399;
  }

  .t-item.current .t-dot {
    background: linear-gradient(90deg, #22d3ee, #3b82f6);
    border-color: transparent;
    box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.15);
  }

  .t-item.overdue .t-dot {
    background: #f87171;
    border-color: #f87171;
  }

  .t-item.upcoming .t-dot {
    background: rgba(20, 26, 53, 0.98);
  }

  .t-card {
    background: rgba(26, 32, 68, 0.98);
    border: 1px solid rgba(35, 43, 82, 1);
    border-radius: 13px;
    padding: 18px 20px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
  }

  .t-item.current .t-card {
    border-color: rgba(59, 130, 246, 0.4);
  }

  .t-item.overdue .t-card {
    border-color: rgba(248, 113, 113, 0.4);
  }

  .t-name {
    font-size: 15.5px;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .t-due {
    font-size: 13px;
    color: rgba(107, 118, 168, 1);
  }

  .t-due .over {
    color: #f87171;
    font-weight: 500;
  }

  .t-badge {
    font-size: 12.5px;
    font-weight: 600;
    padding: 4px 11px;
    border-radius: 100px;
    white-space: nowrap;
  }

  .t-badge.completed {
    color: #34d399;
    background: rgba(52, 211, 153, 0.12);
  }

  .t-badge.pending {
    color: #f5b942;
    background: rgba(245, 185, 66, 0.12);
  }

  .t-badge.overdue {
    color: #f87171;
    background: rgba(248, 113, 113, 0.14);
  }

  @media (max-width: 880px) {
    .page {
      padding: 28px 16px 60px;
    }

    .case-head {
      flex-direction: column;
      padding: 26px 22px;
    }

    .grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
<div class="page">
  <div class="case-head">
    <div>
      <h1>Detainee Information</h1>
      <p>This is a read-only view for the detainee's family or authorized contacts. Contact the facility directly for updates beyond what's shown here.</p>
    </div>
    <div class="code-chip">
      <span class="lbl">Code:</span>
      <span class="val mono">{{ $detainee->tracking_code ?? 'TAYA-XXXXXX' }}</span>
    </div>
  </div>

  <div class="alert-banner">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
    <div>
      <h3>Overstay flagged: {{ $latestAlert->title ?? 'Filing of Information' }}</h3>
      <p>{{ $latestAlert->message ?? "This phase was due July 22, 2026 and has now run past its expected duration. If you haven't heard from the facility or counsel, this is a good time to follow up." }}</p>
    </div>
  </div>

  <div class="grid">
    <div class="panel">
      <h2>
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Detainee Profile
      </h2>

      <div class="field">
        <div class="k">Full Name</div>
        <div class="v">{{ $detainee->full_name ?? 'Alyas Bebot' }}</div>
      </div>
      <div class="field">
        <div class="k">Status</div>
        <div class="v"><span class="status-pill active">{{ ucfirst($detainee->status ?? 'active') }}</span></div>
      </div>
      <div class="field">
        <div class="k">Facility</div>
        <div class="v">{{ $detainee->facility->name ?? 'Manila City Jail' }}</div>
      </div>
      <div class="field">
        <div class="k">Commitment Date</div>
        <div class="v">{{ optional($detainee->commitment_date)->format('F d, Y') ?? 'January 01, 2026' }}</div>
      </div>
      <div class="field">
        <div class="k">Days in Custody</div>
        <div class="v">{{ $detainee->days_detained_display ?? '193 days' }}</div>
      </div>
    </div>

    <div class="panel">
      <h2>
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h14v4H5z"/><path d="M5 12h14v8H5z"/></svg>
        Case Details
      </h2>

      <div class="field">
        <div class="k">Primary Charge</div>
        <div class="v">{{ $detainee->penaltyReference ? "[{$detainee->penaltyReference->rpc_code}] {$detainee->penaltyReference->charge_name}" : ($detainee->charge_description ?? 'N/A') }}</div>
      </div>
      <div class="field">
        <div class="k">Charge Description</div>
        <div class="v">{{ $detainee->charge_description ?? 'Not provided' }}</div>
      </div>
      <div class="field">
        <div class="k">Bail Amount</div>
        <div class="v">{{ $detainee->bail_amount_display ?? 'Not set' }}</div>
      </div>
      <div class="field">
        <div class="k">Bail Status</div>
        <div class="v">{{ $detainee->bail_status_label ?: 'Unknown' }}</div>
      </div>
      <div class="field">
        <div class="k">Bail Posted</div>
        <div class="v">{{ optional($detainee->bail_posted_at)->format('F d, Y') ?? 'Not posted' }}</div>
      </div>
      <div class="field">
        <div class="k">Assigned Lawyer</div>
        <div class="v">{{ $latestAlert?->assigned_to ? 'Assigned' : 'Not assigned' }}</div>
      </div>
    </div>

<div class="panel timeline-panel">
      <h2>
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        Timeline
      </h2>

      <div class="timeline">
        @forelse($detainee->phases ?? collect() as $phase)
          @php
            $dueDate = $phase->due_date ? \Carbon\Carbon::parse($phase->due_date) : null;
            $isOverdue = !$phase->completed && $dueDate?->isPast();
            $state = $phase->completed ? 'completed' : ($isOverdue ? 'overdue' : 'upcoming');
          @endphp
          <div class="t-item {{ $state }}">
            <div class="t-dot">
              @if($phase->completed)
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#0d1128" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
              @elseif($isOverdue)
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#0d1128" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4M12 16h.01"/></svg>
              @endif
            </div>
            <div class="t-card">
              <div>
                <div class="t-name">{{ $phase->phase_name }}</div>
                <div class="t-due">Due date: {{ optional($dueDate)->format('F d, Y') ?? 'TBD' }} @if($isOverdue) · <span class="over">running late</span>@endif</div>
              </div>
              <span class="t-badge {{ $phase->completed ? 'completed' : ($isOverdue ? 'overdue' : 'pending') }}">{{ $phase->completed ? 'Completed' : ($isOverdue ? 'Overstay' : 'Pending') }}</span>
            </div>
          </div>
        @empty
          <div style="color: rgba(162, 173, 224, 1);">No timeline information available.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
