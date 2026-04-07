@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        @php($adminUser = auth('admin')->user())
        @php($sectionPermissions = collect($sections)->mapWithKeys(fn ($section, $sectionKey) => [$sectionKey => 'settings.' . $sectionKey . '.update']))
        @php($editableSectionKey = collect($sections)->keys()->first(fn ($sectionKey) => $adminUser?->can($sectionPermissions[$sectionKey])))
        @php($canUpdateAnySection = $sectionPermissions->contains(fn ($permission) => $adminUser?->can($permission)))
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Settings Overview</h4>
                <p class="text-muted mb-0">Review each settings section first, then open the edit page when you want to update values.</p>
            </div>
            {{-- @if($canUpdateAnySection)
                <a href="{{ route('admin.settings.section.edit', $editableSectionKey) }}" class="btn btn-primary">Edit Settings</a>
            @endif --}}
        </div>

        <div class="row g-4">
            @foreach($sections as $sectionKey => $section)
                @php($canUpdateSection = $adminUser?->can($sectionPermissions[$sectionKey]))
                <div class="col-12" id="section-{{ $sectionKey }}" @if($activeSection && $activeSection !== $sectionKey) style="display:none;" @endif>
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                            <h5 class="card-title mb-1">{{ $section['title'] }}</h5>
                            <p class="text-muted mb-0">{{ $section['description'] }}</p>
                            </div>
                            @if($canUpdateSection)
                                <div>
                                    <a href="{{ route('admin.settings.section.edit', $sectionKey) }}" class="btn text-primary"><iconify-icon icon="solar:pen-new-square-line-duotone" width="20" height="20"/></a>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($section['fields'] as $key => $field)
                                    <div class="col-md-3">
                                        <div class="border rounded p-2 h-100">
                                            <div class="text-muted small mb-1">{{ $field['label'] }}</div>
                                            @php($value = $settings[$key] ?? null)

                                            @if($field['type'] === 'password')
                                                <div class="fw-semibold" title="Password is not visibile">{{ str_repeat('*', strlen($value)) }}</div>
                                            @elseif($field['type'] === 'image')
                                                @if($value)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $value) }}" alt="{{ $field['label'] }}" class="auto-contrast-logo auto-contrast-logo-preview" style="max-height:56px; max-width:100%; object-fit:contain;">
                                                    </div>
                                                    {{-- <div class="small text-muted">{{ $value }}</div> --}}
                                                @else
                                                    <div class="fw-semibold">-</div>
                                                @endif
                                            @elseif($field['type'] === 'url' && $value)
                                                <a href="{{ $value }}" target="_blank" class="fw-semibold">{{ $value }}</a>
                                            @elseif ($field['type'] === 'color' && $value)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div style="width: 20px; height: 20px; background-color: {{ $value }}; border: 1px solid #ccc;"></div>
                                                    <div class="fw-semibold">{{ $value }}</div>
                                                </div>
                                            @elseif($field['type'] === 'boolean')
                                                <span class="badge {{ (string) $value === '1' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ (string) $value === '1' ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            @elseif($field['type'] === 'textarea')
                                                <div class="fw-normal border rounded p-1"
                                                    style="
                                                            overflow-y: auto;
                                                            max-height: 80px;
                                                            background-color: #f8f9fa;
                                                            font-size: 0.875rem;">
                                                    {{ $value ?: '—' }}
                                                </div>
                                            @else
                                                <div class="fw-semibold" style="white-space: pre-wrap;">{{ $value !== null && $value !== '' ? $value : '-' }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
