@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        @include('email::admin.partials.tabs')

        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.email.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Email System</h4>
                    <p class="text-muted mb-0">Control the global branding and layout applied to every CMS email.</p>
                </div>
                <button type="submit" class="btn btn-primary">Save Email Settings</button>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Global Layout</h5>
                            <p class="text-muted mb-0">Header, footer, and signature support HTML and dynamic placeholders.</p>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Email Header</label>
                                <textarea name="email_header" rows="5" class="form-control @error('email_header') is-invalid @enderror">{{ old('email_header', $settings['email_header'] ?? '') }}</textarea>
                                @error('email_header')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Footer</label>
                                <textarea name="email_footer" rows="5" class="form-control @error('email_footer') is-invalid @enderror">{{ old('email_footer', $settings['email_footer'] ?? '') }}</textarea>
                                @error('email_footer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label">Email Signature</label>
                                <textarea name="email_signature" rows="4" class="form-control @error('email_signature') is-invalid @enderror">{{ old('email_signature', $settings['email_signature'] ?? '') }}</textarea>
                                @error('email_signature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">Branding</h5>
                            <p class="text-muted mb-0">Logo and theme options for the master email layout.</p>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Email Logo</label>
                                <input type="file" name="email_logo" class="form-control @error('email_logo') is-invalid @enderror" accept="image/*">
                                @error('email_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if(!empty($settings['email_logo']))
                                    <div class="mt-3">
                                        <img src="{{ asset('storage/' . $settings['email_logo']) }}" alt="Email Logo" class="auto-contrast-logo auto-contrast-logo-preview" style="max-height:72px; max-width:100%;">
                                    </div>
                                @endif
                            </div>
                            <div class="email-color-theme d-flex gap-2 justify-content-start align-items-center">
                            <div class="mb-3 text-center">
                                    <label class="form-label">Theme Color</label>
                                    <div class="d-flex justify-content-center">
                                <input type="color" name="email_theme_color" class="form-control text-center form-control-color @error('email_theme_color') is-invalid @enderror" value="{{ old('email_theme_color', $settings['email_theme_color'] ?? 'var(--bs-primary)') }}">
                                </div>
                                @error('email_theme_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3 text-center">
                                <label class="form-label">Text Color</label>
                                <div class="d-flex justify-content-center">
                                <input type="color" name="email_text_color" class="form-control text-center form-control-color @error('email_text_color') is-invalid @enderror" value="{{ old('email_text_color', $settings['email_text_color'] ?? '#111827') }}">
                                </div>
                                @error('email_text_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.email-variable-chip').forEach(function (button) {
        button.addEventListener('click', function () {
            navigator.clipboard?.writeText(button.dataset.variable);
            if (window.showAdminToast) {
                window.showAdminToast(button.dataset.variable + ' copied.', 'success');
            }
        });
    });
});
</script>
@endpush
