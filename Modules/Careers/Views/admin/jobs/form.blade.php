@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" action="{{ $isEdit ? route('admin.jobs.update', $job) : route('admin.jobs.store') }}" id="careerJobForm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="d-flex commons-ticky-template-toolbar justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? 'Edit Job' : 'Create Job' }}</h4>
                    <p class="text-muted mb-0">Publish structured openings with SEO, requirements, and expiry controls.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-light">Back</a>
                    @if($isEdit)
                        <a href="{{ route('careers.show', $job->slug) }}" target="_blank" class="btn btn-outline-primary">Open Job</a>
                    @endif
                    <button type="submit" class="btn btn-primary">Save Job</button>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Job Content</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" name="title" id="career-job-title" value="{{ old('title', $job->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Slug</label>
                                    <input type="text" name="slug" id="career-job-slug" value="{{ old('slug', $job->slug) }}" class="form-control @error('slug') is-invalid @enderror" placeholder="senior-php-developer">
                                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="short_description" rows="3" class="form-control @error('short_description') is-invalid @enderror" required>{{ old('short_description', $job->short_description) }}</textarea>
                                    @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Full Description</label>
                                    <div class="border rounded">
                                        <div id="career-job-editor" style="height: 320px;"></div>
                                    </div>
                                    <textarea name="description" id="career-job-description" hidden class="d-none @error('description') is-invalid @enderror">{{ old('description', $job->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Skills / Requirements</label>
                                    <textarea name="skills" rows="6" class="form-control @error('skills') is-invalid @enderror" placeholder="Laravel, Vue, REST APIs">{{ old('skills', $job->skills) }}</textarea>
                                    @error('skills')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Detailed Requirements</label>
                                    <textarea name="requirements" rows="6" class="form-control @error('requirements') is-invalid @enderror">{{ old('requirements', $job->requirements) }}</textarea>
                                    @error('requirements')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Responsibilities</label>
                                    <textarea name="responsibilities" rows="6" class="form-control @error('responsibilities') is-invalid @enderror">{{ old('responsibilities', $job->responsibilities) }}</textarea>
                                    @error('responsibilities')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Benefits</label>
                                    <textarea name="benefits" rows="6" class="form-control @error('benefits') is-invalid @enderror">{{ old('benefits', $job->benefits) }}</textarea>
                                    @error('benefits')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">SEO</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" value="{{ old('meta_title', $job->meta_title) }}" class="form-control @error('meta_title') is-invalid @enderror">
                                    @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" rows="4" class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description', $job->meta_description) }}</textarea>
                                    @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card common-template-sidebar-sticky ">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Publishing</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Uncategorized</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('category_id', $job->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" value="{{ old('location', $job->location) }}" class="form-control @error('location') is-invalid @enderror" placeholder="Ahmedabad / Remote" required>
                                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Job Type</label>
                                <select name="job_type" class="form-select @error('job_type') is-invalid @enderror" required>
                                    @foreach($jobTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('job_type', $job->job_type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('job_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Experience Required</label>
                                <input type="text" name="experience" value="{{ old('experience', $job->experience) }}" class="form-control @error('experience') is-invalid @enderror" placeholder="3+ years" required>
                                @error('experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Salary Range</label>
                                    <input type="text" name="salary" value="{{ old('salary', $job->salary) }}" class="form-control @error('salary') is-invalid @enderror" placeholder="5-8 LPA">
                                    @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Vacancies</label>
                                    <input type="number" min="1" name="vacancies" value="{{ old('vacancies', $job->vacancies ?? 1) }}" class="form-control @error('vacancies') is-invalid @enderror" required>
                                    @error('vacancies')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', $job->status) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry_date" value="{{ old('expiry_date', $job->expiry_date?->format('Y-m-d')) }}" class="form-control @error('expiry_date') is-invalid @enderror">
                                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="form-check form-switch mt-3">
                                <input type="hidden" name="is_featured" value="0">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="career-job-featured" @checked(old('is_featured', $job->is_featured) == 1)>
                                <label class="form-check-label" for="career-job-featured">Featured job</label>
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
    const titleInput = document.getElementById('career-job-title');
    const slugInput = document.getElementById('career-job-slug');
    let slugTouched = Boolean(slugInput && slugInput.value);

    if (slugInput) {
        slugInput.addEventListener('input', function () {
            slugTouched = true;
        });
    }

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugTouched) {
                slugInput.value = titleInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
    }

    if (typeof Quill !== 'undefined') {
        const quill = new Quill('#career-job-editor', {
            theme: 'snow',
            placeholder: 'Write the full job description...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'blockquote'],
                    ['clean']
                ]
            }
        });

        quill.root.innerHTML = @json(old('description', $job->description ?? ''));

        document.getElementById('careerJobForm').addEventListener('submit', function () {
            let html = quill.root.innerHTML;

            if (html === '<p><br></p>') {
                html = '';
            }

            document.getElementById('career-job-description').value = html;
        });
    }
});
</script>
@endpush
