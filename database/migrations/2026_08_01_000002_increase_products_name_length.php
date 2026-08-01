<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Some product names (e.g. imported from lab/analysis PDFs)
            // are long descriptive sentences rather than short titles.
            // TEXT allows up to 65,535 characters vs VARCHAR(255).
            $table->text('name')->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name', 255)->change();
        });
    }
};