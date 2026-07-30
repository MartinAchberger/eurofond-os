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
        Schema::create('gate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->boolean('is_met')->default(false);
            $table->text('evidence')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_items');
    }
};
