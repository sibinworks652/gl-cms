<?php

namespace Modules\Ecommerce\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Models\OrderItem;
use Modules\Ecommerce\Services\VendorService;

class VendorEarningsController extends Controller
{
    public function __construct(
        protected VendorService $vendorService,
    ) {
    }

    public function index(Request $request)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $stats = $this->vendorService->getDashboardStats($vendor);
        $report = $this->vendorService->getEarningsReport($vendor, $startDate, $endDate);
        $monthlyEarnings = $this->vendorService->getMonthlyEarnings($vendor, 6);

        return view('ecommerce::vendor.earnings.index', [
            'vendor' => $vendor,
            'stats' => $stats,
            'report' => $report,
            'monthlyEarnings' => $monthlyEarnings,
        ]);
    }

    public function transactions(Request $request)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        $transactions = OrderItem::where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->with(['order'])
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('start_date')))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('end_date')))
            ->when($request->filled('status'), fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('status', $request->input('status'))))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('ecommerce::vendor.earnings.transactions', [
            'vendor' => $vendor,
            'transactions' => $transactions,
        ]);
    }

    public function payouts(Request $request)
    {
        $vendor = $this->vendorService->getVendorOrFail($request->user());

        return view('ecommerce::vendor.earnings.payouts', [
            'vendor' => $vendor,
        ]);
    }
}