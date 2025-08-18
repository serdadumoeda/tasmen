<?php

namespace Tests\Feature\Admin;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superadmin = User::factory()->create(['role' => 'superadmin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
    }

    public function test_superadmin_can_access_api_key_management_page()
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.api-keys.index'));
        $response->assertStatus(200);
        $response->assertSee('API Key Management');
    }

    public function test_superadmin_can_access_api_docs_page()
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.api_keys.docs'));
        $response->assertStatus(200);
        $response->assertSee('Panduan Penggunaan API');
    }

    public function test_regular_user_cannot_access_api_key_management_page()
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.api-keys.index'));
        $response->assertStatus(403); // Or redirect, depending on exception handling
    }

    public function test_superadmin_can_create_a_new_api_client()
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.api-keys.store'), ['name' => 'Test Client']);

        $response->assertRedirect(route('admin.api-keys.index'));
        $this->assertDatabaseHas('api_clients', ['name' => 'Test Client']);
    }

    public function test_superadmin_can_generate_a_token_for_a_client()
    {
        $client = ApiClient::factory()->create();

        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.api-keys.tokens.store', $client));

        $response->assertSessionHas('newApiKey');
        $this->assertCount(1, $client->tokens);
    }

    public function test_superadmin_can_revoke_a_token()
    {
        $client = ApiClient::factory()->create();
        $token = $client->createToken('test-token')->plainTextToken;
        $tokenId = $client->tokens()->first()->id;

        $this->assertCount(1, $client->tokens);

        $response = $this->actingAs($this->superadmin)
            ->delete(route('admin.api-keys.tokens.destroy', ['client' => $client, 'tokenId' => $tokenId]));

        $response->assertRedirect(route('admin.api-keys.index'));
        $this->assertCount(0, $client->fresh()->tokens);
    }

    public function test_superadmin_can_deactivate_and_reactivate_a_client()
    {
        $client = ApiClient::factory()->create(['is_active' => true]);
        $this->assertTrue($client->is_active);

        // Deactivate
        $this->actingAs($this->superadmin)
            ->patch(route('admin.api-keys.status.update', $client), ['is_active' => 0]);

        $this->assertFalse($client->fresh()->is_active);

        // Reactivate
        $this->actingAs($this->superadmin)
            ->patch(route('admin.api-keys.status.update', $client), ['is_active' => 1]);

        $this->assertTrue($client->fresh()->is_active);
    }

    public function test_superadmin_can_delete_a_client()
    {
        $client = ApiClient::factory()->create();
        $this->assertDatabaseHas('api_clients', ['id' => $client->id]);

        $response = $this->actingAs($this->superadmin)
            ->delete(route('admin.api-keys.destroy', $client));

        $response->assertRedirect(route('admin.api-keys.index'));
        $this->assertDatabaseMissing('api_clients', ['id' => $client->id]);
    }
}
