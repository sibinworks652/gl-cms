<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Application Pending</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-header">
                        <h4>{{ $vendor->name }}</h4>
                    </div>
                    <div class="card-body">
                        @if($vendor->isPending())
                            <div class="alert alert-warning">
                                <h5>Application Pending</h5>
                                <p>Your vendor application is currently pending approval from the administrator.</p>
                                <p>Please check back later.</p>
                            </div>
                        @elseif($vendor->isRejected())
                            <div class="alert alert-danger">
                                <h5>Application Rejected</h5>
                                <p>Your vendor application has been rejected.</p>
                                @if($vendor->rejection_reason)
                                    <p><strong>Reason:</strong> {{ $vendor->rejection_reason }}</p>
                                @endif
                                <p>You may reapply or contact support.</p>
                            </div>
                            <form method="POST" action="{{ route('vendor.register') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Reapply</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
