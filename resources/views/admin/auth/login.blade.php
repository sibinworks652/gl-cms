<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login</title>
    @include('admin.layouts.partials.head-css')
</head>
<body class="h-100">
    <div class="d-flex flex-column h-100 p-3">
        <div class="d-flex flex-column flex-grow-1">
            <div class="row h-100">
                <div class="col-xxl-7">
                    <div class="row justify-content-center h-100">
                        <div class="col-lg-6 py-lg-5">
                            <div class="d-flex flex-column h-100 justify-content-center">
                                <div class="auth-logo mb-4">
                                    <a href="{{ url('/') }}" class="logo-dark">
                                        <img src="{{ asset('admin/assets/images/logo-dark.png') }}" height="24" alt="logo dark">
                                    </a>

                                    <a href="{{ url('/') }}" class="logo-light">
                                        <img src="{{ asset('admin/assets/images/logo-light.png') }}" height="24" alt="logo light">
                                    </a>
                                </div>

                                <h2 class="fw-bold fs-24">Sign In</h2>
                                <p class="text-muted mt-1 mb-4">Enter your email address and password to access admin panel.</p>

                                <div class="mb-5">
                                    <div id="login-alert" class="alert d-none" role="alert"></div>

                                    <form method="POST" action="{{ route('login.submit') }}" class="authentication-form" id="admin-login-form">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email">
                                            <span class="text-danger small" data-error-for="email"></span>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password">Password</label>
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password">
                                            <span class="text-danger small" data-error-for="password"></span>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                                                <label class="form-check-label" for="remember">Remember me</label>
                                            </div>
                                        </div>

                                        <div class="mb-1 text-center d-grid">
                                            <button class="btn btn-soft-primary w-100" type="submit" id="login-submit-btn">Sign In</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-5 d-none d-xxl-flex">
                    <div class="card h-100 mb-0 overflow-hidden">
                        <div class="d-flex flex-column h-100">
                            <img src="{{ asset('admin/assets/images/small/img-10.jpg') }}" alt="" class="w-100 h-100">
                        </div>
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

                form.querySelectorAll('.is-invalid').forEach((element) => {
                    element.classList.remove('is-invalid');
                });

                alertBox.className = 'alert d-none';
                alertBox.textContent = '';
            };

            const setFieldError = (field, message) => {
                const input = form.querySelector(`[name="${field}"]`);
                const error = form.querySelector(`[data-error-for="${field}"]`);

                if (input) {
                    input.classList.add('is-invalid');
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
