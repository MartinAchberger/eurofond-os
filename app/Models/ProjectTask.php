<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\ProjectTaskFactory;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ProjectTask extends Model
{
    /** @use HasFactory<ProjectTaskFactory> */
    use HasFactory;

    use LogsActivity;

    protected $guarded = [];

    protected $attributes = [
        'priority' => TaskPriority::Stredna->value,
        'status' => TaskStatus::Otvorena->value,
    ];

    // logAll(): pri pridaní citlivého stĺpca do modelu prehodnoť explicitný logOnly([...])
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'due_at' => 'date',
            'status' => TaskStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeOrderByUrgency(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', [TaskStatus::Hotova->value])
            ->orderByRaw(
                'CASE priority WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 ELSE 3 END',
                [TaskPriority::Blokator->value, TaskPriority::Vysoka->value, TaskPriority::Stredna->value],
            )
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->latest();
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function evidenceDocumentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'evidence_document_version_id');
    }

    public function complete(?DocumentVersion $evidence = null, ?string $evidenceNote = null): void
    {
        if ($evidence === null && blank($evidenceNote)) {
            throw new DomainException('Úlohu nemožno uzavrieť bez dôkazu.');
        }

        $this->update([
            'status' => TaskStatus::Hotova,
            'evidence_document_version_id' => $evidence?->id,
            'evidence_note' => $evidenceNote,
            'completed_at' => now(),
        ]);
    }
}
