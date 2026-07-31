<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facility Report - {{ $facility->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a365d; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1a365d; margin: 0; }
        .subtitle { font-size: 12px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; font-weight: bold; color: #4b5563; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; color: #1a365d; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Facility Status Report: {{ $facility->name }}</h1>
        <div class="subtitle">Generated on {{ now()->format('F j, Y, g:i a') }}</div>
    </div>

    <div class="section-title">Facility Statistics</div>
    <table>
        <tr>
            <th>Total Active Detainees</th>
            <td>{{ $stats['total_active'] ?? 0 }}</td>
        </tr>
        <tr>
            <th>Critical Alerts</th>
            <td style="color: #dc2626; font-weight: bold;">{{ $stats['critical'] ?? 0 }}</td>
        </tr>
        <tr>
            <th>At Risk Alerts</th>
            <td style="color: #d97706; font-weight: bold;">{{ $stats['at_risk'] ?? 0 }}</td>
        </tr>
        <tr>
            <th>Total Active Detainees</th>
            <td>{{ $stats['total_active'] ?? 0 }}</td>
        </tr>
        <tr>
            <th>Capacity Limit</th>
            <td>{{ $facility->capacity }}</td>
        </tr>
        <tr>
            <th>Occupancy Rate</th>
            <td>{{ $stats['occupancy_pct'] }}%</td>
        </tr>
        <tr>
            <th>Over Capacity</th>
            <td>{{ $stats['over_capacity'] ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
            <th>Address</th>
            <td>{{ $facility->address }}, {{ $facility->region }}</td>
        </tr>
    </table>

</body>
</html>
