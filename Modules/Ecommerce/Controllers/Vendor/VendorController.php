<?php

namespace Modules\Ecommerce\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Requests\VendorRegistrationRequest;
use Modules\Ecommerce\Services\CatalogManager;

class VendorController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
    ) {
    }

    public function showRegistrationForm()
    {
        return view('ecommerce::vendor.register');
    }

    public function register(VendorRegistrationRequest $request)
    {
        $user = $request->user() ?? \App\Models\User::where('email', $request->input('email'))->first();
        
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
            ]);
            $user->assignRole('vendor');
        } else {
            $existingVendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->first();
            
            if ($existingVendor) {
                if ($existingVendor->isPending()) {
                    return redirect()->back()->with('info', 'Your vendor application is pending approval.');
                }
                if ($existingVendor->isApproved()) {
                    return redirect()->back()->with('info', 'You are already a vendor. Please login to access dashboard.');
                }
                if ($existingVendor->isRejected()) {
                    $existingVendor->update([
                        'status' => 'pending',
                        'rejection_reason' => null,
                    ]);
                    return redirect()->route('vendor.dashboard')->with('success', 'Your vendor application has been resubmitted.');
                }
            }
            
            $user->assignRole('vendor');
        }

        $data = $request->validated();
        $data['store_name'] = $data['store_name'] ?? $data['name'];
        
        $vendor = $this->catalog->registerVendor($user, $data);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('vendor.dashboard')->with('success', 'Vendor account created! Pending admin approval.');
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        if (!$vendor->isApproved()) {
            return view('ecommerce::vendor.pending', [
                'vendor' => $vendor,
            ]);
        }

        $stats = [
            'total_products' => $vendor->products()->count(),
            'total_orders' => \Modules\Ecommerce\Models\OrderItem::where('vendor_id', $vendor->id)
                ->distinct('order_id')
                ->count('order_id'),
            'pending_orders' => \Modules\Ecommerce\Models\OrderItem::where('vendor_id', $vendor->id)
                ->whereHas('order', fn ($query) => $query->where('status', 'pending'))
                ->distinct('order_id')
                ->count('order_id'),
        ];

        $products = $vendor->products()->latest()->take(5)->get();
        $orders = \Modules\Ecommerce\Models\Order::whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->with(['items' => fn ($query) => $query->where('vendor_id', $vendor->id)])
            ->latest()
            ->take(5)
            ->get();

        return view('ecommerce::vendor.dashboard', [
            'vendor' => $vendor,
            'stats' => $stats,
            'products' => $products,
            'orders' => $orders,
        ]);
    }

    public function products(Request $request)
    {
        $user = $request->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        $products = $vendor->products()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->input('search') . '%'))
            ->when(isset($request->status) && $request->status !== '', fn ($query) => $query->where('status', (bool) $request->boolean('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::vendor.products.index', [
            'vendor' => $vendor,
            'products' => $products,
        ]);
    }

    public function orders(Request $request)
    {
        $user = $request->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        $orders = \Modules\Ecommerce\Models\Order::whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->with(['items' => fn ($query) => $query->where('vendor_id', $vendor->id)])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::vendor.orders.index', [
            'vendor' => $vendor,
            'orders' => $orders,
        ]);
    }

    public function showOrder(\Modules\Ecommerce\Models\Order $order)
    {
        $user = request()->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        $order->load(['items' => fn ($query) => $query->where('vendor_id', $vendor->id)]);

        $vendorOrderItems = $order->items->filter(fn ($item) => $item->vendor_id === $vendor->id);

        if ($vendorOrderItems->isEmpty()) {
            abort(403, 'This order does not contain items from your store.');
        }

        return view('ecommerce::vendor.orders.show', [
            'vendor' => $vendor,
            'order' => $order,
            'vendorOrderItems' => $vendorOrderItems,
        ]);
    }

    public function updateOrderStatus(\Modules\Ecommerce\Requests\OrderStatusRequest $request, \Modules\Ecommerce\Models\Order $order)
    {
        $user = $request->user();
        $vendor = \Modules\Ecommerce\Models\Vendor::where('user_id', $user->id)->firstOrFail();

        $vendorOrderItem = \Modules\Ecommerce\Models\OrderItem::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (!$vendorOrderItem) {
            abort(403, 'This order does not contain items from your store.');
        }

        $order->update(['status' => $request->input('status')]);

        return redirect()->route('vendor.orders.show', $order)->with('success', 'Order status updated.');
    }
}
