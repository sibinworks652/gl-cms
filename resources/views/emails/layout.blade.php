<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0; padding:24px; background:#f8fafc; color:#0f172a; font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:720px; border-collapse:collapse; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:24px 28px; background:#0f172a; color:#ffffff;">
                            <div style="font-size:20px; font-weight:700;">{{ config('app.name') }}</div>
                            @isset($subject)
                                <div style="margin-top:6px; font-size:14px; color:#cbd5e1;">{{ $subject }}</div>
                            @endisset
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            {{ $slot }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
