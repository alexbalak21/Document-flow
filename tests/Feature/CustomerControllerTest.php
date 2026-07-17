<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_store_requires_a_name(): void
    {
        $response = $this->post('/customers', ['email' => 'no-name@example.com']);

        $response->assertSessionHasErrors('name');
        $this->assertSame(0, Customer::count());
    }

    public function test_store_creates_a_customer_with_valid_data(): void
    {
        $response = $this->post('/customers', [
            'name'  => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    }

    public function test_store_returns_json_when_called_via_ajax(): void
    {
        $response = $this->postJson('/customers', [
            'name'  => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'Jane Doe']);
    }

    public function test_update_validates_email_format(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->put("/customers/{$customer->id}", [
            'name'  => 'Still Required',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_persists_changes(): void
    {
        $customer = Customer::factory()->create(['name' => 'Old Name']);

        $this->put("/customers/{$customer->id}", ['name' => 'New Name']);

        $this->assertSame('New Name', $customer->fresh()->name);
    }

    public function test_list_endpoint_returns_customers_as_json(): void
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/customers');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    public function test_guest_cannot_access_customer_routes(): void
    {
        auth()->logout();

        $this->get('/customers')->assertRedirect('/login');
        $this->getJson('/api/customers')->assertUnauthorized(); // JSON requests get 401, not a redirect
    }
}
