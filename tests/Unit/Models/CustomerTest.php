<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_name_includes_company_when_present(): void
    {
        $customer = Customer::factory()->make(['name' => 'John Doe', 'company' => 'TechNova LLC']);

        $this->assertSame('John Doe — TechNova LLC', $customer->display_name);
    }

    public function test_display_name_falls_back_to_name_only_when_no_company(): void
    {
        $customer = Customer::factory()->make(['name' => 'John Doe', 'company' => null]);

        $this->assertSame('John Doe', $customer->display_name);
    }

    public function test_to_entity_array_returns_expected_shape(): void
    {
        $customer = Customer::factory()->make([
            'name' => 'John Doe', 'company' => 'TechNova LLC', 'department' => 'Procurement',
            'street' => '42 Innovation St', 'city' => 'SF', 'zip' => '94107', 'country' => 'USA',
            'phone' => '+1 415 555 0199', 'email' => 'john@technova.com', 'vat_number' => 'US123456789',
        ]);

        $this->assertEquals([
            'name' => 'John Doe', 'company' => 'TechNova LLC', 'department' => 'Procurement',
            'street' => '42 Innovation St', 'city' => 'SF', 'zip' => '94107', 'country' => 'USA',
            'phone' => '+1 415 555 0199', 'email' => 'john@technova.com', 'vat_number' => 'US123456789',
        ], $customer->toEntityArray());
    }
}
