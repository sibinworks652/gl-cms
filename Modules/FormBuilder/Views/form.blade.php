@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" action="{{ $isEdit ? route('admin.forms.update', $form) : route('admin.forms.store') }}" id="formbuilder-form">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <input type="hidden" name="schema_payload" id="schema-payload" value="{{ old('schema_payload', $schemaPayload) }}">

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">{{ $isEdit ? 'Edit Form' : 'Create Form' }}</h4>
                            <p class="text-muted mb-0">Configure the form and add fields to the JSON schema.</p>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Form Name</label>
                                <input type="text" name="name" id="form-name" class="form-control @error('name') error-input-bottom @enderror" value="{{ old('name', $form->name) }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="form-slug" class="form-control @error('slug') error-input-bottom @enderror" value="{{ old('slug', $form->slug) }}">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control @error('description') error-input-bottom @enderror">{{ old('description', $form->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active" id="form-is-active" {{ old('is_active', $form->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="form-is-active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Add Field</h4>
                            <p class="text-muted mb-0">Text, email, select, checkbox, radio, and more.</p>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="editing-field-id">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select id="field-type" class="form-select">
                                    @foreach ($fieldTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Label</label>
                                <input type="text" id="field-label" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Machine Name</label>
                                <input type="text" id="field-name" class="form-control" placeholder="full_name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Placeholder</label>
                                <input type="text" id="field-placeholder" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Options</label>
                                <textarea id="field-options" rows="4" class="form-control" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                                <small class="text-muted">Used only for select, checkbox, and radio. One option per line.</small>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="field-required">
                                <label class="form-check-label" for="field-required">Required field</label>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-primary" id="save-field-button">Add Field</button>
                                <button type="button" class="btn btn-light" id="reset-field-button">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="commons-ticky-template-toolbar">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Form Schema Builder</h4>
                            <p class="text-muted mb-0">Build fields, reorder them, and save the schema as JSON.</p>
                        </div>
                        <div class="card-body">
                            @error('schema_payload')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <div id="schema-empty" class="text-center text-muted py-5 border rounded">Add your first field to start building the form.</div>
                            <div id="schema-list" class="d-flex flex-column gap-3"></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Form' : 'Create Form' }}</button>
                        <a href="{{ route('admin.forms.index') }}" class="btn btn-light">Back</a>
                    </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const payloadInput = document.getElementById('schema-payload');
    const list = document.getElementById('schema-list');
    const emptyState = document.getElementById('schema-empty');
    const form = document.getElementById('formbuilder-form');
    const formName = document.getElementById('form-name');
    const formSlug = document.getElementById('form-slug');

    const editingId = document.getElementById('editing-field-id');
    const typeInput = document.getElementById('field-type');
    const labelInput = document.getElementById('field-label');
    const nameInput = document.getElementById('field-name');
    const placeholderInput = document.getElementById('field-placeholder');
    const optionsInput = document.getElementById('field-options');
    const requiredInput = document.getElementById('field-required');
    const saveBtn = document.getElementById('save-field-button');
    const resetBtn = document.getElementById('reset-field-button');

    let fields = parsePayload();

    render();

    form.addEventListener('submit', syncPayload);
    resetBtn.addEventListener('click', resetEditor);

    saveBtn.addEventListener('click', () => {
        const field = collectField();
        if (!field) return;

        if (editingId.value) {
            const index = fields.findIndex((item) => String(item.id) === String(editingId.value));
            if (index >= 0) {
                fields[index] = { ...fields[index], ...field, id: fields[index].id };
            }
        } else {
            fields.push(field);
        }

        syncPayload();
        render();
        resetEditor();
    });

    formName.addEventListener('input', () => {
        if (formSlug.dataset.touched === 'true') return;
        formSlug.value = slugify(formName.value);
    });

    formSlug.addEventListener('input', () => {
        formSlug.dataset.touched = formSlug.value.trim() !== '' ? 'true' : 'false';
    });

    function parsePayload() {
        try {
            const parsed = JSON.parse(payloadInput.value || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }

    function syncPayload() {
        payloadInput.value = JSON.stringify(fields);
    }

    function render() {
        syncPayload();
        list.innerHTML = '';
        emptyState.style.display = fields.length ? 'none' : 'block';

        fields.forEach((field, index) => {
            const card = document.createElement('div');
            card.className = 'card border mb-0';
            card.innerHTML = `
                <div class="card-body d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold">${escapeHtml(field.label)}</div>
                        <div class="text-muted small">${escapeHtml(field.type)} | ${escapeHtml(field.name)}${field.required ? ' | Required' : ''}</div>
                        ${field.options?.length ? `<div class="small mt-1 text-muted">Options: ${escapeHtml(field.options.join(', '))}</div>` : ''}
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-soft-secondary btn-sm" data-action="up"><iconify-icon icon="solar:alt-arrow-up-outline" width="16" height="16" /></button>
                        <button type="button" class="btn btn-soft-secondary btn-sm" data-action="down"><iconify-icon icon="solar:alt-arrow-down-outline" width="16" height="16" /></button>
                        <button type="button" class="btn btn-soft-warning btn-sm" data-action="edit"><iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" /></button>
                        <button type="button" class="btn btn-soft-danger btn-sm" data-action="delete"><iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16" /></button>
                    </div>
                </div>
            `;

            const buttons = card.querySelectorAll('button');
            buttons[0].onclick = () => move(index, index - 1);
            buttons[1].onclick = () => move(index, index + 1);
            buttons[2].onclick = () => loadField(field);
            buttons[3].onclick = () => {
                fields.splice(index, 1);
                syncPayload();
                render();
                if (editingId.value === String(field.id)) resetEditor();
            };

            list.appendChild(card);
        });
    }

    function collectField() {
        const type = typeInput.value;
        const label = labelInput.value.trim();
        const name = nameInput.value.trim();
        const placeholder = placeholderInput.value.trim();
        const options = optionsInput.value
            .split('\n')
            .map((value) => value.trim())
            .filter(Boolean);

        if (!label || !name) {
            alert('Each field needs a label and machine name.');
            return null;
        }

        return {
            id: editingId.value || createId(),
            type,
            label,
            name: slugify(name).replace(/-/g, '_'),
            placeholder: placeholder || null,
            required: requiredInput.checked,
            options: ['select', 'checkbox', 'radio'].includes(type) ? options : [],
        };
    }

    function loadField(field) {
        editingId.value = field.id;
        typeInput.value = field.type;
        labelInput.value = field.label;
        nameInput.value = field.name;
        placeholderInput.value = field.placeholder || '';
        optionsInput.value = (field.options || []).join('\n');
        requiredInput.checked = Boolean(field.required);
        saveBtn.textContent = 'Update Field';
    }

    function resetEditor() {
        editingId.value = '';
        typeInput.value = 'text';
        labelInput.value = '';
        nameInput.value = '';
        placeholderInput.value = '';
        optionsInput.value = '';
        requiredInput.checked = false;
        saveBtn.textContent = 'Add Field';
    }

    function move(from, to) {
        if (to < 0 || to >= fields.length) return;
        const [item] = fields.splice(from, 1);
        fields.splice(to, 0, item);
        syncPayload();
        render();
    }

    function createId() {
        return `field-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;
    }

    function slugify(value) {
        return String(value).toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
</script>
@endpush
