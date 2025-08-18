<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->errorResponse('Authentication token not provided.', 401);
        }

        // Sanctum stores the plain text token only once. We need to find the token record
        // by the token string itself. Sanctum doesn't provide a direct way to do this
        // for security reasons (to avoid timing attacks).
        // The standard way is to use the `auth:sanctum` guard which handles it.
        // Let's try to authenticate using the sanctum guard but for our api_client provider.

        // We need to configure a custom guard and provider in config/auth.php
        // to make Sanctum work with ApiClient model. This is getting complex.

        // Let's try a simpler approach first. We can find the token if we have its ID.
        // But the token is just a string.
        // Let's look at how Sanctum does it. It hashes the token.

        // The token in the database is hashed. Sanctum can find a token instance from a plain-text token.
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return $this->errorResponse('Invalid API Key.', 401);
        }

        // Check if the tokenable model is an ApiClient
        if ($accessToken->tokenable_type !== ApiClient::class) {
             return $this->errorResponse('Invalid API Key. Not a client token.', 403);
        }

        $client = $accessToken->tokenable;

        if (!$client) {
            return $this->errorResponse('API client not found.', 401);
        }

        // Check if the client is active
        if (!$client->is_active) {
            return $this->errorResponse('API client is inactive.', 403);
        }

        // Check token scopes/abilities if needed in the future
        // e.g., if (!$accessToken->can('read:users')) { ... }

        // Update last used timestamp
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Set the authenticated client on the request for use in controllers
        $request->attributes->add(['apiClient' => $client]);

        return $next($request);
    }
}
