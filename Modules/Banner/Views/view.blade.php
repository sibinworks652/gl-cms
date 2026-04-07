@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">{{ $slide->title }}</h4>
                            <p class="text-muted mb-0">Banner slide details and publishing window.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.banners.edit', $slide) }}" class="btn btn-primary btn-sm">Edit</a>
                            <a href="{{ route('admin.banners.index') }}" class="btn btn-light btn-sm">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="border rounded p-3 h-100">
                                    <h5 class="mb-3">Media Preview</h5>
                                    @if($slide->media_type === 'image' && $slide->image_path)
                                    <img src="{{ asset('storage/' . $slide->image_path) }}" alt="{{ $slide->title }}"
                                        class="img-fluid rounded">
                                    @elseif($slide->media_type === 'video' && $slide->video_url)
                                    <div class="ratio ratio-16x9">
                                        <iframe src="{{ $slide->video_url }}" title="{{ $slide->title }}"
                                            allowfullscreen></iframe>
                                    </div>
                                    @else
                                    <div class="text-muted">No media configured.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="border rounded p-3 h-100">
                                    <h5 class="mb-3">Slide Details</h5>

                                    <table class="table table-sm table-bordered align-middle mb-4">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Subtitle</th>
                                                <td>{{ $slide->subtitle ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Description</th>
                                                <td>{{ $slide->description ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Media Type</th>
                                                <td>{{ ucfirst($slide->media_type) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <span
                                                        class="badge {{ $slide->is_active ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $slide->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Order</th>
                                                <td>{{ $slide->sort_order }}</td>
                                            </tr>
                                            <tr>
                                                <th>Start</th>
                                                <td>{{ $slide->starts_at?->format('d M Y H:i') ?: 'Immediate' }}</td>
                                            </tr>
                                            <tr>
                                                <th>End</th>
                                                <td>{{ $slide->ends_at?->format('d M Y H:i') ?: 'No end date' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <h5 class="mb-3">Button / CTA</h5>

                                    <table class="table table-sm table-bordered align-middle">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Label</th>
                                                <td>{{ $slide->button_label ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Link Type</th>
                                                <td>{{ ucfirst($slide->button_link_type) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Stored Link</th>
                                                <td>{{ $slide->button_link ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Resolved Link</th>
                                                <td>
                                                    @if($slide->resolved_button_link)
                                                    <a href="{{ $slide->resolved_button_link }}" target="_blank">
                                                        {{ $slide->resolved_button_link }}
                                                    </a>
                                                    @else
                                                    N/A
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Open In New Tab</th>
                                                <td>{{ $slide->open_in_new_tab ? 'Yes' : 'No' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
