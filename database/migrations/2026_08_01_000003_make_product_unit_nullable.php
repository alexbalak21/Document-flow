<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // product_unit is an optional field on the form — the column
            // must allow NULL, otherwise saving a product with no unit
            // (e.g. lab analysis services with no unit of measure) fails.
            $table->string('product_unit')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_unit')->nullable(false)->change();
        });
    }
};