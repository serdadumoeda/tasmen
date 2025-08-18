<?php

namespace App\Http\Middleware;

use App\Models\ApiActivityLog;
use Closure;
use Illuminate\Http\Request;

class LogApiActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Record start time to calculate response time
        $startTime = microtime(true);

        // Process the request and get the response
        $response = $next($request);

        $endTime = microtime(true);

        // Log the activity
        ApiActivityLog::create([
            // The `apiClient` attribute is attached to the request by the AuthenticateApiClient middleware
            'api_client_id' => $request->apiClient?->id,
            'ip_address' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => round(($endTime - $startTime) * 1000),
        ]);

        return $response;
    }
}
