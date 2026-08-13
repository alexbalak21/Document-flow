<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Product;
use App\Services\EntityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_manifest_only_returns_declared_entities(): void
    {
        $resolver = new EntityResolver();

        $resolved = $resolver->forManifest(['entities' => ['customer']]);

        $this->assertArrayHasKey('customer', $resolved);
        $this->assertArrayNotHasKey('product', $resolved);
    }

    public function test_for_manifest_returns_empty_array_when_no_entities_declared(): void
    {
        $resolver = new EntityResolver();

        $this->assertSame([], $resolver->forManifest([]));
    }

    public function test_for_manifest_ignores_unknown_entity_keys(): void
    {
        $resolver = new EntityResolver();

        $resolved = $resolver->forManifest(['entities' => ['customer', 'something_unregistered']]);

        $this->assertArrayHasKey('customer', $resolved);
        $this->assertCount(1, $resolved);
    }

    public function test_save_from_request_creates_a_new_customer_when_none_selected_and_email_is_new(): void
    {
        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['customer']]);

        $data = [
            'customer_name'  => 'Jane Smith',
            'customer_email' => 'jane@example.com',
        ];

        $linkedIds = $resolver->saveFromRequest($entities, $data, []);

        $this->assertDatabaseHas('customers', [
            'name'  => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);
        $this->assertSame(1, Customer::count());
        $this->assertSame(Customer::first()->id, $linkedIds['customer_id']);
    }

    public function test_save_from_request_updates_existing_customer_found_by_email_instead_of_duplicating(): void
    {
        $existing = Customer::create([
            'name'  => 'Old Name',
            'email' => 'jane@example.com',
        ]);

        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['customer']]);

        $data = [
            'customer_name'  => 'New Name',
            'customer_email' => 'jane@example.com', // same email as $existing
        ];

        $linkedIds = $resolver->saveFromRequest($entities, $data, []);

        $this->assertSame(1, Customer::count(), 'A duplicate customer was created instead of updating the existing one.');
        $this->assertSame($existing->id, $linkedIds['customer_id']);
        $this->assertSame('New Name', $existing->fresh()->name);
    }

    public function test_save_from_request_updates_the_explicitly_selected_record_even_if_email_differs(): void
    {
        $existing = Customer::create(['name' => 'Old Name', 'email' => 'old@example.com']);

        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['customer']]);

        $data = [
            'customer_name'  => 'Updated Name',
            'customer_email' => 'brandnew@example.com',
        ];

        $linkedIds = $resolver->saveFromRequest($entities, $data, ['customer_id' => $existing->id]);

        $this->assertSame(1, Customer::count());
        $this->assertSame($existing->id, $linkedIds['customer_id']);
        $this->assertSame('Updated Name', $existing->fresh()->name);
        $this->assertSame('brandnew@example.com', $existing->fresh()->email);
    }

    public function test_save_from_request_never_overwrites_existing_values_with_blanks(): void
    {
        $existing = Customer::create([
            'name'    => 'Jane Smith',
            'email'   => 'jane@example.com',
            'company' => 'Acme Inc',
        ]);

        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['customer']]);

        $data = [
            'customer_name'    => 'Jane Smith',
            'customer_email'   => 'jane@example.com',
            'customer_company' => '', // blank submitted — must NOT wipe out "Acme Inc"
        ];

        $resolver->saveFromRequest($entities, $data, []);

        $this->assertSame('Acme Inc', $existing->fresh()->company);
    }

    public function test_save_from_request_injects_resolved_fields_back_into_data_for_template_rendering(): void
    {
        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['customer']]);

        $data = [
            'customer_name'  => 'Jane Smith',
            'customer_email' => 'jane@example.com',
        ];

        $resolver->saveFromRequest($entities, $data, []);

        $this->assertSame('Jane Smith', $data['customer_name']);
        $this->assertSame('jane@example.com', $data['customer_email']);
    }

    public function test_product_entity_resolves_by_reference_instead_of_email(): void
    {
        $existing = Product::create([
            'reference'  => 'PRD-001',
            'name'       => 'Old Product Name',
            'product_unit' => 'unit',
            'unit_price' => 10,
        ]);

        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['product']]);

        $data = [
            'product_reference' => 'PRD-001',
            'product_name'      => 'New Product Name',
            'product_unit_price'=> 20,
        ];

        $linkedIds = $resolver->saveFromRequest($entities, $data, []);

        $this->assertSame(1, Product::count());
        $this->assertSame($existing->id, $linkedIds['product_id']);
        $this->assertSame('New Product Name', $existing->fresh()->name);
    }

    public function test_no_record_created_when_no_selection_and_absolutely_no_data_present(): void
    {
        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['customer']]);

        // No fields at all — resolver has nothing to match or create against.
        $data = [];

        $linkedIds = $resolver->saveFromRequest($entities, $data, []);

        $this->assertSame(0, Customer::count());
        $this->assertArrayNotHasKey('customer_id', $linkedIds);
    }

    public function test_customer_is_still_created_when_email_is_missing_but_other_fields_present(): void
    {
        // Regression test: "Generate from JSON" payloads often omit customer.email.
        // The customer must still be saved, not silently dropped.
        $resolver = new EntityResolver();
        $entities = $resolver->forManifest(['entities' => ['customer']]);

        $data = ['customer_name' => 'Nameless', 'customer_company' => 'Acme Inc'];

        $linkedIds = $resolver->saveFromRequest($entities, $data, []);

        $this->assertSame(1, Customer::count());
        $this->assertDatabaseHas('customers', [
            'name'    => 'Nameless',
            'company' => 'Acme Inc',
        ]);
        $this->assertSame(Customer::first()->id, $linkedIds['customer_id']);
    }
}