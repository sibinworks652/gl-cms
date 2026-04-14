@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Bank Details</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('vendor.settings.bank.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $vendor->bank_name) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $vendor->bank_account_name) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $vendor->bank_account_number) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">IFSC / Routing Code</label>
                            <input type="text" name="bank_ifsc_code" class="form-control" value="{{ old('bank_ifsc_code', $vendor->bank_ifsc_code) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">PayPal Email</label>
                            <input type="email" name="paypal_email" class="form-control" value="{{ old('paypal_email', $vendor->paypal_email) }}">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Bank Details</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
