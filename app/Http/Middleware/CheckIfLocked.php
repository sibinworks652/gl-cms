<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfLocked
{
    public function handle($request, Closure $next)
    {
         if (session('locked') && !$request->routeIs('admin.unlock')) {
        return redirect()->route('admin.unlock');
        }

        return $next($request);
    }
}
