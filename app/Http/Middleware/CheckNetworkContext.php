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
        $officeIp = cache()->remember('office_ip', 60, function () {
            return Setting::where('key', 'office_ip')->value('value');
        });

        $userIp = $request->ip();

        if ($userIp === $officeIp) {
            $request->attributes->set('location_type', 'office');
        } else {
            $request->attributes->set('location_type', 'remote');
        }

        return $next($request);
    }
}
