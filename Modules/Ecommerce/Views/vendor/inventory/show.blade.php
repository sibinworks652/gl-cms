@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Inventory Details</h4>
                <a href="{{ route('vendor.inventory.index') }}" class="btn btn-light">Back</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Stock Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vendor.inventory.update', $inventory) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Quantity</label>
                                <input type="number" class="form-control" value="{{ $inventory->quantity }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" name="low_stock_threshold" class="form-control" value="{{ $inventory->low_stock_threshold }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Available</label>
                                <input type="text" class="form-control" value="{{ $inventory->available_quantity }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="track_inventory" class="form-check-input" id="trackInventory" value="1" @checked($inventory->track_inventory)>
                                    <label class="form-check-label" for="trackInventory">Track Inventory</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="allow_backorder" class="form-check-input" id="allowBackorder" value="1" @checked($inventory->allow_backorder)>
                                    <label class="form-check-label" for="allowBackorder">Allow Backorder</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Stock History</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#restockModal">Restock</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Change</th>
                                    <th>After</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventory->logs as $log)
                                    <tr>
                                        <td><span class="badge bg-light text-dark">{{ $log->type }}</span></td>
                                        <td class="{{ $log->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }}
                                        </td>
                                        <td>{{ $log->quantity_after }}</td>
                                        <td>{{ $log->created_at->format('M d, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No history yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Product Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Product</div>
                        <div>{{ $inventory->product?->name }}</div>
                    </div>
                    @if($inventory->variant)
                    <div class="mb-3">
                        <div class="text-muted small">Variant</div>
                        <div>{{ $inventory->variant->label }}</div>
                    </div>
                    @endif
                    <div class="mb-3">
                        <div class="text-muted small">SKU</div>
                        <div><code>{{ $inventory->sku }}</code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('vendor.inventory.restock', $inventory) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Restock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" name="quantity" class="form-control" required min="1" value="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection