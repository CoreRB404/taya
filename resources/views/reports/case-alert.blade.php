<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Case Alert #{{ $alert->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a365d; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1a365d; margin: 0; }
        .subtitle { font-size: 12px; color: #666; margin-top: 5px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .badge-critical { background-color: #fca5a5; color: #991b1b; }
        .badge-at_risk { background-color: #fdba74; color: #9a3412; }
        .badge-flagged { background-color: #fde047; color: #854d0e; }
        .badge-monitored { background-color: #bfdbfe; color: #1e40af; }
        .badge-resolved { background-color: #bbf7d0; color: #166534; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; font-weight: bold; color: #4b5563; width: 35%; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #1a365d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">TAYA - System Case Alert Report</h1>
        <div class="subtitle">Generated on {{ now()->format('F j, Y, g:i a') }}</div>
    </div>

    <div class="section-title">Alert Overview</div>
    <table>
        <tr>
            <th>Alert ID</th>
            <td>#{{ str_pad($alert->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <th>Status/Severity</th>
            <td>
                <span class="badge badge-{{ $alert->alert_level }}">
                    {{ str_replace('_', ' ', $alert->alert_level) }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Generated Date</th>
            <td>{{ $alert->created_at->format('M d, Y g:i A') }}</td>
        </tr>
        <tr>
            <th>Assigned To</th>
            <td>{{ $alert->assignedUser ? $alert->assignedUser->name : 'Unassigned' }}</td>
        </tr>
    </table>

    <div class="section-title">Detainee Profile</div>
    <table>
        <tr>
            <th>Name</th>
            <td>{{ $alert->detainee->full_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Facility</th>
            <td>{{ $alert->detainee->facility->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Commitment Date</th>
            <td>{{ $alert->detainee->commitment_date?->format('M d, Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Charge Code</th>
            <td>{{ $alert->detainee->charge_rpc_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Charge Description</th>
            <td>{{ $alert->detainee->charge_description ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Legal & Penalty Information</div>
    <table>
        <tr>
            <th>Penalty Reference</th>
            <td>{{ $alert->detainee->penaltyReference ? $alert->detainee->penaltyReference->rpc_code . ' - ' . $alert->detainee->penaltyReference->charge_name : ($alert->detainee->charge_description ?? 'N/A') }}</td>
        </tr>
        <tr>
            <th>Max Penalty (Days)</th>
            <td>{{ $alert->computation?->max_penalty_days !== null ? $alert->computation->max_penalty_display : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Time Served (Days)</th>
            <td>{{ $alert->computation?->days_detained !== null ? $alert->computation->days_detained_display : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Estimated Overstay (Days)</th>
            <td style="color: #dc2626; font-weight: bold;">{{ $alert->computation?->overstay_days !== null ? $alert->computation->overstay_days_display : 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Action Notes & Details</div>
    <div style="background-color: #f9fafb; padding: 10px; border: 1px solid #e5e7eb; border-radius: 4px;">
        <p style="margin: 0; white-space: pre-wrap;">{{ $alert->recommended_action ?: 'No additional details provided.' }}</p>
        @if($alert->override_note)
            <p style="margin: 8px 0 0; white-space: pre-wrap;"><strong>Override Note:</strong> {{ $alert->override_note }}</p>
        @endif
    </div>

</body>
</html>
