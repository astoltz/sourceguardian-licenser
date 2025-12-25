<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;

class LogApiActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('api/*') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'method' => $request->method(),
                'path' => $request->path(),
                'action' => $request->route()->getActionName(),
                'payload' => $request->all(),
                'ip_address' => $request->ip(),
            ]);
        }

        return $response;
    }
}
