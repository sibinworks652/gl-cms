@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Payouts</h4>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-2">Payout management will be available soon.</p>
            <a href="{{ route('vendor.earnings.index') }}" class="btn btn-primary">Back to Earnings</a>
        </div>
    </div>
</div>
@endsection
