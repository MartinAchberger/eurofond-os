<?php

namespace App\Models;

use App\Enums\QuestionStatus;
use Database\Factories\QuestionFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    use LogsActivity;

    protected $guarded = [];

    protected $attributes = [
        'status' => QuestionStatus::Otvorena->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => QuestionStatus::class,
            'asked_at' => 'datetime',
            'due_at' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function close(): void
    {
        if ($this->status === QuestionStatus::Uzavreta) {
            throw new DomainException('Otázka je už uzavretá.');
        }

        $this->update(['status' => QuestionStatus::Uzavreta]);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
