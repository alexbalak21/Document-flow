<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            // Controls the order groups appear in the sidebar (e.g. Sales, Shipping, Compliance).
            $table->unsignedInteger('sidebar_group_order')->nullable()->after('sidebar_group');
            // Controls the order of items within their group (e.g. Proposal, Quote, Invoice).
            $table->unsignedInteger('sidebar_order')->nullable()->after('sidebar_group_order');
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn(['sidebar_group_order', 'sidebar_order']);
        });
    }
};
