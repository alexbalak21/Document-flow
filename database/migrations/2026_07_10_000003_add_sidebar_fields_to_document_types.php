<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('icon')->default('bi-file-earmark-text')->after('active');
            $table->string('sidebar_label')->nullable()->after('icon');
            $table->string('sidebar_group')->nullable()->after('sidebar_label');
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn(['icon', 'sidebar_label', 'sidebar_group']);
        });
    }
};
