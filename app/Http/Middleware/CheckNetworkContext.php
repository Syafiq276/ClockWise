<?php 

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckNetworkContext
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Try to get office IP from database directly (cache may not work in all environments)
            $officeIp = Setting::where('key', 'office_ip')->value('value');
        } catch (\Exception $e) {
            // If database query fails, default to null
            $officeIp = null;
        }

        $userIp = $request->ip();

        if ($officeIp && $userIp === $officeIp) {
            $request->attributes->set('location_type', 'office');
        } else {
            $request->attributes->set('location_type', 'remote');
        }

        return $next($request);
    }
}
