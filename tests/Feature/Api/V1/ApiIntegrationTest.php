<?php

namespace Tests\Feature\Api\V1;

use App\Models\ApiClient;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Project::factory()->count(1)->create();
    }

    private function createClientAndToken(array $scopes = []): array
    {
        $client = ApiClient::factory()->create(['is_active' => true]);
        $token = $client->createToken('test-token', $scopes)->plainTextToken;
        return ['client' => $client, 'token' => $token];
    }

    public function test_unauthenticated_request_is_blocked()
    {
        $response = $this->getJson('/api/v1/projects');
        $response->assertStatus(401);
    }

    public function test_request_from_inactive_client_is_blocked()
    {
        $client = ApiClient::factory()->create(['is_active' => false]);
        $token = $client->createToken('inactive-token', ['read:projects'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/projects');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'API client is inactive.']);
    }

    public function test_token_with_correct_scope_can_access_resource()
    {
        ['token' => $token] = $this->createClientAndToken(['read:projects']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/projects');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_token_without_correct_scope_cannot_access_resource()
    {
        ['token' => $token] = $this->createClientAndToken(['read:users']); // Has users scope, not projects

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/projects');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'This action is forbidden. The provided key does not have the required scope: [read:projects]']);
    }

    public function test_token_with_some_but_not_all_scopes_is_forbidden()
    {
        // In a future route that requires multiple scopes, e.g., middleware('auth.apikey:scope1,scope2')
        // This test demonstrates the principle.
        ['token' => $token] = $this->createClientAndToken(['read:projects']);

        // Let's pretend a route required both projects and users
        // For now, we simulate this by checking against a scope the token doesn't have
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/users'); // This endpoint requires 'read:users'

        $response->assertStatus(403);
    }

    public function test_status_endpoint_is_accessible_with_any_valid_key()
    {
        ['token' => $token] = $this->createClientAndToken(); // Token with no specific scopes

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/status');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }
}
