@php
    $adminSettings = \Modules\Settings\Models\Setting::pairs();
    $adminPreferredTheme = (string) ($adminSettings['admin_dark_mode_enabled'] ?? '0') === '1' ? 'dark' : 'light';
    $hexToRgb = function (?string $hex): ?string {
        if (!is_string($hex) || !preg_match('/^#([A-Fa-f0-9]{6})$/', $hex)) {
            return null;
        }
        return implode(', ', [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))]);
    };
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vendor Application Status</title>
    @if (!empty($adminSettings['site_favicon']))
        <link rel="shortcut icon" href="{{ asset('storage/' . $adminSettings['site_favicon']) }}">
        <link rel="icon" href="{{ asset('storage/' . $adminSettings['site_favicon']) }}">
    @endif
    <script>
        window.adminPreferredTheme = @json($adminPreferredTheme);
    </script>
    @include('admin.layouts.partials.head-css')
    @if (filled($adminSettings['admin_primary_color'] ?? null) ||
            filled($adminSettings['admin_page_bg'] ?? null))
        <style>
            @if (filled($adminSettings['admin_primary_color'] ?? null))
                :root {
                    --bs-primary: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-primary-rgb: {!! $hexToRgb($adminSettings['admin_primary_color']) !!} !important;
                }
                .btn-primary {
                    --bs-btn-color: #ffffff !important;
                    --bs-btn-bg: var(--bs-primary) !important;
                    --bs-btn-border-color: var(--bs-primary) !important;
                }
            @endif
            html[data-bs-theme="light"] {
                @if (filled($adminSettings['admin_page_bg'] ?? null))
                    --bs-body-bg: {!! $adminSettings['admin_page_bg'] !!} !important;
                @endif
            }
        </style>
    @endif
</head>

<body class="min-vh-100">
    <div class="container-fluid min-vh-100">
        <div class="row min-vh-100 justify-content-center align-items-center">
            <div class="col-12 col-md-5 col-lg-4 bg-white p-4 rounded-3">
                <div class="text-center">
                    <div class="auth-logo mx-auto pb-3">
                        <a href="{{ url('/') }}" class="logo-dark">
                            <img src="{{ asset('admin/assets/images/logo-sm.png') }}" height="50" alt="logo dark">
                        </a>
                        <a href="{{ url('/') }}" class="logo-light">
                            <img src="{{ asset('admin/assets/images/logo-sm.png') }}" height="50" alt="logo light">
                        </a>
                    </div>

                    <h4 class="fw-bold">{{ $vendor->name }}</h4>

                    @if($vendor->isPending())
                        <div class="alert alert-warning mt-3">
                            <iconify-icon icon="mdi:clock-outline" width="48" height="48" class="d-block mx-auto mb-2"></iconify-icon>
                            <h5>Application Pending</h5>
                            <p class="mb-0">Your vendor application is currently pending approval from the administrator.</p>
                        </div>
                    @elseif($vendor->isRejected())
                        <div class="alert alert-danger mt-3">
                            <iconify-icon icon="mdi:close-circle-outline" width="48" height="48" class="d-block mx-auto mb-2"></iconify-icon>
                            <h5>Application Rejected</h5>
                            <p class="mb-0">Your vendor application has been rejected.</p>
                            @if($vendor->rejection_reason)
                                <p class="mb-0 mt-2"><strong>Reason:</strong> {{ $vendor->rejection_reason }}</p>
                            @endif
                        </div>
                        <a href="{{ route('vendor.register') }}" class="btn btn-primary">Reapply</a>
                    @endif

                    @auth('web')
                        <form method="POST" action="{{ route('vendor.logout') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Logout</button>
                        </form>
                    @endauth

                    <div class="mt-3">
                        <a href="{{ route('vendor.login') }}" class="text-muted">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.partials.vendor-scripts')
</body>

</html>