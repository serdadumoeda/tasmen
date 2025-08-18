<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the API clients and their tokens.
     */
    public function index()
    {
        // Eager load the tokens relationship
        $clients = ApiClient::with('tokens')->get();

        return view('admin.api_keys.index', compact('clients'));
    }

    /**
     * Display the API usage documentation page.
     */
    public function showDocs()
    {
        return view('admin.api_keys.docs');
    }

    /**
     * Store a newly created API client in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:api_clients,name',
        ]);

        ApiClient::create($request->only('name'));

        return redirect()->route('admin.api_keys.index')
            ->with('success', 'API client created successfully.');
    }

    /**
     * Generate a new token for the specified API client.
     */
    public function generateToken(Request $request, ApiClient $client)
    {
        $request->validate([
            'scopes' => 'nullable|array',
            'scopes.*' => 'string', // Ensure each scope is a string
        ]);

        $scopes = $request->input('scopes', []);
        $tokenName = 'token-' . Str::slug($client->name) . '-' . now()->timestamp;

        // The createToken method returns an object with the plainTextToken
        $newToken = $client->createToken($tokenName, $scopes);

        // The plainTextToken is only available right after creation.
        // We must show it to the user now and store it in the session to display.
        return redirect()->route('admin.api_keys.index')
            ->with('success', 'New API Key generated for ' . $client->name . '.')
            ->with('newApiKey', $newToken->plainTextToken);
    }

    /**
     * Revoke the specified token.
     * Note: Sanctum stores tokens as objects, not just strings.
     * We need the token ID to revoke it.
     */
    public function revokeToken(ApiClient $client, $tokenId)
    {
        $token = $client->tokens()->findOrFail($tokenId);
        $token->delete();

        return redirect()->route('admin.api_keys.index')
            ->with('success', 'API Key revoked successfully.');
    }

    /**
     * Update the status of the specified API client.
     */
    public function update(Request $request, ApiClient $client)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $client->update(['is_active' => $request->is_active]);
        $action = $request->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.api_keys.index')
            ->with('success', "Client {$client->name} has been {$action}.");
    }

    /**
     * Remove the specified API client from storage.
     */
    public function destroy(ApiClient $client)
    {
        // This will also cascade delete the tokens due to Sanctum's model relationships
        $client->delete();

        return redirect()->route('admin.api_keys.index')
            ->with('success', 'API client and all its keys have been deleted.');
    }
}
