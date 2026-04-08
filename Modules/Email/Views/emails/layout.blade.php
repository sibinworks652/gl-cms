@php
    $pageBg = '#ffffff';
    $cardBg = '#ffffff';
    $textColor = '#111827';
    $mutedColor = '#454545';
    $borderColor = '#c7c7c7';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? $siteName ?? config('app.name') }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
*{
    font-family: 'Poppins', sans-serif;
}
h1{
    font-size: 22px;
    margin: 0px;
}
h2{
    font-size: 18px;
    margin: 0px;
}
h3{
    font-size: 16px;
    margin: 0px;
}
h4{
    font-size: 14px;
    margin: 0px;
}
h5{
    font-size: 13px;
    margin: 0px;
}
h6{
    font-size: 12px;
    margin: 0px;
}
p{
    font-size: 14px;
    margin: 0;
}
a{
    font-size: 12px;
}
</style>
</head>
<body style="margin:0; padding:0; background:{{ $pageBg }}; color:{{ $textColor }}; font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background:{{ $pageBg }};">
        <tr>
            <td align="center" style="padding:28px 14px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:720px; border-collapse:collapse;">
                    @if(!empty($logoUrl) || !empty($header))
                    <tr>
                        <td style="padding:18px; text-align:center; background:{{ $themeColor ?? 'var(--bs-primary)' }}; color:{{ $textColor }} !important; display:flex; align-items:center; justify-content:center; gap:10px;">

                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="{{ $siteName ?? config('app.name') }}" style="max-height:72px; max-width:220px; display:inline-block;">
                            @elseif(!empty($header))
                                <div style="display:inline-block; color:{{ $textColor }}; font-size:22px; font-weight:700;">{{ $siteName ?? config('app.name') }}</div>
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="background:{{ $cardBg }}; border:1px solid {{ $borderColor }};overflow:hidden;">
                            @if(!empty($header))
                             <div style="padding:10px; color:{{ $textColor }}; line-height:1.65;">
                                {!! $header !!}
                            </div>
                            @endif

                            <div style="padding:10px; color:{{ $textColor }}; line-height:1.65;">
                                {!! $body ?? '' !!}
                            </div>

                            @if(!empty($signature))
                                <div style="padding:20px 10px 10px 10px; color:{{ $mutedColor }};">
                                    {!! $signature !!}
                                </div>
                            @endif
                            @if(!empty($footer))
                                <div style="border-top:1px solid {{ $borderColor }}; padding:18px 28px; color:{{ $mutedColor }}; font-size:13px; text-align:center;">
                                    {!! $footer !!}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
