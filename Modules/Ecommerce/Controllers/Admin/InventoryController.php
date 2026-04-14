<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\Inventory;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductVariant;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $inventory = Inventory::query()
            ->with(['product', 'variant'])
            ->when($request->status === 'low', fn($q) => $q->lowStock())
            ->when($request->status === 'out', fn($q) => $q->outOfStock())
            ->when($request->search, function ($q, $search) {
                $q->where('sku', 'like', '%' . $search . '%')
                    ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', '%' . $search . '%'));
            })
            ->latest()
            ->paginate(20);

        return view('ecommerce::admin.inventory.index', compact('inventory'));
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['product', 'variant', 'logs.user']);
        return view('ecommerce::admin.inventory.show', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
        ]);

        $data['track_inventory'] = $request->boolean('track_inventory');
        $data['allow_backorder'] = $request->boolean('allow_backorder');

        $inventory->update($data);

        return back()->with('success', 'Inventory updated successfully.');
    }

    public function restock(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $inventory->restock($data['quantity'], $data['notes'] ?? null);

        return back()->with('success', 'Stock added successfully.');
    }
}
