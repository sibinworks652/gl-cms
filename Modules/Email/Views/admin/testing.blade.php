@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        @include('email::admin.partials.tabs')

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Test Email & SMTP</h4>
                <p class="text-muted mb-0">Preview rendered templates, send test emails, and verify SMTP connectivity.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <form method="POST" action="{{ route('admin.email.testing.send') }}" class="card h-100">
                    @csrf
                    <div class="card-header">
                        <h5 class="card-title mb-1">Send Test Email</h5>
                        <p class="text-muted mb-0">Uses the full layout, active branding, and template variables.</p>
                    </div>
                    <div class="card-body">
                        @if(session('email_test_status'))
                            @php($status = session('email_test_status'))
                            <div class="alert alert-{{ $status['type'] }}">{{ $status['message'] }}</div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Template</label>
                            <select name="template_id" id="email-test-template" class="form-select @error('template_id') error-input-bottom @enderror">
                                <option value="">Select template</option>
                                @foreach($templates as $template)
                                    <option
                                        value="{{ $template->id }}"
                                        data-preview-url="{{ route('admin.email.testing.preview', $template) }}"
                                        data-variables='@json($template->variables ?? [])'
                                        @selected((string) old('template_id') === (string) $template->id)
                                    >{{ $template->name }}</option>
                                @endforeach
                            </select>
                            @error('template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To Email Addresses</label>
                            <textarea name="email" rows="3" class="form-control @error('email') error-input-bottom @enderror" placeholder="admin@example.com, team@example.com">{{ old('email', $settings['mail_from_address'] ?? $settings['site_email'] ?? '') }}</textarea>
                            <div class="form-text">Optional if the selected template already has default To recipients.</div>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CC Email Addresses</label>
                            <textarea name="cc_email" rows="2" class="form-control @error('cc_email') error-input-bottom @enderror" placeholder="owner@example.com, audit@example.com">{{ old('cc_email') }}</textarea>
                            <div class="form-text">Optional extra CC recipients for this test send.</div>
                            @error('cc_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variable Values</label>
                            <div id="email-test-payload" class="row g-2"></div>
                            <div class="form-text">Leave blank to use generated dummy preview data.</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">Send Test Email</button>
                            <a href="#" target="_blank" id="email-test-preview-link" class="btn btn-outline-primary disabled">Preview Email</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-xl-6">
                <form method="POST" action="{{ route('admin.email.testing.smtp') }}" class="card h-100">
                    @csrf
                    <div class="card-header">
                        <h5 class="card-title mb-1">SMTP Tester</h5>
                        <p class="text-muted mb-0">Checks the SMTP credentials saved in Mail Settings.</p>
                    </div>
                    <div class="card-body">
                        @if(session('smtp_test_status'))
                            @php($status = session('smtp_test_status'))
                            <div class="alert alert-{{ $status['type'] }}">{{ $status['message'] }}</div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Host</label>
                                <input type="text" class="form-control" value="{{ $settings['mail_host'] ?? config('mail.mailers.smtp.host') }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port</label>
                                <input type="text" class="form-control" value="{{ $settings['mail_port'] ?? config('mail.mailers.smtp.port') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="{{ $settings['mail_username'] ?? '' }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="text" class="form-control" value="{{ !empty($settings['mail_password']) ? 'Saved in Mail Settings' : 'Not configured' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Encryption</label>
                                <input type="text" class="form-control" value="{{ strtoupper($settings['mail_encryption'] ?? '') ?: 'None' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">From Address</label>
                                <input type="text" class="form-control" value="{{ $settings['mail_from_address'] ?? config('mail.from.address') }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" value="{{ $settings['mail_from_name'] ?? config('mail.from.name') }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Test SMTP Connection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateSelect = document.getElementById('email-test-template');
    const payload = document.getElementById('email-test-payload');
    const previewLink = document.getElementById('email-test-preview-link');

    function renderPayloadInputs() {
        const option = templateSelect.options[templateSelect.selectedIndex];
        const variables = option?.dataset.variables ? JSON.parse(option.dataset.variables) : [];
        payload.innerHTML = '';

        variables.forEach(function (variable) {
            const wrapper = document.createElement('div');
            wrapper.className = 'col-md-6';
            wrapper.innerHTML = '<label class="form-label">&#123;' + variable + '&#125;</label><input type="text" name="payload[' + variable + ']" class="form-control">';
            payload.appendChild(wrapper);
        });

        if (option?.dataset.previewUrl) {
            previewLink.href = option.dataset.previewUrl;
            previewLink.classList.remove('disabled');
        } else {
            previewLink.href = '#';
            previewLink.classList.add('disabled');
        }
    }

    templateSelect?.addEventListener('change', renderPayloadInputs);
    renderPayloadInputs();
});
</script>
@endpush
