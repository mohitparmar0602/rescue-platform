<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Rescue Platform Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: sans-serif; font-size: 11px; color: #1f2937; background: #fff; padding: 24px; }

    .header { border-bottom: 3px solid #4f46e5; padding-bottom: 14px; margin-bottom: 20px; }
    .header h1 { font-size: 22px; font-weight: 800; color: #4f46e5; }
    .header .meta { font-size: 10px; color: #6b7280; margin-top: 4px; }

    .kpi-row { display: flex; gap: 12px; margin-bottom: 20px; }
    .kpi { flex: 1; border-radius: 8px; padding: 12px 16px; }
    .kpi.indigo { background: #eef2ff; border-left: 4px solid #6366f1; }
    .kpi.green  { background: #f0fdf4; border-left: 4px solid #22c55e; }
    .kpi.yellow { background: #fefce8; border-left: 4px solid #eab308; }
    .kpi.red    { background: #fef2f2; border-left: 4px solid #ef4444; }
    .kpi-val  { font-size: 28px; font-weight: 900; color: #111827; }
    .kpi-lbl  { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #6b7280; margin-top: 2px; }

    section { margin-bottom: 22px; }
    section h2 { font-size: 12px; font-weight: 700; color: #374151; border-bottom: 1px solid #e5e7eb;
                 padding-bottom: 5px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.06em; }

    table { width: 100%; border-collapse: collapse; }
    th { background: #f9fafb; text-align: left; padding: 6px 10px; font-size: 9px;
         text-transform: uppercase; letter-spacing: 0.06em; color: #6b7280; font-weight: 700;
         border-bottom: 1px solid #e5e7eb; }
    td { padding: 6px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 999px; font-size: 9px;
             font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .badge-critical { background: #fee2e2; color: #b91c1c; }
    .badge-high     { background: #ffedd5; color: #c2410c; }
    .badge-medium   { background: #fef9c3; color: #854d0e; }
    .badge-low      { background: #dbeafe; color: #1d4ed8; }

    .bar-wrap { background: #e5e7eb; border-radius: 4px; height: 6px; width: 80px; display: inline-block; vertical-align: middle; }
    .bar-fill { height: 6px; border-radius: 4px; display: block; }

    .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px;
              font-size: 9px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>

<div class="header">
    <h1>🚨 Rescue Platform — Administrative Report</h1>
    <p class="meta">Generated: {{ $generatedAt }}</p>
</div>

{{-- KPIs --}}
<div class="kpi-row">
    <div class="kpi indigo">
        <div class="kpi-val">{{ $totalAgencies }}</div>
        <div class="kpi-lbl">Total Agencies</div>
    </div>
    <div class="kpi green">
        <div class="kpi-val">{{ $approvedCount }}</div>
        <div class="kpi-lbl">Approved</div>
    </div>
    <div class="kpi yellow">
        <div class="kpi-val">{{ $pendingCount }}</div>
        <div class="kpi-lbl">Pending</div>
    </div>
    <div class="kpi red">
        <div class="kpi-val">{{ $alertsBySeverity->sum('active') }}</div>
        <div class="kpi-lbl">Active Alerts</div>
    </div>
</div>

{{-- Agency Breakdown --}}
<section>
    <h2>Agencies by Type</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agenciesByType as $type => $count)
            <tr>
                <td style="text-transform: capitalize;">{{ $type }}</td>
                <td><strong>{{ $count }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>

{{-- Alert Severity --}}
<section>
    <h2>Alert History by Severity</h2>
    <table>
        <thead>
            <tr>
                <th>Severity</th>
                <th>Total Issued</th>
                <th>Active</th>
                <th>Resolved</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alertsBySeverity as $row)
            <tr>
                <td><span class="badge badge-{{ $row->severity }}">{{ $row->severity }}</span></td>
                <td>{{ $row->total }}</td>
                <td style="color:#dc2626;font-weight:700">{{ $row->active }}</td>
                <td style="color:#16a34a;font-weight:700">{{ $row->total - $row->active }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="color:#9ca3af;text-align:center">No alerts issued.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{-- Recent Alerts --}}
<section>
    <h2>Recent Alerts (Last 10)</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Severity</th>
                <th>Issued By</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAlerts as $alert)
            <tr>
                <td>{{ $alert->title }}</td>
                <td><span class="badge badge-{{ $alert->severity }}">{{ $alert->severity }}</span></td>
                <td>{{ $alert->issuer->name ?? 'System' }}</td>
                <td>{{ $alert->created_at->format('d M Y H:i') }}</td>
                <td style="color: {{ $alert->is_active ? '#dc2626' : '#16a34a' }}; font-weight:700">
                    {{ $alert->is_active ? 'Active' : 'Resolved' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="color:#9ca3af;text-align:center">No alerts.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{-- Resource Utilization --}}
<section>
    <h2>Approved Agencies — Resource Utilization</h2>
    <table>
        <thead>
            <tr>
                <th>Agency</th>
                <th>Type</th>
                <th>Total</th>
                <th>Available</th>
                <th>Deployed</th>
                <th>Maintenance</th>
                <th>Utilization</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agencies as $a)
            @php
                $barColor = $a['utilization'] >= 75 ? '#ef4444' : ($a['utilization'] >= 40 ? '#f59e0b' : '#22c55e');
            @endphp
            <tr>
                <td style="font-weight:600">{{ $a['name'] }}</td>
                <td style="text-transform:capitalize;color:#6b7280">{{ $a['type'] }}</td>
                <td>{{ $a['resources'] }}</td>
                <td style="color:#16a34a;font-weight:700">{{ $a['available'] }}</td>
                <td style="color:#d97706;font-weight:700">{{ $a['deployed'] }}</td>
                <td style="color:#dc2626;font-weight:700">{{ $a['maintenance'] }}</td>
                <td>
                    <span class="bar-wrap">
                        <span class="bar-fill" style="width:{{ $a['utilization'] }}%;background:{{ $barColor }}"></span>
                    </span>
                    <span style="margin-left:6px;font-weight:700">{{ $a['utilization'] }}%</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="color:#9ca3af;text-align:center">No approved agencies.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<div class="footer">
    This report is confidential and intended for authorised platform administrators only. &copy; {{ now()->year }} Rescue Platform.
</div>

</body>
</html>
