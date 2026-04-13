<!DOCTYPE html>
<html lang="en">
<head>
     @php
        $adminSettings = \Modules\Settings\Models\Setting::pairs();
        // dd($adminSettings);
        $adminPreferredTheme = (string) ($adminSettings['admin_dark_mode_enabled'] ?? '0') === '1' ? 'dark' : 'light';
        $hexToRgb = function (?string $hex): ?string {
            if (! is_string($hex) || ! preg_match('/^#([A-Fa-f0-9]{6})$/', $hex)) {
                return null;
            }

            return implode(', ', [
                hexdec(substr($hex, 1, 2)),
                hexdec(substr($hex, 3, 2)),
                hexdec(substr($hex, 5, 2)),
            ]);
        };
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
     @if(!empty($adminSettings['site_favicon']))
        <link rel="shortcut icon" href="{{ asset('storage/' . $adminSettings['site_favicon']) }}">
        <link rel="icon" href="{{ asset('storage/' . $adminSettings['site_favicon']) }}">
    @endif



    @if(\App\Support\ModuleRegistry::enabled('seo') && \Illuminate\Support\Facades\View::exists('seo::meta'))
        @include('seo::meta')
    @endif
      @php($adminLogo = \Modules\Settings\Models\Setting::value('admin_logo'))
      @php($adminLoginLogo = \Modules\Settings\Models\Setting::value('admin_login_logo', $adminLogo))
    {{-- @dd($adminLogo) --}}
    <script>
        window.adminPreferredTheme = @json($adminPreferredTheme);
    </script>
    @include('admin.layouts.partials.head-css')
    @if(
        filled($adminSettings['admin_primary_color'] ?? null) ||
        filled($adminSettings['admin_topbar_bg'] ?? null) ||
        filled($adminSettings['admin_topbar_text_color'] ?? null) ||
        filled($adminSettings['admin_sidebar_bg'] ?? null) ||
        filled($adminSettings['admin_sidebar_text_color'] ?? null) ||
        filled($adminSettings['admin_sidebar_hover_color'] ?? null) ||
        filled($adminSettings['admin_page_bg'] ?? null)
    )
    <style>
        @if(filled($adminSettings['admin_primary_color'] ?? null))
                :root {
                        --bs-primary: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-primary-rgb: {!! $hexToRgb($adminSettings['admin_primary_color']) !!} !important;
                        --bs-btn-bg: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-btn-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-link-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-link-hover-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-btn-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-btn-hover-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-btn-active-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                        --bs-btn-active-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                }
                .btn-outline-primary{
                    --bs-btn-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-hover-color: #ffffff;
                    --bs-btn-hover-bg: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-hover-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-focus-shadow-rgb: 255, 108, 47;
                    --bs-btn-active-color: #ffffff;
                    --bs-btn-active-bg: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-active-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
                    --bs-btn-disabled-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-btn-disabled-bg: transparent;
                    --bs-btn-disabled-border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    --bs-gradient: none;
                   }
                   .btn-soft-primary {
                    --bs-btn-color: #ffffff !important;
                   --bs-btn-bg: rgba(var(--bs-primary-rgb), 0.1) !important;
                    --bs-btn-border-color: transparent;
                    --bs-btn-hover-color: #ffffff;
                    --bs-btn-hover-bg: var(--bs-primary);
                    --bs-btn-hover-border-color: var(--bs-primary);
                    --bs-btn-active-color: #ffffff;
                    --bs-btn-active-bg: var(--bs-primary);
                    --bs-btn-active-border-color: var(--bs-primary);
                    --bs-btn-disabled-color: #ffffff;
                    --bs-btn-disabled-bg: var(--bs-primary);
                    --bs-btn-disabled-border-color: var(--bs-primary);
                    --bs-btn-focus-shadow-rgb: 0 0 0 $btn-focus-width rgba($bg, 0.5);
                    }
                   .btn-primary {
                    --bs-btn-color: var(--bs-primary) !important;
                     --bs-btn-bg: rgba(var(--bs-primary-rgb), 0.1) !important;
                    --bs-btn-border-color: transparent;
                    --bs-btn-hover-color: #ffffff;
                    --bs-btn-hover-bg: var(--bs-primary);
                    --bs-btn-hover-border-color: var(--bs-primary);
                    --bs-btn-active-color: #ffffff;
                    --bs-btn-active-bg: var(--bs-primary);
                    --bs-btn-active-border-color: var(--bs-primary);
                    --bs-btn-disabled-color: #ffffff;
                    --bs-btn-disabled-bg: var(--bs-primary);
                    --bs-btn-disabled-border-color: var(--bs-primary);
                    --bs-btn-focus-shadow-rgb: 0 0 0 $btn-focus-width rgba($bg, 0.5);
                    }
            @endif

            html[data-bs-theme="light"] {
                @if(filled($adminSettings['admin_topbar_bg'] ?? null))
                    --bs-topbar-bg: {!! $adminSettings['admin_topbar_bg'] !!} !important;
                    --bs-topbar-search-bg: {!! $adminSettings['admin_topbar_bg'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_topbar_text_color'] ?? null))
                    --bs-topbar-item-color: {!! $adminSettings['admin_topbar_text_color'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_sidebar_bg'] ?? null))
                    --bs-main-nav-bg: {!! $adminSettings['admin_sidebar_bg'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_sidebar_text_color'] ?? null))
                    --bs-main-nav-item-color: {!! $adminSettings['admin_sidebar_text_color'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_sidebar_hover_color'] ?? null))
                    --bs-main-nav-item-hover-color: {!! $adminSettings['admin_sidebar_hover_color'] !!} !important;
                @endif
                @if(filled($adminSettings['admin_page_bg'] ?? null))
                    --bs-body-bg: {!! $adminSettings['admin_page_bg'] !!} !important;
                @endif
            }

            @if(filled($adminSettings['admin_primary_color'] ?? null))
                .btn-primary,
                .bg-primary {
                    background-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    color: #ffffff !important;
                }

                .text-primary,
                a {
                    color: {!! $adminSettings['admin_primary_color'] !!};
                }
                .form-check-input:checked{
                    background-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                }
                .btn-outline-primary{
                    color: {!! $adminSettings['admin_primary_color'] !!} !important;
                    border-color: {!! $adminSettings['admin_primary_color'] !!} !important;
                }
                .btn:hover {
                    color: var(--bs-main-nav-item-hover-color) !important;
                }
                .btn-outline-input{
                    border:var(--bs-border-width) solid var(--bs-input-border-color);
                }
                .btn-outline-input:hover {
                    color:black !important;
                    border:var(--bs-border-width) solid var(--bs-input-border-color);
                }
            @endif
            .error-input-bottom{
                border-bottom: 2px solid red;
            }
    </style>
    @endif
</head>
<body class="min-vh-100">
    <div class="container-fluid min-vh-100">
        <div class="row min-vh-100 justify-content-center align-items-center">
            <div class="col-12 col-md-4 col-lg-3 col-xl-3 bg-white p-4 rounded-3">
                <div class="d-flex flex-column justify-content-center">
                                <div class="auth-logo mx-auto pb-3 ">
                                    <a href="{{ url('/') }}" class="logo-dark">
                                        <img src="{{ $adminLoginLogo ? asset('storage/' . $adminLoginLogo) : asset('admin/assets/images/logo-sm.png') }}" height="50" alt="logo dark">
                                    </a>

                                    <a href="{{ url('/') }}" class="logo-light">
                                        <img src="{{ $adminLoginLogo ? asset('storage/' . $adminLoginLogo) : asset('admin/assets/images/logo-sm.png') }}" height="50" alt="logo light">
                                    </a>
                                </div>

                                <h2 class="fw-bold fs-24 d-flex justify-content-center align-items-center gap-2">Hi, {{ Auth::user()->name }} Screen Locked <iconify-icon icon="solar:lock-keyhole-minimalistic-bold-duotone" width="20" height="20" /></h2>
                                <p class="text-muted mt-1 mb-2">Enter your password to access admin panel.</p>

                                <div>
                                    <div id="login-alert" class="alert d-none" role="alert"></div>

                                    <form method="POST" action="{{ route('admin.unlock') }}" class="authentication-form" >
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label" for="password">Password</label>
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password">
                                            <span class="text-danger small" data-error-for="password"></span>
                                            @if(session('error'))
                                            <p style="color:red">{{ session('error') }}</p>
                                            @endif
                                        </div>
                                        <div class="mb-1 text-center d-grid">
                                            <button class="btn btn-primary w-100" type="submit" id="login-submit-btn">Unlock</button>
                                        </div>
                                    </form>
                                </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.partials.vendor-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('admin-login-form');
            const submitButton = document.getElementById('login-submit-btn');
            const alertBox = document.getElementById('login-alert');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const clearErrors = () => {
                form.querySelectorAll('[data-error-for]').forEach((element) => {
                    element.textContent = '';
                });

                form.querySelectorAll('.error-input-bottom').forEach((element) => {
                    element.classList.remove('error-input-bottom');
                });

                alertBox.className = 'alert d-none';
                alertBox.textContent = '';
            };

            const setFieldError = (field, message) => {
                const input = form.querySelector(`[name="${field}"]`);
                const error = form.querySelector(`[data-error-for="${field}"]`);

                if (input) {
                    input.classList.add('error-input-bottom');
                }

                if (error) {
                    error.textContent = message;
                }
            };

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                clearErrors();

                submitButton.disabled = true;
                submitButton.textContent = 'Signing In...';

                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            Object.entries(data.errors).forEach(([field, messages]) => {
                                setFieldError(field, messages[0]);
                            });
                        } else {
                            alertBox.className = 'alert alert-danger';
                            alertBox.textContent = data.message || 'Login failed. Please try again.';
                        }

                        return;
                    }

                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = data.message || 'Login successful.';
                    window.location.href = data.redirect || "{{ route('admin.dashboard') }}";
                } catch (error) {
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = 'Unable to process login right now. Please try again.';
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Sign In';
                }
            });
        });
    </script>
</body>
</html>
