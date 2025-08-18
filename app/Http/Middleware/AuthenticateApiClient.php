<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Traits\ApiResponser;

class AuthenticateApiClient
{
    use ApiResponser;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$scopes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->errorResponse('Authentication token not provided.', 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return $this->errorResponse('Invalid API Key.', 401);
        }

        if ($accessToken->tokenable_type !== ApiClient::class || !$accessToken->tokenable) {
             return $this->errorResponse('Invalid API Key. Not a valid client token.', 403);
        }

        $client = $accessToken->tokenable;

        if (!$client->is_active) {
            return $this->errorResponse('API client is inactive.', 403);
        }

        // Check if the token has all the required scopes.
        foreach ($scopes as $scope) {
            if (!$accessToken->can($scope)) {
                return $this->errorResponse(
                    "This action is forbidden. The provided key does not have the required scope: [{$scope}]",
                    403
                );
            }
        }

        // Update last used timestamp
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Set the authenticated client on the request for use in controllers
        $request->attributes->add(['apiClient' => $client]);

        return $next($request);
    }
}
