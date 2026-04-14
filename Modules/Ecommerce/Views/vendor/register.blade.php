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
    <title>Become a Vendor</title>
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
                    --bs-btn-bg: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-link-color: {!! $adminSettings['admin_primary_color'] !!} !important;
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
            <div class="col-12 col-md-6 col-lg-5 col-xl-4 bg-white p-4 rounded-3">
                <div class="d-flex flex-column justify-content-center">
                    <div class="auth-logo mx-auto pb-3">
                        <a href="{{ url('/') }}" class="logo-dark">
                            <img src="{{ asset('admin/assets/images/logo-sm.png') }}" height="50" alt="logo dark">
                        </a>
                        <a href="{{ url('/') }}" class="logo-light">
                            <img src="{{ asset('admin/assets/images/logo-sm.png') }}" height="50" alt="logo light">
                        </a>
                    </div>

                    <h2 class="fw-bold fs-24">Become a Vendor</h2>
                    <p class="text-muted mt-1 mb-2">Create your vendor account to start selling.</p>

                    <div>
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('info'))
                            <div class="alert alert-info">{{ session('info') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form method="POST" action="{{ route('vendor.register.store') }}" class="authentication-form" autocomplete="off">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="name">Your Name *</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Enter your name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="store_name">Store Name *</label>
                                <input type="text" id="store_name" name="store_name" class="form-control @error('store_name') is-invalid @enderror"
                                    placeholder="Enter store name" value="{{ old('store_name') }}" required>
                                @error('store_name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    placeholder="Enter your email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="password">Password *</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter password" required>
                                @error('password')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="text" name="phone" class="form-control"
                                    placeholder="Enter phone number" value="{{ old('phone') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="description">Store Description</label>
                                <textarea name="description" class="form-control" rows="3"
                                    placeholder="Describe your store">{{ old('description') }}</textarea>
                            </div>

                            <div class="mb-1 text-center d-grid">
                                <button class="btn btn-primary w-100" type="submit">Apply as Vendor</button>
                            </div>

                            <div class="mt-3 text-center">
                                <a href="{{ route('vendor.login') }}" class="text-muted">Already a vendor? Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.partials.vendor-scripts')
</body>

</html>