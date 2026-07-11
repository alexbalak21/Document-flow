<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_counters', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20);   // e.g. "INV-", "Q-", "FACT-", "DEV-"
            $table->date('date');            // e.g. 2026-07-11
            $table->unsignedInteger('counter')->default(0);
            $table->timestamps();

            // One row per prefix+day; used as the lock target
            $table->unique(['prefix', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_counters');
    }
};
