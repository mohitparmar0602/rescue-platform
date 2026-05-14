<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Alert — {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.1); }
        .header-critical { background: #dc2626; }
        .header-high     { background: #ea580c; }
        .header-medium   { background: #ca8a04; }
        .header-low      { background: #2563eb; }
        .header { padding: 28px 32px; color: #fff; }
        .header h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; }
        .header p  { margin: 0; opacity: .85; font-size: 14px; }
        .badge { display: inline-block; background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.4);
                 border-radius: 999px; padding: 3px 12px; font-size: 11px; font-weight: 700;
                 letter-spacing: .06em; text-transform: uppercase; margin-bottom: 10px; }
        .body  { padding: 28px 32px; }
        .body h2 { margin: 0 0 12px; font-size: 20px; color: #111; }
        .body p  { color: #444; line-height: 1.6; margin: 0 0 16px; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 18px; margin-bottom: 20px; }
        .info-box dt { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
        .info-box dd { font-size: 14px; color: #111; margin: 2px 0 10px; }
        .cta { display: inline-block; background: #dc2626; color: #fff; text-decoration: none;
               font-weight: 700; padding: 12px 24px; border-radius: 6px; font-size: 14px; margin-top: 4px; }
        .footer { border-top: 1px solid #e5e7eb; padding: 18px 32px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header header-{{ $alert->severity }}">
        <div class="badge">{{ strtoupper($alert->severity) }} severity</div>
        <h1>🚨 Emergency Alert</h1>
        <p>{{ config('app.name') }} · {{ now()->format('d M Y, H:i') }}</p>
    </div>

    <div class="body">
        <p>Dear {{ $recipient->name }},</p>
        <p>A new emergency alert has been issued that affects your agency. Please review the details below and take immediate action.</p>

        <h2>{{ $alert->title }}</h2>
        <p>{{ $alert->description }}</p>

        <div class="info-box">
            <dl>
                <dt>Severity</dt>
                <dd>{{ ucfirst($alert->severity) }}</dd>

                @if($alert->lat && $alert->lng)
                <dt>Incident Coordinates</dt>
                <dd>{{ number_format($alert->lat, 6) }}, {{ number_format($alert->lng, 6) }}</dd>
                @endif

                <dt>Issued By</dt>
                <dd>{{ $alert->issuer->name ?? 'System Administrator' }}</dd>

                <dt>Issued At</dt>
                <dd>{{ $alert->created_at->format('d M Y, H:i T') }}</dd>
            </dl>
        </div>

        <a href="{{ route('alerts.index') }}" class="cta">View Alert Dashboard →</a>
    </div>

    <div class="footer">
        You are receiving this because your agency is registered on the {{ config('app.name') }} platform.
        Do not reply to this email — use the platform dashboard for all communications.
    </div>
</div>
</body>
</html>
