<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    use ApiResponser;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        // The AuthenticateApiClient middleware attaches the client to the request.
        $clientName = $request->apiClient?->name ?? 'Unknown Client';

        return $this->successResponse(
            [
                'service_status' => 'online',
                'authenticated_client' => $clientName,
                'timestamp' => now()->toIso8601String(),
            ],
            'API service is running and accessible.'
        );
    }
}
