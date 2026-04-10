@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <form method="POST" action="{{ $isEdit ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" id="faqForm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit FAQ' : 'Create FAQ' }}</h4>
                    <p class="text-muted mb-0">Add reusable help content for support pages, onboarding, and common user questions.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.faqs.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">Save FAQ</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">FAQ Content</h5></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Question</label>
                                    <input type="text" name="question" class="form-control @error('question') is-invalid @enderror" value="{{ old('question', $faq->question) }}" required>
                                    @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Answer</label>
                                    <div class="border rounded">
                                        <div id="faq-answer-editor" style="height: 320px;"></div>
                                    </div>
                                    <textarea name="answer" id="faq-answer" hidden class="d-none @error('answer') is-invalid @enderror">{{ old('answer', $faq->answer) }}</textarea>
                                    @error('answer')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Publishing</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="faq_category_id" id="faq-category-select" class="form-select @error('faq_category_id') is-invalid @enderror">
                                    <option value="">No category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('faq_category_id', $faq->faq_category_id) === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('faq_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($adminUser?->can('faq-categories.create'))
                                    <div class="input-group mt-2" id="faq-category-inline-create" data-store-url="{{ route('admin.faq-categories.store') }}" data-csrf-token="{{ csrf_token() }}">
                                        <input type="text" class="form-control" id="new-faq-category-name" placeholder="New category name">
                                        <button class="btn btn-outline-input" type="button" id="add-faq-category-button">
                                            <span class="spinner-border spinner-border-sm me-1 d-none" id="add-faq-category-spinner" aria-hidden="true"></span>
                                            <span id="add-faq-category-label">Add</span>
                                        </button>
                                    </div>
                                    <div class="small mt-1" id="faq-category-inline-status" aria-live="polite"></div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order</label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $faq->order) }}">
                                @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="status" @checked(old('status', $faq->status) == 1)>
                                <label class="form-check-label" for="status">Active</label>
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
    const quill = new Quill('#faq-answer-editor', {
        theme: 'snow',
        placeholder: 'Write the answer...',
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

    quill.root.innerHTML = @json(old('answer', $faq->answer ?? ''));

    document.getElementById('faqForm')?.addEventListener('submit', function () {
        let html = quill.root.innerHTML;
        if (html === '<p><br></p>') {
            html = '';
        }

        document.getElementById('faq-answer').value = html;
    });

    const inlineCreate = document.getElementById('faq-category-inline-create');
    const categorySelect = document.getElementById('faq-category-select');
    const categoryNameInput = document.getElementById('new-faq-category-name');
    const addButton = document.getElementById('add-faq-category-button');
    const spinner = document.getElementById('add-faq-category-spinner');
    const label = document.getElementById('add-faq-category-label');
    const status = document.getElementById('faq-category-inline-status');
    if (inlineCreate && categorySelect && categoryNameInput && addButton) {
        const setLoading = (isLoading) => {
            categoryNameInput.disabled = isLoading;
            addButton.disabled = isLoading;
            spinner?.classList.toggle('d-none', !isLoading);
            if (label) label.textContent = isLoading ? 'Adding...' : 'Add';
        };
        const setStatus = (message, className) => {
            if (!status) return;
            status.textContent = message;
            status.className = 'small mt-1 ' + className;
        };
        const addCategory = async () => {
            const name = categoryNameInput.value.trim();
            if (!name) {
                categoryNameInput.focus();
                setStatus('Enter a category name first.', 'text-danger');
                return;
            }
            setLoading(true);
            setStatus('Saving category...', 'text-muted');
            const previousValue = categorySelect.value;
            const loadingOption = new Option('Adding category...', '__loading_category__', true, true);
            loadingOption.disabled = true;
            categorySelect.appendChild(loadingOption);
            try {
                const response = await fetch(inlineCreate.dataset.storeUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': inlineCreate.dataset.csrfToken || '',
                    },
                    body: JSON.stringify({ name, status: 1 }),
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.errors?.name?.[0] || result.message || 'Unable to add category.');
                loadingOption.remove();
                categorySelect.appendChild(new Option(result.category.name, result.category.id, true, true));
                categoryNameInput.value = '';
                setStatus(result.message || 'Category added.', 'text-success');
            } catch (error) {
                loadingOption.remove();
                categorySelect.value = previousValue;
                setStatus(error.message || 'Unable to add category.', 'text-danger');
            } finally {
                setLoading(false);
                categoryNameInput.focus();
            }
        };
        addButton.addEventListener('click', addCategory);
        categoryNameInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') { event.preventDefault(); addCategory(); }
        });
    }
});
</script>
@endpush
