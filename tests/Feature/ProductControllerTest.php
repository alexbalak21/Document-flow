<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_store_requires_reference_name_and_unit_price(): void
    {
        $response = $this->post('/products', []);

        $response->assertSessionHasErrors(['reference', 'name', 'unit_price']);
        $this->assertSame(0, Product::count());
    }

    public function test_store_rejects_negative_unit_price(): void
    {
        $response = $this->post('/products', [
            'reference'  => 'PRD-1',
            'name'       => 'Widget',
            'unit_price' => -10,
        ]);

        $response->assertSessionHasErrors('unit_price');
    }

    public function test_store_creates_a_product_with_valid_data(): void
    {
        $response = $this->post('/products', [
            'reference'  => 'PRD-1',
            'name'       => 'Widget',
            'unit_price' => 19.99,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['reference' => 'PRD-1', 'name' => 'Widget']);
    }

    public function test_update_persists_changes(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name', 'unit_price' => 10]);

        $this->put("/products/{$product->id}", [
            'reference'  => $product->reference,
            'name'       => 'New Name',
            'unit_price' => 25,
        ]);

        $this->assertSame('New Name', $product->fresh()->name);
        $this->assertEquals(25, $product->fresh()->unit_price);
    }

    public function test_guest_cannot_access_product_routes(): void
    {
        auth()->logout();

        $this->get('/products')->assertRedirect('/login');
    }
}
