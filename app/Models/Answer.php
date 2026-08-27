<?php

namespace App\Models;

use App\Enums\AnswerBindingness;
use App\Enums\QuestionStatus;
use Database\Factories\AnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Answer extends Model
{
    /** @use HasFactory<AnswerFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bindingness' => AnswerBindingness::class,
            'answered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Answer $answer) {
            $answer->question->update(['status' => QuestionStatus::Zodpovedana]);
        });
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }
}
