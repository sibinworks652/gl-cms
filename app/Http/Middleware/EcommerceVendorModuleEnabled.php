<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Settings\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class EcommerceVendorModuleEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = in_array((string) Setting::value('ecommerce_vendor_module_enabled', '1'), ['1', 'true', 'on', 'yes'], true);

        abort_unless($enabled, 404, 'Vendor module is currently disabled.');

        return $next($request);
    }
}
