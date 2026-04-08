@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('admin.seo.update', $seoSetting) : route('admin.seo.store') }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">{{ $isEdit ? 'Edit SEO Setting' : 'Create SEO Setting' }}</h4>
                        <p class="text-muted mb-0">Add search and social metadata for a page, route, slug, or module item.</p>
                    </div>
                    <a href="{{ route('admin.seo.index') }}" class="btn btn-light btn-sm">Back</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Page Type</label>
                            <select name="page_type" id="seo-page-type" class="form-select @error('page_type') is-invalid @enderror">
                                @foreach($pageTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('page_type', $seoSetting->page_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('page_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Page Key</label>
                            <div class="input-group">
                                <span class="input-group-text" >{{ url('/') }}/</span>
                                <input type="text" name="page_key" id="seo-page-key" class="form-control @error('page_key') is-invalid @enderror" value="{{ old('page_key', $seoSetting->page_key) }}" placeholder="about-us">
                            </div>
                            <small class="text-muted" id="seo-page-key-help">For a normal page, enter the URL path without the domain, like about-us or contact.</small>
                            @error('page_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Page Label</label>
                            <input type="text" name="page_label" class="form-control @error('page_label') is-invalid @enderror" value="{{ old('page_label', $seoSetting->page_label) }}" placeholder="About Us">
                            @error('page_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="seo_meta_title" class="form-control @error('seo_meta_title') is-invalid @enderror" value="{{ old('seo_meta_title', $seoSetting->seo_meta_title) }}">
                            @error('seo_meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Meta Description</label>
                            <textarea name="seo_meta_description" rows="3" class="form-control @error('seo_meta_description') is-invalid @enderror">{{ old('seo_meta_description', $seoSetting->seo_meta_description) }}</textarea>
                            @error('seo_meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Meta Keywords</label>
                            <textarea name="seo_meta_keywords" rows="2" class="form-control @error('seo_meta_keywords') is-invalid @enderror" placeholder="cms, website, services">{{ old('seo_meta_keywords', $seoSetting->seo_meta_keywords) }}</textarea>
                            @error('seo_meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">OG Image</label>
                            <input type="file" name="seo_og_image" class="form-control @error('seo_og_image') is-invalid @enderror" accept="image/*">
                            @error('seo_og_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($seoSetting->seo_og_image)
                                <div class="mt-2">
                                    <img src="{{ $seoSetting->ogImageUrl() }}" alt="OG image" style="max-height: 80px; max-width: 180px;">
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Twitter Card</label>
                            <select name="seo_twitter_card" class="form-select @error('seo_twitter_card') is-invalid @enderror">
                                @foreach($twitterCards as $value => $label)
                                    <option value="{{ $value }}" @selected(old('seo_twitter_card', $seoSetting->seo_twitter_card) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('seo_twitter_card')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Canonical URL</label>
                            <input type="url" name="seo_canonical_url" class="form-control @error('seo_canonical_url') is-invalid @enderror" value="{{ old('seo_canonical_url', $seoSetting->seo_canonical_url) }}">
                            @error('seo_canonical_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Indexing</label>
                            <select name="seo_indexing" class="form-select @error('seo_indexing') is-invalid @enderror">
                                <option value="index" @selected(old('seo_indexing', $seoSetting->seo_indexing) === 'index')>index</option>
                                <option value="noindex" @selected(old('seo_indexing', $seoSetting->seo_indexing) === 'noindex')>noindex</option>
                            </select>
                            @error('seo_indexing')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="seo-active" @checked(old('is_active', $seoSetting->is_active ?? true))>
                                <label class="form-check-label" for="seo-active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update SEO' : 'Create SEO' }}</button>
                    <a href="{{ route('admin.seo.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeInput = document.getElementById('seo-page-type');
            const keyInput = document.getElementById('seo-page-key');
            const keyHelp = document.getElementById('seo-page-key-help');

            const helpByType = {
                page: {
                    placeholder: 'about-us',
                    help: 'For a normal page, enter the URL path without the domain, like about-us or contact.'
                },
                route: {
                    placeholder: 'admin.dashboard',
                    help: 'For a Laravel route, enter the route name, like admin.dashboard or admin.forms.index.'
                },
                form: {
                    placeholder: 'contact-us',
                    help: 'For a form page, enter the form slug, like contact-us.'
                },
                gallery: {
                    placeholder: 'events',
                    help: 'For a gallery page, enter the gallery album slug, like events.'
                },
                menu: {
                    placeholder: 'main-menu',
                    help: 'For a menu, enter the menu slug, like main-menu.'
                },
                custom: {
                    placeholder: 'homepage-hero',
                    help: 'For a custom placement, enter any unique key and use that same key when including seo::meta in Blade.'
                }
            };

            function updatePageKeyHelp() {
                const config = helpByType[typeInput.value] || helpByType.page;
                keyInput.placeholder = config.placeholder;
                keyHelp.textContent = config.help;
            }

            if (typeInput && keyInput && keyHelp) {
                typeInput.addEventListener('change', updatePageKeyHelp);
                updatePageKeyHelp();
            }
        });
    </script>
@endpush
