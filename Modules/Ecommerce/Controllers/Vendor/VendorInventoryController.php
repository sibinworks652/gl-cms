<?php

namespace Modules\Ecommerce\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Inventory;
use Modules\Ecommerce\Services\VendorService;

class VendorInventoryController extends Controller
{
    public function __construct(
        protected VendorService $vendorService,
    ) {
    }

    public function index(Request $request)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        $inventory = Inventory::query()
            ->whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['product', 'variant'])
            ->when($request->status === 'low', fn ($q) => $q->lowStock())
            ->when($request->status === 'out', fn ($q) => $q->outOfStock())
            ->when($request->search, fn ($q, $search) => $q->where('sku', 'like', '%' . $search . '%')
                ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', '%' . $search . '%')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::vendor.inventory.index', [
            'vendor' => $vendor,
            'inventory' => $inventory,
        ]);
    }

    public function show(Inventory $inventory)
    {
        $vendor = $this->vendorService->getVendorOrFail(request()->user());

        if ($inventory->product->vendor_id !== $vendor->id) {
            abort(403, 'You can only view your own inventory.');
        }

        $inventory->load(['product', 'variant', 'logs']);

        return view('ecommerce::vendor.inventory.show', [
            'vendor' => $vendor,
            'inventory' => $inventory,
        ]);
    }

    public function restock(Request $request, Inventory $inventory)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        if ($inventory->product->vendor_id !== $vendor->id) {
            abort(403, 'You can only manage your own inventory.');
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $inventory->restock($data['quantity']);

        return back()->with('success', 'Stock added successfully.');
    }

    public function update(Request $request, Inventory $inventory)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        if ($inventory->product->vendor_id !== $vendor->id) {
            abort(403, 'You can only manage your own inventory.');
        }

        $data = $request->validate([
            'low_stock_threshold' => 'required|integer|min:0',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
        ]);

        $inventory->update($data);

        return back()->with('success', 'Inventory settings updated.');
    }
}