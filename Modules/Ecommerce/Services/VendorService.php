<?php

namespace Modules\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Ecommerce\Models\Order;
use Modules\Ecommerce\Models\OrderItem;
use Modules\Ecommerce\Models\Vendor;
use Modules\Ecommerce\Models\Inventory;
use Modules\Ecommerce\Support\EcommerceSettings;

class VendorService
{
    public function getVendorFromUser($user): ?Vendor
    {
        return Vendor::where('user_id', $user->id)->first();
    }

    public function getVendorOrFail($user): Vendor
    {
        $vendor = $this->getVendorFromUser($user);
        abort_unless($vendor, 403, 'Vendor account not found.');
        abort_unless($vendor->isApproved(), 403, 'Your vendor account is pending approval.');
        return $vendor;
    }

    public function getDashboardStats(Vendor $vendor): array
    {

        $totalProducts = $vendor->products()->count();

       $orderStats = OrderItem::where('order_items.vendor_id', $vendor->id)
                    ->join('orders', 'order_items.order_id', '=', 'orders.id') // Join the orders table
                    ->selectRaw('COUNT(DISTINCT order_items.order_id) as total_orders')
                    ->selectRaw('COUNT(DISTINCT CASE
                        WHEN orders.status != "delivered" AND orders.status != "cancelled"
                        THEN order_items.order_id
                    END) as active_orders')
                    ->first();
        // dd($orderStats);
        $lowStockCount = Inventory::query()
            ->whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->lowStock()
            ->count();

        $paidItems = OrderItem::where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->with('order')
            ->get();

        $pendingItems = OrderItem::where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'pending'))
            ->with('order')
            ->get();

        $grossEarnings = $this->calculateAllocatedGrandTotal($paidItems);
        $pendingEarnings = $this->calculateAllocatedGrandTotal($pendingItems);

        $commissionRate = (float) ($vendor->commission_rate ?? 0);
        if ($commissionRate <= 0) {
            $commissionRate = EcommerceSettings::vendorDefaultCommissionRate();
        }
        $commissionAmount = round($grossEarnings * ((float) $commissionRate / 100), 2);
        $netEarnings = round(max($grossEarnings - $commissionAmount, 0), 2);

        return [
            'total_products' => $totalProducts,
            'total_orders' => $orderStats->total_orders ?? 0,
            'active_orders' => $orderStats->active_orders ?? 0,
            'low_stock_alerts' => $lowStockCount,
            'total_earnings' => $netEarnings,
            'gross_earnings' => $grossEarnings,
            'pending_earnings' => $pendingEarnings,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'net_earnings' => $netEarnings,
        ];
    }

    public function getEarningsReport(Vendor $vendor, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = OrderItem::where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'));

        if ($startDate) {
            $query->whereHas('order', fn ($q) => $q->whereDate('created_at', '>=', $startDate));
        }
        if ($endDate) {
            $query->whereHas('order', fn ($q) => $q->whereDate('created_at', '<=', $endDate));
        }

        $items = $query->with('order')->get();
        $totalSubtotal = (float) $items->sum('line_total');
        $totalQuantity = (int) $items->sum('quantity');
        $orderCount = (int) $items->pluck('order_id')->unique()->count();
        $totalGrand = $this->calculateAllocatedGrandTotal($items);

        $commissionRate = (float) ($vendor->commission_rate ?? 0);
        if ($commissionRate <= 0) {
            $commissionRate = EcommerceSettings::vendorDefaultCommissionRate();
        }
        $commissionAmount = round($totalGrand * ((float) $commissionRate / 100), 2);
        $netEarnings = round(max($totalGrand - $commissionAmount, 0), 2);

        $ordersByStatus = Order::query()
            ->whereHas('items', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->select('status', DB::raw('COUNT(DISTINCT id) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'total_sales' => $totalGrand,
            'total_subtotal' => round($totalSubtotal, 2),
            'total_grand_total' => $totalGrand,
            'total_quantity' => $totalQuantity,
            'order_count' => $orderCount,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'net_earnings' => $netEarnings,
            'orders_by_status' => $ordersByStatus,
        ];
    }

    protected function calculateAllocatedGrandTotal(\Illuminate\Support\Collection $items): float
    {
        $total = 0.0;

        foreach ($items->groupBy('order_id') as $orderItems) {
            $order = $orderItems->first()?->order;
            if (! $order) {
                continue;
            }

            $orderSubtotal = (float) ($order->subtotal ?? 0);
            $orderGrandTotal = (float) ($order->grand_total ?? 0);
            $vendorSubtotal = (float) $orderItems->sum('line_total');

            if ($orderSubtotal > 0) {
                $total += $orderGrandTotal * ($vendorSubtotal / $orderSubtotal);
            } else {
                $total += $vendorSubtotal;
            }
        }

        return round($total, 2);
    }

    public function getMonthlyEarnings(Vendor $vendor, int $months = 6): Collection
    {
        return OrderItem::where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->selectRaw('MONTH(created_at) as month, SUM(line_total) as total')
            ->whereRaw('created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)', [$months])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function getLowStockInventory(Vendor $vendor): Collection
    {
        return Inventory::query()
            ->whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['product', 'variant'])
            ->lowStock()
            ->get();
    }

    public function getOutOfStockInventory(Vendor $vendor): Collection
    {
        return Inventory::query()
            ->whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['product', 'variant'])
            ->outOfStock()
            ->get();
    }

    public function getVendorOrders(Vendor $vendor, ?string $status = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Order::whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->with(['items' => fn ($query) => $query->where('vendor_id', $vendor->id)])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function updateVendorProfile(Vendor $vendor, array $data): Vendor
    {
        $fillable = ['name', 'email', 'phone', 'description'];

        $updateData = array_filter(
            array_intersect_key($data, array_flip($fillable)),
            fn ($value) => $value !== null
        );

        if (!empty($updateData)) {
            $vendor->update($updateData);
        }

        return $vendor->fresh();
    }

    public function updateVendorSettings(Vendor $vendor, array $data): Vendor
    {
        $settingsFields = ['commission_rate', 'status'];

        $updateData = array_filter(
            array_intersect_key($data, array_flip($settingsFields)),
            fn ($value) => $value !== null
        );

        if (!empty($updateData)) {
            $vendor->update($updateData);
        }

        return $vendor->fresh();
    }
}
