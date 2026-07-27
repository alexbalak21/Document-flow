<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Safety check: warn (via exception) if duplicate reference numbers
        // already exist within the same document type, since the unique
        // index below would otherwise fail to create.
        $duplicates = DB::table('documents')
            ->select('document_type_id', 'reference')
            ->whereNotNull('reference')
            ->where('reference', '!=', '')
            ->groupBy('document_type_id', 'reference')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $list = $duplicates->map(fn($d) => "type #{$d->document_type_id} / \"{$d->reference}\"")->implode(', ');
            throw new \RuntimeException(
                "Cannot add unique constraint: duplicate references already exist ({$list}). " .
                "Resolve these manually (rename or delete duplicates) before running this migration."
            );
        }

        Schema::table('documents', function (Blueprint $table) {
            // Unique per document type — the same number can't repeat within
            // invoices, but "INV-001" as an invoice and "INV-001" as a quote
            // don't conflict with each other. NULL/empty references are not
            // constrained (MySQL unique indexes allow multiple NULLs).
            $table->unique(['document_type_id', 'reference'], 'documents_type_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique('documents_type_reference_unique');
        });
    }
};