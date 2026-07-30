<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discrepancy_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discrepancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->constrained('document_versions')->cascadeOnDelete();
            $table->text('citation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discrepancy_sources');
    }
};
