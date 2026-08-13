<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Relative path (within the "local" storage disk) to the last
            // generated PDF for this document, e.g. "pdf_documents/12-inv-2026-0042.pdf"
            $table->string('pdf_path')->nullable()->after('html_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};