<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detainee Profile - {{ $detainee->full_name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a365d; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1a365d; margin: 0; }
        .subtitle { font-size: 12px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background-color: #f9fafb; font-weight: bold; color: #4b5563; width: 35%; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #1a365d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">TAYA - Detainee Profile Report</h1>
        <div class="subtitle">Generated on {{ now()->format('F j, Y, g:i a') }}</div>
    </div>

    <div class="section-title">Profile Overview</div>
    <table>
        <tr>
            <th>Name</th>
            <td>{{ $detainee->full_name }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($detainee->status) }}</td>
        </tr>
        <tr>
            <th>Facility</th>
            <td>{{ $detainee->facility->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Commitment Date</th>
            <td>{{ $detainee->commitment_date?->format('M d, Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Charge</th>
            <td>{{ $detainee->penaltyReference ? $detainee->penaltyReference->rpc_code . ' - ' . $detainee->penaltyReference->charge_name : ($detainee->charge_description ?? 'N/A') }}</td>
        </tr>
    </table>

    <div class="section-title">Overstay Summary</div>
    <table>
        <tr>
            <th>Days Detained</th>
            <td>{{ $latestComputation?->days_detained !== null ? $latestComputation->days_detained_display : $detainee->days_detained_display }}</td>
        </tr>
        <tr>
            <th>Max Penalty</th>
            <td>{{ $latestComputation?->max_penalty_days !== null ? $latestComputation->max_penalty_display : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Overstay</th>
            <td>{{ $latestComputation?->overstay_days !== null ? $latestComputation->overstay_days_display : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Latest Alert</th>
            <td>{{ $latestAlert ? str_replace('_', ' ', $latestAlert->alert_level) : 'None' }}</td>
        </tr>
        <tr>
            <th>Recommended Action</th>
            <td>{{ $latestAlert?->recommended_action ?? 'No recommendation available.' }}</td>
        </tr>
    </table>

    <div class="section-title">Recent Phases</div>
    <table>
        <tr>
            <th>Phase</th>
            <th>Status</th>
            <th>Due Date</th>
        </tr>
        @foreach($detainee->phases->take(4) as $phase)
            <tr>
                <td>Phase {{ $phase->phase_number }}: {{ $phase->phase_name }}</td>
                <td>{{ $phase->completed ? 'Completed' : ($phase->is_overdue ? 'Overdue' : 'Pending') }}</td>
                <td>{{ $phase->due_date?->format('M d, Y') ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>