<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Change price from integer cents to decimal float
            $table->decimal('unit_price', 10, 2)->after('product_unit')->default(0);
            $table->dropColumn('price');

            // page_url is optional
            $table->string('page_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('price')->after('product_unit')->default(0);
            $table->dropColumn('unit_price');
            $table->string('page_url')->nullable(false)->change();
        });
    }
};
