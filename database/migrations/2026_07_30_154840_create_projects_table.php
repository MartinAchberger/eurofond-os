<?php

use App\Enums\ProjectHealth;
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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('client_id')->constrained();
            $table->string('call_name')->nullable();
            $table->decimal('budget_total', 14, 2)->nullable();
            $table->decimal('grant_requested', 14, 2)->nullable();
            $table->unsignedTinyInteger('phase');
            $table->string('status_label')->nullable();
            $table->string('health')->default(ProjectHealth::Dobre->value);
            $table->date('next_deadline')->nullable();
            $table->text('main_blocker')->nullable();
            $table->text('next_step')->nullable();
            $table->foreignId('owner_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
