@extends('admin.layouts.app')

@push('styles')
<style>
p {
    margin-bottom: 0 !important;
}

.email-template-toolbar {
    position: sticky;
    top: 100px;
    z-index: 20;
    padding: 12px 0;
    background: var(--bs-body-bg);
}

@media (min-width: 1200px) {
    .email-template-sidebar-sticky {
        position: sticky;
        top: 200px;
    }
}
</style>
@endpush

@section('content')
    <div class="container-xxl">
        @include('email::admin.partials.tabs')

        <form method="POST" action="{{ $isEdit ? route('admin.email.templates.update', $template) : route('admin.email.templates.store') }}" id="email-template-form">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="body" id="email-template-body" value="{{ old('body', $template->body) }}">

            <div class="email-template-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Email Template' : 'Create Email Template' }}</h4>
                    <p class="text-muted mb-0">Design the email visually, insert variables, and preview the full branded output.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.email.templates.index') }}" class="btn btn-light">Back</a>
                    @if($isEdit)
                        <a href="{{ route('admin.email.templates.preview', $template) }}" target="_blank" class="btn btn-outline-primary">Preview Email</a>
                    @endif
                    <button type="submit" class="btn btn-primary">Save Template</button>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">Please fix the highlighted template fields and try again.</div>
            @endif

            <div class="row g-4">
                <div class="col-xl-4">

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Template Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Template Name</label>
                                <input type="text" name="name" id="email-template-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="email-template-slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $template->slug) }}" placeholder="invoice">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', $template->subject) }}" required>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">To Email Addresses</label>
                                <textarea name="to_emails" rows="3" class="form-control @error('to_emails') is-invalid @enderror" placeholder="hr@example.com, manager@example.com">{{ old('to_emails', implode(', ', $template->to_emails ?? [])) }}</textarea>
                                <div class="form-text">Optional default recipients for this template. Separate multiple emails with commas, semicolons, or new lines.</div>
                                @error('to_emails')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CC Email Addresses</label>
                                <textarea name="cc_emails" rows="2" class="form-control @error('cc_emails') is-invalid @enderror" placeholder="owner@example.com, audit@example.com">{{ old('cc_emails', implode(', ', $template->cc_emails ?? [])) }}</textarea>
                                <div class="form-text">Optional CC recipients that should always receive this template.</div>
                                @error('cc_emails')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="email-template-status" @checked(old('status', $template->status) == 1)>
                                <label class="form-check-label" for="email-template-status">Active</label>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <h6 class="mb-1">Global Layout Parts</h6>
                                <div class="form-text">Turn off parts that this template should not include.</div>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="use_header" value="0">
                                <input class="form-check-input" type="checkbox" name="use_header" value="1" id="email-template-use-header" @checked(old('use_header', $template->use_header ?? true) == 1)>
                                <label class="form-check-label" for="email-template-use-header">Use email header</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="use_footer" value="0">
                                <input class="form-check-input" type="checkbox" name="use_footer" value="1" id="email-template-use-footer" @checked(old('use_footer', $template->use_footer ?? true) == 1)>
                                <label class="form-check-label" for="email-template-use-footer">Use email footer</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="use_signature" value="0">
                                <input class="form-check-input" type="checkbox" name="use_signature" value="1" id="email-template-use-signature" @checked(old('use_signature', $template->use_signature ?? true) == 1)>
                                <label class="form-check-label" for="email-template-use-signature">Use signature</label>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Variables</h5>
                        </div>
                        <div class="card-body">
                            <div id="email-variable-list">
                                @foreach(old('variables', $template->variables ?? []) as $variable)
                                    <div class="input-group mb-2 email-variable-row">
                                        <span class="input-group-text">&#123;</span>
                                        <input type="text" name="variables[]" class="form-control email-variable-input" value="{{ $variable }}" pattern="[A-Za-z0-9_]+">
                                        <span class="input-group-text">&#125;</span>
                                        <button type="button" class="btn btn-light remove-email-variable">Remove</button>
                                    </div>
                                @endforeach
                            </div>
                            @error('variables.*')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                            <div class="input-group mb-3">
                                <input type="text" id="email-variable-search" class="form-control" placeholder="Search or add variable">
                                <button type="button" class="btn btn-outline-primary" id="add-email-variable">Add</button>
                            </div>
                            <div class="small text-muted mb-2">Search variables, click one to insert it into the active text block, or type a new variable and click Add.</div>
                            <div class="mt-2" id="email-variable-chip-list"></div>
                            <div class="d-none" id="email-starter-variable-list">
                                @foreach($starterVariables as $variable)
                                    <span data-variable="{{ $variable }}"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Image Upload</h5>
                        </div>
                        <div class="card-body">
                            <input type="file" id="email-builder-image" class="form-control" accept="image/*">
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="upload-email-builder-image">Upload Image</button>
                            <div class="small mt-2" id="email-builder-image-status"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card email-template-sidebar-sticky">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-1">Drag & Drop Builder</h5>
                                <p class="text-muted mb-0">Add blocks, edit inline, drag to reorder, then preview.</p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-light btn-sm email-add-block" data-type="text">Text</button>
                                <button type="button" class="btn btn-light btn-sm email-add-block" data-type="image">Image</button>
                                <button type="button" class="btn btn-light btn-sm email-add-block" data-type="button">Button</button>
                                <button type="button" class="btn btn-light btn-sm email-add-block" data-type="divider">Divider</button>
                                <button type="button" class="btn btn-light btn-sm email-add-block" data-type="spacer">Spacer</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="email-preview-toggle">Preview Mode</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="email-builder-canvas" class="border rounded p-3 bg-light"></div>
                            <div id="email-builder-preview" class="border rounded p-3 d-none" style="min-height:420px;"></div>
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
    const form = document.getElementById('email-template-form');
    const nameInput = document.getElementById('email-template-name');
    const slugInput = document.getElementById('email-template-slug');
    const bodyInput = document.getElementById('email-template-body');
    const canvas = document.getElementById('email-builder-canvas');
    const preview = document.getElementById('email-builder-preview');
    const previewToggle = document.getElementById('email-preview-toggle');
    const variableList = document.getElementById('email-variable-list');
    const variableSearch = document.getElementById('email-variable-search');
    const variableChipList = document.getElementById('email-variable-chip-list');
    const starterVariableList = document.getElementById('email-starter-variable-list');
    const imageInput = document.getElementById('email-builder-image');
    const imageStatus = document.getElementById('email-builder-image-status');
    let slugTouched = Boolean(slugInput && slugInput.value);
    let draggedBlock = null;
    let focusedEditable = null;
    let focusedQuill = null;

    slugInput?.addEventListener('input', () => slugTouched = true);
    nameInput?.addEventListener('input', function () {
        if (!slugTouched && slugInput) {
            slugInput.value = nameInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    });

    function blockTemplate(type, content = '') {
        if (type === 'image') {
            content = content || '<img src="https://placehold.co/640x240" alt="Email image" style="max-width:100%; border-radius:8px;">';
        } else if (type === 'button') {
            content = content || buttonMarkup({
                label: 'Call to Action',
                url: '{button_url}',
                background: '#ff6c2f',
                color: '#ffffff',
                align: 'left',
            });
        } else if (type === 'divider') {
            content = '<hr style="border:0; border-top:1px solid #e2e8f0; margin:18px 0;">';
        } else if (type === 'spacer') {
            content = '<div style="height:28px; line-height:28px;">&nbsp;</div>';
        } else {
            content = content || '<p contenteditable="true" data-placeholder="Write your email content here."></p>';
        }

        return content;
    }

    function buttonMarkup(settings = {}) {
        const label = escapeAttribute(settings.label || 'Call to Action');
        const url = escapeAttribute(settings.url || '{button_url}');
        const background = settings.background || '#ff6c2f';
        const color = settings.color || '#ffffff';
        const align = settings.align || 'left';

        return `<div style="text-align:${align};">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; display:inline-table;">
                <tr>
                    <td bgcolor="${background}" style="background:${background}; border-radius:8px;">
                        <a href="${url}" style="display:inline-block; background:${background}; color:${color}; padding:12px 18px; border-radius:8px; text-decoration:none; font-weight:600;">${label}</a>
                    </td>
                </tr>
            </table>
        </div>`;
    }

    function createBlock(type, content) {
        const wrapper = document.createElement('div');
        wrapper.className = 'email-builder-block border bg-white rounded p-2 mb-2';
        wrapper.draggable = true;
        wrapper.dataset.type = type;
        wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <span class="badge bg-light text-dark">${type}</span>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-light btn-sm email-duplicate-block">Duplicate</button>
                    <button type="button" class="btn btn-light btn-sm email-remove-block">Remove</button>
                </div>
            </div>
            <div class="email-block-content p-2" contenteditable="false">${blockTemplate(type, content)}</div>
            ${type === 'button' ? buttonSettingsTemplate(content) : ''}
        `;
        bindBlock(wrapper);
        return wrapper;
    }

    function buttonSettingsTemplate(content = '') {
        const settings = buttonSettingsFromContent(content);

        return `
            <div class="email-button-settings row g-2 mt-2">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Button Text</label>
                    <input type="text" class="form-control form-control-sm email-button-label" value="${escapeAttribute(settings.label)}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Button URL</label>
                    <input type="text" class="form-control form-control-sm email-button-url" value="${escapeAttribute(settings.url)}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Background</label>
                    <input type="color" class="form-control form-control-color form-control-sm email-button-bg" value="${settings.background}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Text</label>
                    <input type="color" class="form-control form-control-color form-control-sm email-button-color" value="${settings.color}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Alignment</label>
                    <select class="form-select form-select-sm email-button-align">
                        <option value="left" ${settings.align === 'left' ? 'selected' : ''}>Left</option>
                        <option value="center" ${settings.align === 'center' ? 'selected' : ''}>Center</option>
                        <option value="right" ${settings.align === 'right' ? 'selected' : ''}>Right</option>
                    </select>
                </div>
            </div>
        `;
    }

    function buttonSettingsFromContent(content = '') {
        const container = document.createElement('div');
        container.innerHTML = content || blockTemplate('button');
        const wrapper = container.firstElementChild;
        const link = container.querySelector('a');
        const cell = container.querySelector('td');
        const style = link?.getAttribute('style') || '';
        const cellStyle = cell?.getAttribute('style') || '';

        return {
            label: link?.textContent?.trim() || 'Call to Action',
            url: link?.getAttribute('href') || '{button_url}',
            background: colorValue(cell?.getAttribute('bgcolor') || styleValue(cellStyle, 'background') || styleValue(style, 'background'), '#ff6c2f'),
            color: colorValue(styleValue(style, 'color'), '#ffffff'),
            align: wrapper?.style?.textAlign || 'left',
        };
    }

    function styleValue(style, property) {
        const match = style.match(new RegExp(property + '\\s*:\\s*([^;]+)', 'i'));

        return match ? match[1].trim() : '';
    }

    function colorValue(value, fallback) {
        return /^#[0-9A-Fa-f]{6}$/.test(value) ? value : fallback;
    }

    function escapeAttribute(value) {
        return String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function bindBlock(block) {
        block.addEventListener('dragstart', function () {
            draggedBlock = block;
            block.classList.add('opacity-50');
        });
        block.addEventListener('dragend', function () {
            draggedBlock = null;
            block.classList.remove('opacity-50');
        });
        block.addEventListener('dragover', function (event) {
            event.preventDefault();
            if (!draggedBlock || draggedBlock === block) return;
            const rect = block.getBoundingClientRect();
            canvas.insertBefore(draggedBlock, (event.clientY - rect.top) < (rect.height / 2) ? block : block.nextSibling);
        });
        block.querySelector('.email-remove-block')?.addEventListener('click', () => block.remove());
        block.querySelector('.email-duplicate-block')?.addEventListener('click', () => block.after(createBlock(block.dataset.type, blockContent(block))));
        block.querySelector('.email-block-content')?.addEventListener('focusin', function (event) {
            focusedEditable = event.currentTarget;
        });
        initializeTextEditor(block);
        bindButtonSettings(block);
    }

    function bindButtonSettings(block) {
        if (block.dataset.type !== 'button') {
            return;
        }

        block.querySelectorAll('.email-button-label, .email-button-url, .email-button-bg, .email-button-color, .email-button-align').forEach(function (input) {
            input.addEventListener('input', () => updateButtonBlock(block));
            input.addEventListener('change', () => updateButtonBlock(block));
        });

        updateButtonBlock(block);
    }

    function updateButtonBlock(block) {
        const label = block.querySelector('.email-button-label')?.value.trim() || 'Call to Action';
        const url = block.querySelector('.email-button-url')?.value.trim() || '{button_url}';
        const background = block.querySelector('.email-button-bg')?.value || '#ff6c2f';
        const color = block.querySelector('.email-button-color')?.value || '#ffffff';
        const align = block.querySelector('.email-button-align')?.value || 'left';
        const content = block.querySelector('.email-block-content');

        content.innerHTML = buttonMarkup({ label, url, background, color, align });
    }

    function initializeTextEditor(block) {
        const content = block.querySelector('.email-block-content');

        if (!content || block.dataset.type !== 'text' || typeof Quill === 'undefined' || content.__quill) {
            return;
        }

        const initialHtml = content.innerHTML;
        const quill = new Quill(content, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean']
                ]
            }
        });

        quill.root.innerHTML = initialHtml;
        content.__quill = quill;
        quill.on('selection-change', function (range) {
            if (range) {
                focusedQuill = quill;
                focusedEditable = content;
            }
        });
    }

    function blockContent(block) {
        const content = block.querySelector('.email-block-content');

        if (!content) {
            return '';
        }

        return content.__quill ? content.__quill.root.innerHTML.trim() : content.innerHTML.trim();
    }

    function renderBody() {
        return Array.from(canvas.querySelectorAll('.email-builder-block'))
            .map((block) => `<!-- email-block:${block.dataset.type} -->${blockContent(block)}<!-- /email-block -->`)
            .join("\n");
    }

    function loadBody(html) {
        if (!html) {
            canvas.appendChild(createBlock('text'));
            return;
        }

        const matches = Array.from(html.matchAll(/<!--\s*email-block:([a-z]+)\s*-->([\s\S]*?)<!--\s*\/email-block\s*-->/gi));

        if (!matches.length) {
            canvas.appendChild(createBlock('text', html));
            return;
        }

        matches.forEach(function (match) {
            canvas.appendChild(createBlock(match[1], match[2].trim()));
        });
    }

    document.querySelectorAll('.email-add-block').forEach(function (button) {
        button.addEventListener('click', function () {
            canvas.appendChild(createBlock(button.dataset.type));
        });
    });

    previewToggle?.addEventListener('click', function () {
        const showingPreview = preview.classList.contains('d-none');
        preview.innerHTML = `<div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; min-height:360px;">${renderBody()}</div>`;
        preview.classList.toggle('d-none', !showingPreview);
        canvas.classList.toggle('d-none', showingPreview);
        previewToggle.textContent = showingPreview ? 'Builder Mode' : 'Preview Mode';
    });

    function normalizeVariable(value) {
        return value.trim().replace(/[{}]/g, '').replace(/[^A-Za-z0-9_]/g, '_').replace(/^_+|_+$/g, '');
    }

    function variableValues() {
        const starter = Array.from(starterVariableList?.querySelectorAll('[data-variable]') || [])
            .map((item) => normalizeVariable(item.dataset.variable))
            .filter(Boolean);

        return Array.from(new Set([...savedVariableValues(), ...starter]));
    }

    function savedVariableValues() {
        return Array.from(variableList.querySelectorAll('.email-variable-input'))
            .map((input) => normalizeVariable(input.value))
            .filter(Boolean);
    }

    function appendVariableRow(value = '') {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 email-variable-row';
        row.innerHTML = '<span class="input-group-text">&#123;</span><input type="text" name="variables[]" class="form-control email-variable-input" pattern="[A-Za-z0-9_]+"><span class="input-group-text">&#125;</span><button type="button" class="btn btn-light remove-email-variable">Remove</button>';
        row.querySelector('.email-variable-input').value = value;
        variableList.appendChild(row);
        renderVariableChips();
    }

    function renderVariableChips() {
        const search = normalizeVariable(variableSearch?.value || '').toLowerCase();
        const variables = variableValues().filter((variable) => !search || variable.toLowerCase().includes(search));
        variableChipList.innerHTML = '';

        if (!variables.length) {
            variableChipList.innerHTML = '<div class="small text-muted">No variables found.</div>';
            return;
        }

        variables.forEach(function (variable) {
            const placeholder = String.fromCharCode(123) + variable + String.fromCharCode(125);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-light btn-sm mb-2 me-1 email-insert-variable';
            button.dataset.variable = placeholder;
            button.textContent = placeholder;
            variableChipList.appendChild(button);
        });
    }

    function addVariableFromSearch() {
        const variable = normalizeVariable(variableSearch?.value || '');

        if (!variable) {
            variableSearch?.focus();
            return;
        }

        if (!savedVariableValues().includes(variable)) {
            appendVariableRow(variable);
        }

        variableSearch.value = '';
        renderVariableChips();
    }

    document.getElementById('add-email-variable')?.addEventListener('click', addVariableFromSearch);
    variableSearch?.addEventListener('input', renderVariableChips);
    variableSearch?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addVariableFromSearch();
        }
    });

    variableList?.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-email-variable')) {
            event.target.closest('.email-variable-row')?.remove();
            renderVariableChips();
        }
    });
    variableList?.addEventListener('input', function (event) {
        if (event.target.classList.contains('email-variable-input')) {
            renderVariableChips();
        }
    });

    variableChipList?.addEventListener('click', function (event) {
        const button = event.target.closest('.email-insert-variable');

        if (!button) {
            return;
        }

        const variable = button.dataset.variable;
        const variableName = normalizeVariable(variable);

        if (variableName && !savedVariableValues().includes(variableName)) {
            appendVariableRow(variableName);
        }

        if (focusedQuill) {
            const range = focusedQuill.getSelection(true);
            focusedQuill.insertText(range ? range.index : focusedQuill.getLength(), variable);
            focusedQuill.focus();
            return;
        }

        if (focusedEditable) {
            focusedEditable.focus();
            document.execCommand('insertText', false, variable);
            return;
        }

        navigator.clipboard?.writeText(variable);
        if (window.showAdminToast) window.showAdminToast(variable + ' copied.', 'success');
    });

    document.getElementById('upload-email-builder-image')?.addEventListener('click', async function () {
        if (!imageInput.files.length) {
            imageStatus.textContent = 'Choose an image first.';
            imageStatus.className = 'small mt-2 text-danger';
            return;
        }

        const payload = new FormData();
        payload.append('image', imageInput.files[0]);
        imageStatus.textContent = 'Uploading image...';
        imageStatus.className = 'small mt-2 text-muted';

        try {
            const response = await fetch(@json(route('admin.email.templates.images.store')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: payload,
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Image upload failed.');
            canvas.appendChild(createBlock('image', '<img src="' + result.url + '" alt="Email image" style="max-width:100%; border-radius:8px;">'));
            imageStatus.textContent = 'Image uploaded and added to the builder.';
            imageStatus.className = 'small mt-2 text-success';
            imageInput.value = '';
        } catch (error) {
            imageStatus.textContent = error.message || 'Image upload failed.';
            imageStatus.className = 'small mt-2 text-danger';
        }
    });

    form?.addEventListener('submit', function () {
        variableList.querySelectorAll('.email-variable-input').forEach(function (input) {
            input.value = normalizeVariable(input.value);
        });
        bodyInput.value = renderBody();
    });

    loadBody(bodyInput.value);
    renderVariableChips();
});
</script>
@endpush
@push('styles')
    <style>
        /* Show the placeholder when the paragraph is empty */
p[contenteditable="true"]:empty:before {
  content: attr(data-placeholder);
  color: #888;
  cursor: text;
}
    </style>
@endpush
