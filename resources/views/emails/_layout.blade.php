<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, table, td, p, a, h1, h2 { font-family: 'Fredoka', 'Helvetica Neue', Arial, sans-serif; }
    </style>
</head>
<body style="margin:0;padding:0;background:#faf5ff;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;color:#1a1a1a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf5ff;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 8px 32px rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.08);">
                    {{-- Hero header with prism gradient + pokeball + sparkles --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#7c3aed 0%,#ec4899 50%,#f59e0b 100%);padding:32px 32px 36px;position:relative;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;padding-right:16px;width:64px;">
                                        {{-- Inline pokeball SVG (renders in modern clients; degrades to alt text) --}}
                                        <svg width="56" height="56" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" style="display:block;filter:drop-shadow(0 4px 8px rgba(0,0,0,0.25));">
                                            <circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1a1a1a" stroke-width="3"/>
                                            <path d="M2 32 A30 30 0 0 1 62 32 Z" fill="#ee1515"/>
                                            <rect x="2" y="29" width="60" height="6" fill="#1a1a1a"/>
                                            <circle cx="32" cy="32" r="9" fill="#ffffff" stroke="#1a1a1a" stroke-width="3"/>
                                            <circle cx="32" cy="32" r="4" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
                                        </svg>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <p style="margin:0;font-size:12px;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,0.92);font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                                            ✦ {{ $eyebrow ?? 'PokeTrade TCG' }} ✦
                                        </p>
                                        <h1 style="margin:6px 0 0;font-size:28px;font-weight:700;color:#ffffff;line-height:1.2;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;text-shadow:0 2px 8px rgba(0,0,0,0.15);">{{ $heading ?? '' }}</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body slot --}}
                    <tr>
                        <td style="padding:32px;background:#ffffff;">
                            {!! $slot !!}
                        </td>
                    </tr>

                    {{-- Footer with mini pokeball + tagline --}}
                    <tr>
                        <td style="padding:24px 32px;background:linear-gradient(180deg,#faf5ff 0%,#fdf2f8 100%);border-top:1px solid rgba(124,58,237,0.1);text-align:center;">
                            <svg width="20" height="20" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" style="display:inline-block;vertical-align:middle;margin-right:6px;">
                                <circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1a1a1a" stroke-width="4"/>
                                <path d="M2 32 A30 30 0 0 1 62 32 Z" fill="#ee1515"/>
                                <rect x="2" y="29" width="60" height="6" fill="#1a1a1a"/>
                                <circle cx="32" cy="32" r="9" fill="#ffffff" stroke="#1a1a1a" stroke-width="3"/>
                            </svg>
                            <span style="font-size:13px;font-weight:600;color:#7c3aed;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;letter-spacing:0.5px;">Gotta track 'em all.</span>
                            <p style="margin:12px 0 0;font-size:11px;color:#999999;line-height:1.6;font-family:'Fredoka','Helvetica Neue',Arial,sans-serif;">
                                You're getting this because you have a {{ config('app.name') }} account.<br>
                                Manage your notification preferences from your trainer settings.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
