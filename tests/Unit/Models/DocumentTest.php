<?php

namespace Tests\Unit\Models;

use App\Models\Document;
use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_edited_only_when_status_is_draft(): void
    {
        $draft = Document::factory()->status(Document::STATUS_DRAFT)->create();
        $sent  = Document::factory()->status(Document::STATUS_SENT)->create();

        $this->assertTrue($draft->canBeEdited());
        $this->assertFalse($sent->canBeEdited());
    }

    public function test_is_quote_and_is_invoice_check_the_related_document_type_slug(): void
    {
        $quoteType   = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $invoiceType = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $quote   = Document::factory()->create(['document_type_id' => $quoteType->id]);
        $invoice = Document::factory()->create(['document_type_id' => $invoiceType->id]);

        $this->assertTrue($quote->isQuote());
        $this->assertFalse($quote->isInvoice());

        $this->assertTrue($invoice->isInvoice());
        $this->assertFalse($invoice->isQuote());
    }

    public function test_can_be_converted_requires_quote_accepted_status_and_no_existing_invoice(): void
    {
        $quoteType = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);

        $draftQuote = Document::factory()->create([
            'document_type_id' => $quoteType->id,
            'status'            => Document::STATUS_DRAFT,
        ]);
        $this->assertFalse($draftQuote->canBeConverted(), 'A draft quote must not be convertible.');

        $acceptedQuote = Document::factory()->create([
            'document_type_id' => $quoteType->id,
            'status'            => Document::STATUS_ACCEPTED,
        ]);
        $this->assertTrue($acceptedQuote->canBeConverted());

        // Once converted (an invoice points back at it via parent_id), it must no longer be convertible.
        $invoiceType = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        Document::factory()->create([
            'document_type_id' => $invoiceType->id,
            'parent_id'         => $acceptedQuote->id,
        ]);

        $this->assertFalse($acceptedQuote->fresh()->canBeConverted());
    }

    public function test_converted_invoice_relation_finds_the_child_invoice(): void
    {
        $quoteType   = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $invoiceType = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $quote = Document::factory()->create(['document_type_id' => $quoteType->id]);
        $invoice = Document::factory()->create([
            'document_type_id' => $invoiceType->id,
            'parent_id'         => $quote->id,
        ]);

        $this->assertTrue($quote->fresh()->convertedInvoice->is($invoice));
    }

    public function test_a_quote_with_no_converted_invoice_returns_null_relation(): void
    {
        $quoteType = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $quote = Document::factory()->create(['document_type_id' => $quoteType->id]);

        $this->assertNull($quote->convertedInvoice);
    }
}
