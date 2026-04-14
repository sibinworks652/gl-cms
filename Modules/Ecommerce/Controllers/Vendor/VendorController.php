<?php

namespace Modules\Ecommerce\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Ecommerce\Models\Vendor;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\Order;
use Modules\Ecommerce\Models\OrderItem;
use Modules\Ecommerce\Requests\VendorRegistrationRequest;
use Modules\Ecommerce\Services\CatalogManager;
use Modules\Ecommerce\Services\VendorService;

class VendorController extends Controller
{
    public function __construct(
        protected CatalogManager $catalog,
        protected VendorService $vendorService,
    ) {
    }

    public function showLoginForm()
    {
        return view('ecommerce::vendor.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user || !Auth::guard('web')->attempt($credentials)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            Auth::guard('web')->logout();
            return back()->withErrors(['email' => 'No vendor account found for this email.'])->onlyInput('email');
        }

        if (!$vendor->isApproved()) {
            Auth::guard('web')->logout();
            $request->session()->put('vendor_id', $vendor->id);
            return redirect()->route('vendor.pending');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('vendor.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }

    public function showRegistrationForm()
    {
        return view('ecommerce::vendor.register');
    }

    public function pending()
    {
        $vendorId = session('vendor_id');
        if (!$vendorId) {
            return redirect()->route('vendor.login');
        }

        $vendor = Vendor::findOrFail($vendorId);

        return view('ecommerce::vendor.pending', ['vendor' => $vendor]);
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
        } else {
            $existingVendor = Vendor::where('user_id', $user->id)->first();

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
                    $request->session()->put('vendor_id', $existingVendor->id);
                    return redirect()->route('vendor.pending')->with('success', 'Your vendor application has been resubmitted.');
                }
            }

        }

        $data = $request->validated();
        $data['store_name'] = $data['store_name'] ?? $data['name'];

        $vendor = $this->catalog->registerVendor($user, $data);

        Auth::guard('web')->login($user);

        if ($vendor->isApproved()) {
            return redirect()->route('vendor.dashboard')->with('success', 'Vendor account approved successfully.');
        }

        return redirect()->route('vendor.pending')->with('success', 'Vendor account created! Pending admin approval.');
    }

    public function dashboard(Request $request)
    {
        // dd($request->user());
        $user = $request->user();
        $vendor = $this->vendorService->getVendorOrFail($user);

        $stats = $this->vendorService->getDashboardStats($vendor);
        // dd($stats);
        $products = $vendor->products()->latest()->take(5)->get();
        $orders = Order::whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
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
        $vendor = $this->vendorService->getVendorOrFail($request->user());

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
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        $orders = Order::whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
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

    public function showOrder(Order $order)
    {
        $vendor = $this->vendorService->getVendorOrFail(request()->user());

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

    public function updateOrderStatus(\Modules\Ecommerce\Requests\OrderStatusRequest $request, Order $order)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        $vendorOrderItem = OrderItem::where('order_id', $order->id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (!$vendorOrderItem) {
            abort(403, 'This order does not contain items from your store.');
        }

        $order->update(['status' => $request->input('status')]);

        return redirect()->route('vendor.orders.show', $order)->with('success', 'Order status updated.');
    }
}
