<?php

namespace App\Models;

use App\Enums\AiSuggestionKind;
use App\Enums\AiSuggestionStatus;
use Database\Factories\AiSuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiSuggestion extends Model
{
    /** @use HasFactory<AiSuggestionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => AiSuggestionStatus::Navrhnute->value,
    ];

    protected function casts(): array
    {
        return [
            'kind' => AiSuggestionKind::class,
            'payload' => 'array',
            'status' => AiSuggestionStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function suggestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
