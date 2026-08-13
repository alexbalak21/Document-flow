<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Some invoiced services (e.g. recurring lab analysis lines like
            // NovoCIB's FACTURE documents) don't have a catalog reference at
            // all. The column stays unique but must allow NULL so these can
            // be saved without a fabricated reference. Multiple NULLs are
            // permitted by unique indexes in MySQL/PostgreSQL, so uniqueness
            // for products that do have a reference is unaffected.
            $table->string('reference', 80)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('reference', 80)->nullable(false)->change();
        });
    }
};
