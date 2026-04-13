@extends('admin.layouts.app')

@section('content')
    @php
        $adminUser = auth('admin')->user();

        $formatBytes = function (int|float $bytes): string {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max(0, (float) $bytes);
            $index = 0;

            while ($bytes >= 1024 && $index < count($units) - 1) {
                $bytes /= 1024;
                $index++;
            }

            return ($index === 0 ? (string) (int) $bytes : number_format($bytes, 2)) . ' ' . $units[$index];
        };
    @endphp
    <div class="container-xxl">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Backup</h4>
                    <p class="text-muted mb-0">Create full project file backups with a database dump.</p>
                </div>
                @if($adminUser?->can('backups.create'))
                    <form action="{{ route('admin.backups.store') }}" method="POST" class="d-flex align-items-center gap-3 flex-wrap" id="backup-create-form">
                        @csrf
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="upload-to-google" name="upload_to_google" value="1" @disabled(! $googleAccount)>
                            <label class="form-check-label" for="upload-to-google">Upload to Google Drive</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Backup</button>
                    </form>
                @endif
            </div>

            <div class="card-body">
                {{-- @unless($googleConfigured)
                    <div class="alert alert-warning">
                        {{ $googleConfigurationError ?? 'Google Drive is not configured for this CMS.' }}
                    </div>
                @else
                    <div class="alert alert-info">
                        Add this exact redirect URI in Google Console: <code>{{ $googleRedirectUri }}</code>
                    </div>
                @endunless

                <div class="border rounded p-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1">Google Drive</h5>
                        @if($googleAccount)
                            <p class="text-success mb-0">Connected ({{ $googleAccount->google_email }}). Enable the switch when creating a backup to upload it.</p>
                        @else
                            <p class="text-muted mb-0">Not connected. Normal backups and downloads still work locally.</p>
                        @endif
                    </div>
                    @if($adminUser?->can('backups.create'))
                        @if($googleAccount)
                            <form action="{{ route('admin.backups.google.disconnect') }}" method="POST" data-confirm="Disconnect Google Drive?" data-confirm-button="Yes, disconnect">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Disconnect</button>
                            </form>
                        @elseif($googleConfigured)
                            <a href="{{ route('admin.backups.google.redirect') }}" class="btn btn-primary btn-sm">Connect Google Drive</a>
                        @else
                            <button type="button" class="btn btn-secondary btn-sm" disabled>Connect Google Drive</button>
                        @endif
                    @endif
                </div> --}}

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-centered">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>File</th>
                                <th>Size</th>
                                <th>Storage</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td class="fw-semibold">{{ $backup['filename'] }}</td>
                                    <td>{{ $formatBytes($backup['size']) }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">Local</span>
                                        @if(!empty($backup['google_uploaded']))
                                            <span class="badge bg-success-subtle text-success">Google Drive</span>
                                        @endif
                                    </td>
                                    <td>{{ $backup['created_at'] }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if($adminUser?->can('backups.view'))
                                                <a href="{{ route('admin.backups.download', $backup['filename']) }}" class="btn btn-soft-success btn-sm w-100"><iconify-icon icon="solar:download-minimalistic-broken" width="20" height="20" /></a>
                                            @endif
                                            @if($adminUser?->can('backups.delete'))
                                                <form action="{{ route('admin.backups.destroy', $backup['filename']) }}" method="POST" data-confirm="Delete this backup?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="delete_google" value="1">
                                                    <button type="submit" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-trash-broken" width="20" height="20" /></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No backups found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="backup-loading-overlay" id="backup-loading-overlay" aria-live="polite" aria-hidden="true">
        <div class="backup-loading-card">
            <div class="backup-loading-spinner" aria-hidden="true"></div>
            <h5 class="mb-1">Creating backup</h5>
            <p class="text-muted mb-0">Please wait while the files and database are prepared.</p>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .backup-loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
        }

        .backup-loading-overlay.is-visible {
            display: flex;
        }

        .backup-loading-card {
            width: min(360px, calc(100% - 32px));
            padding: 24px;
            text-align: center;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
        }

        .backup-loading-spinner {
            width: 46px;
            height: 46px;
            margin: 0 auto 16px;
            border: 4px solid rgba(var(--bs-primary-rgb), 0.18);
            border-top-color: var(--bs-primary);
            border-radius: 50%;
            animation: backup-spin 0.8s linear infinite;
        }

        @keyframes backup-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('backup-create-form');
            const overlay = document.getElementById('backup-loading-overlay');

            if (!form || !overlay) {
                return;
            }

            form.addEventListener('submit', function () {
                overlay.classList.add('is-visible');
                overlay.setAttribute('aria-hidden', 'false');

                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                }
            });
        });
    </script>
@endpush
