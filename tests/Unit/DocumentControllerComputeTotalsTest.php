<?php

namespace Tests\Unit;

use App\Http\Controllers\DocumentController;
use App\Services\DocumentNumberService;
use App\Services\EntityResolver;
use Tests\Concerns\InteractsWithPrivateMethods;
use Tests\TestCase;

class DocumentControllerComputeTotalsTest extends TestCase
{
    use InteractsWithPrivateMethods;

    private function controller(): DocumentController
    {
        return new DocumentController(new EntityResolver(), new DocumentNumberService());
    }

    private function computeTotals(string $slug, array $data): array
    {
        return $this->callPrivateMethod($this->controller(), 'computeTotals', [$slug, $data]);
    }

    public function test_it_computes_subtotal_vat_and_total(): void
    {
        $result = $this->computeTotals('invoice', [
            'product_quantity'   => 2,
            'product_unit_price' => 100,
            'vat_rate'           => 20,
        ]);

        $this->assertSame('200.00', $result['subtotal']);
        $this->assertSame('40.00', $result['vat_amount']);
        $this->assertSame('240.00', $result['total']);
    }

    public function test_it_falls_back_to_legacy_quantity_and_unit_price_keys(): void
    {
        // The controller also accepts bare quantity/unit_price for slugs
        // that don't use the product_* entity prefix.
        $result = $this->computeTotals('invoice', [
            'quantity'   => 3,
            'unit_price' => 10,
            'vat_rate'   => 0,
        ]);

        $this->assertSame('30.00', $result['subtotal']);
        $this->assertSame('0.00', $result['vat_amount']);
        $this->assertSame('30.00', $result['total']);
    }

    public function test_no_fx_flag_is_set_when_fx_currency_is_absent(): void
    {
        $result = $this->computeTotals('invoice', [
            'product_quantity' => 1, 'product_unit_price' => 1, 'vat_rate' => 0,
        ]);

        $this->assertSame('1', $result['no_fx']);
        $this->assertArrayNotHasKey('fx_subtotal', $result);
    }

    public function test_no_fx_flag_is_empty_when_fx_currency_is_set(): void
    {
        $result = $this->computeTotals('invoice', [
            'product_quantity' => 1, 'product_unit_price' => 1, 'vat_rate' => 0,
            'fx_currency' => 'USD', 'fx_rate' => 1.1,
        ]);

        $this->assertSame('', $result['no_fx']);
    }

    public function test_fx_conversion_multiplies_every_amount_by_the_rate(): void
    {
        $result = $this->computeTotals('invoice', [
            'product_quantity' => 2, 'product_unit_price' => 100, 'vat_rate' => 20,
            'fx_currency' => 'USD', 'fx_rate' => 1.5,
        ]);

        // Base (EUR) amounts unaffected
        $this->assertSame('200.00', $result['subtotal']);
        $this->assertSame('240.00', $result['total']);

        // FX amounts = base * rate
        $this->assertSame('300.00', $result['fx_subtotal']);   // 200 * 1.5
        $this->assertSame('60.00',  $result['fx_vat']);        // 40  * 1.5
        $this->assertSame('360.00', $result['fx_total']);      // 240 * 1.5
        $this->assertSame('150.00', $result['fx_unit_price']); // 100 * 1.5
        $this->assertSame('$', $result['fx_symbol']);
    }

    public function test_fx_delivery_fee_only_appears_when_delivery_fee_and_fx_are_both_set(): void
    {
        $withDeliveryFee = $this->computeTotals('invoice', [
            'product_quantity' => 1, 'product_unit_price' => 1, 'vat_rate' => 0,
            'fx_currency' => 'USD', 'fx_rate' => 2,
            'delivery_fee' => 10,
        ]);
        $this->assertSame('20.00', $withDeliveryFee['fx_delivery_fee']);

        $withoutDeliveryFee = $this->computeTotals('invoice', [
            'product_quantity' => 1, 'product_unit_price' => 1, 'vat_rate' => 0,
            'fx_currency' => 'USD', 'fx_rate' => 2,
        ]);
        $this->assertArrayNotHasKey('fx_delivery_fee', $withoutDeliveryFee);

        $noFx = $this->computeTotals('invoice', [
            'product_quantity' => 1, 'product_unit_price' => 1, 'vat_rate' => 0,
            'delivery_fee' => 10,
        ]);
        $this->assertArrayNotHasKey('fx_delivery_fee', $noFx);
    }

    public function test_bank_account_is_forced_to_international_when_fx_currency_is_set(): void
    {
        $result = $this->computeTotals('invoice', [
            'product_quantity' => 1, 'product_unit_price' => 1, 'vat_rate' => 0,
            'fx_currency' => 'USD', 'fx_rate' => 1,
            'bank_account' => 'fr', // explicitly chose French account
        ]);

        // Non-EUR invoices always settle via the international account,
        // regardless of what was explicitly selected in the form.
        $this->assertSame('int', $result['bank_account']);
    }

    public function test_bank_account_is_left_untouched_without_fx(): void
    {
        $result = $this->computeTotals('invoice', [
            'product_quantity' => 1, 'product_unit_price' => 1, 'vat_rate' => 0,
            'bank_account' => 'fr',
        ]);

        $this->assertSame('fr', $result['bank_account']);
    }

    public function test_non_financial_slugs_are_returned_unmodified(): void
    {
        $data = ['delivery_number' => 'DN-001', 'notes' => 'hello'];

        $result = $this->computeTotals('tsca-statement', $data);

        $this->assertSame($data, $result);
        $this->assertArrayNotHasKey('subtotal', $result);
    }

    /**
     * @dataProvider currencyProvider
     */
    public function test_currency_symbol_mapping(string $code, string $expectedSymbol): void
    {
        $symbol = $this->callPrivateMethod($this->controller(), 'currencySymbol', [$code]);
        $this->assertSame($expectedSymbol, $symbol);
    }

    public static function currencyProvider(): array
    {
        return [
            'USD'              => ['USD', '$'],
            'GBP'              => ['GBP', '£'],
            'INR'              => ['INR', '₹'],
            'JPY'              => ['JPY', '¥'],
            'CHF'              => ['CHF', 'CHF'],
            'CAD'              => ['CAD', 'CA$'],
            'AUD'              => ['AUD', 'A$'],
            'CNY'              => ['CNY', '¥'],
            'lowercase usd'    => ['usd', '$'],
            'unknown fallback' => ['XYZ', 'XYZ'],
        ];
    }
}
