@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Login History</h4>
                <p class="text-muted mb-0">Review active and historical admin sessions with IP and user agent details.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>IP Address</th>
                                <th>Login</th>
                                <th>Logout</th>
                                <th>Status</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                                <tr>
                                    <td>{{ $history->admin?->name ?? 'Unknown' }}</td>
                                    <td>{{ $history->ip_address ?: '-' }}</td>
                                    <td>{{ $history->login_at?->format('d M Y h:i A') ?: '-' }}</td>
                                    <td>{{ $history->logout_at?->format('d M Y h:i A') ?: 'Still active' }}</td>
                                    <td>
                                        <span class="badge {{ $history->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $history->is_active ? 'Active' : 'Closed' }}
                                        </span>
                                    </td>
                                    <td class="small text-muted" style="max-width: 360px;">{{ \Illuminate\Support\Str::limit($history->user_agent, 120) ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No login history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $histories->links('admin.vendor.pagination') }}
            </div>
        </div>
    </div>
@endsection
