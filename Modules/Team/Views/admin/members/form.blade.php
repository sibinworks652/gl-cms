@extends('admin.layouts.app')

@section('content')
    @php($adminUser = auth('admin')->user())
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.team-members.update', $member) : route('admin.team-members.store') }}" id="teamMemberForm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex commons-ticky-template-toolbar justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Team Member' : 'Create Team Member' }}</h4>
                    <p class="text-muted mb-0">Build reusable profile pages with department grouping, social links, and SEO details.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.team-members.index') }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">Save Member</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Profile</h5></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" id="team-member-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $member->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" id="team-member-slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $member->slug) }}" placeholder="john-doe">
                                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Designation</label>
                                    <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $member->designation) }}" required>
                                    @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <select name="department_id" id="team-department-select" class="form-select @error('department_id') is-invalid @enderror">
                                        <option value="">No department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @selected((string) old('department_id', $member->department_id) === (string) $department->id)>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if($adminUser?->can('team-departments.create'))
                                        <div class="input-group mt-2" id="team-department-inline-create" data-store-url="{{ route('admin.team-departments.store') }}" data-csrf-token="{{ csrf_token() }}">
                                            <input type="text" class="form-control" id="new-team-department-name" placeholder="New department name" aria-label="New department name">
                                            <button class="btn btn-outline-input" type="button" id="add-team-department-button">
                                                <span class="spinner-border spinner-border-sm me-1 d-none" id="add-team-department-spinner" aria-hidden="true"></span>
                                                <span id="add-team-department-label">Add</span>
                                            </button>
                                        </div>
                                        <div class="small mt-1" id="team-department-inline-status" aria-live="polite"></div>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Short Bio</label>
                                    <textarea name="short_bio" rows="3" class="form-control @error('short_bio') is-invalid @enderror">{{ old('short_bio', $member->short_bio) }}</textarea>
                                    @error('short_bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="7" class="form-control @error('description') is-invalid @enderror">{{ old('description', $member->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title mb-0">Contact & Social Links</h5></div>
                        <div class="card-body">
                            @php($socialLinks = old('social_links', $member->social_links ?? []))
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $member->email) }}">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $member->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Facebook URL</label>
                                    <input type="url" name="social_links[facebook]" class="form-control @error('social_links.facebook') is-invalid @enderror" value="{{ data_get($socialLinks, 'facebook') }}">
                                    @error('social_links.facebook')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Twitter / X URL</label>
                                    <input type="url" name="social_links[twitter]" class="form-control @error('social_links.twitter') is-invalid @enderror" value="{{ data_get($socialLinks, 'twitter') }}">
                                    @error('social_links.twitter')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">LinkedIn URL</label>
                                    <input type="url" name="social_links[linkedin]" class="form-control @error('social_links.linkedin') is-invalid @enderror" value="{{ data_get($socialLinks, 'linkedin') }}">
                                    @error('social_links.linkedin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Instagram URL</label>
                                    <input type="url" name="social_links[instagram]" class="form-control @error('social_links.instagram') is-invalid @enderror" value="{{ data_get($socialLinks, 'instagram') }}">
                                    @error('social_links.instagram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Website URL</label>
                                    <input type="url" name="social_links[website]" class="form-control @error('social_links.website') is-invalid @enderror" value="{{ data_get($socialLinks, 'website') }}">
                                    @error('social_links.website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title mb-0">SEO</h5></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $member->meta_title) }}">
                                    @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" rows="4" class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description', $member->meta_description) }}</textarea>
                                    @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="common-template-sidebar-sticky">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Publishing</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Order</label>
                                <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $member->order) }}">
                                @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="status" @checked(old('status', $member->status) == 1)>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is-featured" @checked(old('is_featured', $member->is_featured) == 1)>
                                <label class="form-check-label" for="is-featured">Featured</label>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title mb-0">Photo</h5></div>
                        <div class="card-body">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($member->image_url)
                                <img src="{{ $member->image_url }}" alt="{{ $member->name }}" class="img-fluid rounded mt-3">
                            @endif
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
    const nameInput = document.getElementById('team-member-name');
    const slugInput = document.getElementById('team-member-slug');
    let slugTouched = Boolean(slugInput && slugInput.value);
    slugInput?.addEventListener('input', () => slugTouched = true);
    nameInput?.addEventListener('input', function () {
        if (!slugTouched) slugInput.value = nameInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    });

    const inlineCreate = document.getElementById('team-department-inline-create');
    const departmentSelect = document.getElementById('team-department-select');
    const departmentNameInput = document.getElementById('new-team-department-name');
    const addButton = document.getElementById('add-team-department-button');
    const spinner = document.getElementById('add-team-department-spinner');
    const label = document.getElementById('add-team-department-label');
    const status = document.getElementById('team-department-inline-status');
    if (inlineCreate && departmentSelect && departmentNameInput && addButton) {
        const setLoading = (isLoading) => {
            departmentNameInput.disabled = isLoading;
            addButton.disabled = isLoading;
            spinner?.classList.toggle('d-none', !isLoading);
            if (label) label.textContent = isLoading ? 'Adding...' : 'Add';
        };
        const setStatus = (message, className) => {
            if (!status) return;
            status.textContent = message;
            status.className = 'small mt-1 ' + className;
        };
        const addDepartment = async () => {
            const name = departmentNameInput.value.trim();
            if (!name) {
                departmentNameInput.focus();
                setStatus('Enter a department name first.', 'text-danger');
                return;
            }
            setLoading(true);
            setStatus('Saving department...', 'text-muted');
            const previousValue = departmentSelect.value;
            const loadingOption = new Option('Adding department...', '__loading_department__', true, true);
            loadingOption.disabled = true;
            departmentSelect.appendChild(loadingOption);
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
                if (!response.ok) throw new Error(result.errors?.name?.[0] || result.message || 'Unable to add department.');
                loadingOption.remove();
                const option = new Option(result.department.name, result.department.id, true, true);
                departmentSelect.appendChild(option);
                departmentNameInput.value = '';
                setStatus(result.message || 'Department added.', 'text-success');
            } catch (error) {
                loadingOption.remove();
                departmentSelect.value = previousValue;
                setStatus(error.message || 'Unable to add department.', 'text-danger');
            } finally {
                setLoading(false);
                departmentNameInput.focus();
            }
        };
        addButton.addEventListener('click', addDepartment);
        departmentNameInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') { event.preventDefault(); addDepartment(); }
        });
    }
});
</script>
@endpush
