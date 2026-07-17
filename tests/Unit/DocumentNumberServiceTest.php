<?php

namespace Tests\Unit;

use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_number_for_a_prefix_today_ends_in_dash_1(): void
    {
        $number = (new DocumentNumberService())->generate('INV-');

        $today = now()->format('Ymd');
        $this->assertSame("INV-{$today}-1", $number);
    }

    public function test_subsequent_calls_same_day_same_prefix_increment(): void
    {
        $service = new DocumentNumberService();

        $first  = $service->generate('INV-');
        $second = $service->generate('INV-');
        $third  = $service->generate('INV-');

        $today = now()->format('Ymd');
        $this->assertSame("INV-{$today}-1", $first);
        $this->assertSame("INV-{$today}-2", $second);
        $this->assertSame("INV-{$today}-3", $third);
    }

    public function test_different_prefixes_have_independent_counters(): void
    {
        $service = new DocumentNumberService();

        $invoice1 = $service->generate('INV-');
        $quote1   = $service->generate('Q-');
        $invoice2 = $service->generate('INV-');

        $today = now()->format('Ymd');
        $this->assertSame("INV-{$today}-1", $invoice1);
        $this->assertSame("Q-{$today}-1", $quote1);
        $this->assertSame("INV-{$today}-2", $invoice2);
    }

    public function test_counter_row_is_persisted_per_prefix_per_day(): void
    {
        (new DocumentNumberService())->generate('INV-');

        $this->assertDatabaseHas('document_counters', [
            'prefix'  => 'INV-',
            'date'    => now()->format('Y-m-d'),
            'counter' => 1,
        ]);
    }
}
