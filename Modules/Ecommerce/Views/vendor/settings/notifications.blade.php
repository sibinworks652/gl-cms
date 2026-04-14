@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Notification Settings</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-2">Notification preferences will be configurable soon.</p>
            <a href="{{ route('vendor.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
