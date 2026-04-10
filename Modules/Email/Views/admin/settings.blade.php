@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        @include('email::admin.partials.tabs')

        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.email.settings.update') }}" id="email-settings-form">
            @csrf
            @method('PUT')

            <div class="email-settings-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
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
                                <div class="border rounded email-editor-shell @error('email_header') border-danger @enderror">
                                    <div id="email-header-editor" class="email-rich-editor" data-placeholder="Write the global email header..."></div>
                                </div>
                                <textarea name="email_header" id="email-header-input" hidden class="d-none @error('email_header') is-invalid @enderror">{{ old('email_header', $settings['email_header'] ?? '') }}</textarea>
                                @error('email_header')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Footer</label>
                                <div class="border rounded email-editor-shell @error('email_footer') border-danger @enderror">
                                    <div id="email-footer-editor" class="email-rich-editor" data-placeholder="Write the global email footer..."></div>
                                </div>
                                <textarea name="email_footer" id="email-footer-input" hidden class="d-none @error('email_footer') is-invalid @enderror">{{ old('email_footer', $settings['email_footer'] ?? '') }}</textarea>
                                @error('email_footer')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label">Email Signature</label>
                                <div class="border rounded email-editor-shell @error('email_signature') border-danger @enderror">
                                    <div id="email-signature-editor" class="email-rich-editor" data-placeholder="Write the global email signature..."></div>
                                </div>
                                <textarea name="email_signature" id="email-signature-input" hidden class="d-none @error('email_signature') is-invalid @enderror">{{ old('email_signature', $settings['email_signature'] ?? '') }}</textarea>
                                @error('email_signature')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card email-settings-branding-sticky">
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
    const emailSettingsForm = document.getElementById('email-settings-form');
    const editorConfigs = [
        {
            editorSelector: '#email-header-editor',
            inputSelector: '#email-header-input',
        },
        {
            editorSelector: '#email-footer-editor',
            inputSelector: '#email-footer-input',
        },
        {
            editorSelector: '#email-signature-editor',
            inputSelector: '#email-signature-input',
        }
    ];

    const quillEditors = editorConfigs.map(function (config) {
        const editorElement = document.querySelector(config.editorSelector);
        const inputElement = document.querySelector(config.inputSelector);

        if (!editorElement || !inputElement || typeof Quill === 'undefined') {
            return null;
        }

        const quill = new Quill(editorElement, {
            theme: 'snow',
            placeholder: editorElement.dataset.placeholder || 'Write something...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean']
                ]
            }
        });

        quill.root.innerHTML = inputElement.value || '';

        return {
            quill: quill,
            input: inputElement,
        };
    }).filter(Boolean);

    const decodeHtmlEntities = function (value) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = value;

        return textarea.value;
    };

    const extractEditorHtml = function (quill) {
        const rawHtml = quill.root.innerHTML.trim();
        const plainText = quill.getText().trim();

        if (rawHtml === '<p><br></p>') {
            return '';
        }

        if (rawHtml.includes('&lt;') && /<\/?[a-z][\s\S]*>/i.test(plainText)) {
            return decodeHtmlEntities(plainText).trim();
        }

        return rawHtml;
    };

    emailSettingsForm?.addEventListener('submit', function () {
        quillEditors.forEach(function (instance) {
            instance.input.value = extractEditorHtml(instance.quill);
        });
    });

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

@push('styles')
<style>
.email-settings-toolbar {
    position: sticky;
    top: 100px;
    z-index: 20;
    padding: 12px 0;
    background: var(--bs-body-bg);
}

/* .email-editor-shell {
    background: var(--bs-body-bg);
} */

.email-rich-editor {
    min-height: 180px;
}

.email-rich-editor .ql-editor {
    min-height: 180px;
}

@media (min-width: 992px) {
    .email-settings-branding-sticky {
        position: sticky;
        top: 200px;
    }
}
</style>
@endpush
