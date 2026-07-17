<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatted_price_always_shows_two_decimals(): void
    {
        $product = Product::factory()->make(['unit_price' => 5]);
        $this->assertSame('5.00', $product->formatted_price);

        $product2 = Product::factory()->make(['unit_price' => 499.999]);
        $this->assertSame('500.00', $product2->formatted_price);

        $product3 = Product::factory()->make(['unit_price' => 19.1]);
        $this->assertSame('19.10', $product3->formatted_price);
    }

    public function test_to_entity_array_casts_unit_price_to_float(): void
    {
        $product = Product::factory()->make([
            'reference' => 'PRD-5001',
            'name' => 'Industrial Thermal Camera',
            'product_unit' => 'unit',
            'unit_price' => '499.99', // stored/retrieved as string from decimal column
        ]);

        $array = $product->toEntityArray();

        $this->assertIsFloat($array['unit_price']);
        $this->assertSame(499.99, $array['unit_price']);
        $this->assertSame('PRD-5001', $array['reference']);
        $this->assertSame('Industrial Thermal Camera', $array['name']);
        $this->assertSame('unit', $array['product_unit']);
    }
}
