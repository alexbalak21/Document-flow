<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_assets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // e.g. 'logo'
            $table->string('mime_type');        // e.g. 'image/png'
            $table->string('filename');         // original filename
            $table->longText('data_base64');    // base64-encoded binary
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_assets');
    }
};
