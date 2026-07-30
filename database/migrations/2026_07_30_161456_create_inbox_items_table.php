<?php

use App\Enums\InboxItemStatus;
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
        Schema::create('inbox_items', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->text('raw_content');
            $table->string('file_path')->nullable();
            $table->string('status')->default(InboxItemStatus::Nove->value);
            $table->foreignId('suggested_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('suggested_type')->nullable();
            $table->date('suggested_deadline')->nullable();
            $table->decimal('ai_confidence', 3, 2)->nullable();
            $table->boolean('unconfirmed')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_items');
    }
};
