<?php

use App\Enums\AiSuggestionStatus;
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
        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('kind');
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('suggestable');
            $table->text('input_summary')->nullable();
            $table->json('payload');
            $table->string('status')->default(AiSuggestionStatus::Navrhnute->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_suggestions');
    }
};
