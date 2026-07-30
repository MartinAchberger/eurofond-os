<?php

use App\Enums\QuestionStatus;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained();
            $table->string('asked_by');
            $table->string('asked_to');
            $table->timestamp('asked_at');
            $table->text('reason')->nullable();
            $table->text('body');
            $table->date('due_at')->nullable();
            $table->string('status')->default(QuestionStatus::Otvorena->value);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
