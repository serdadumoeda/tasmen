<?php

namespace Tests\Feature\Api\V1;

use App\Models\ApiClient;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected ApiClient $activeClient;
    protected string $activeToken;
    protected ApiClient $inactiveClient;
    protected string $inactiveToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an active client and its token
        $this->activeClient = ApiClient::factory()->create(['is_active' => true]);
        $this->activeToken = $this->activeClient->createToken('active-token')->plainTextToken;

        // Create an inactive client and its token
        $this->inactiveClient = ApiClient::factory()->create(['is_active' => false]);
        $this->inactiveToken = $this->inactiveClient->createToken('inactive-token')->plainTextToken;

        Project::factory()->count(5)->create();
    }

    public function test_unauthenticated_request_is_blocked()
    {
        $response = $this->getJson('/api/v1/status');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Authentication token not provided.',
                 ]);
    }

    public function test_request_with_invalid_token_is_blocked()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-string',
        ])->getJson('/api/v1/status');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid API Key.']);
    }

    public function test_request_from_inactive_client_is_blocked()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->inactiveToken,
        ])->getJson('/api/v1/status');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'API client is inactive.']);
    }

    public function test_can_access_status_endpoint_with_valid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->activeToken,
        ])->getJson('/api/v1/status');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'API service is running and accessible.',
                 ])
                 ->assertJsonPath('data.authenticated_client', $this->activeClient->name);
    }

    public function test_projects_endpoint_returns_standardized_response()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->activeToken,
        ])->getJson('/api/v1/projects');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Projects retrieved successfully.',
                 ])
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'current_page',
                         'data',
                         'total'
                     ]
                 ])
                 ->assertJsonCount(5, 'data.data');
    }

    public function test_single_project_endpoint_returns_standardized_response()
    {
        $project = Project::first();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->activeToken,
        ])->getJson('/api/v1/projects/' . $project->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Project retrieved successfully.',
                     'data' => [
                         'id' => $project->id
                     ]
                 ]);
    }
}
