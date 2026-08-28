<?php

namespace App\Models;

use App\Enums\DocumentVersionStatus;
use App\Enums\GateStatus;
use App\Enums\ProjectHealth;
use App\Enums\ProjectPhase;
use App\Enums\QuestionStatus;
use App\Enums\TaskStatus;
use Database\Factories\ProjectFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use LogsActivity;

    protected $guarded = [];

    protected $attributes = [
        'health' => ProjectHealth::Dobre->value,
    ];

    // logAll(): pri pridaní citlivého stĺpca do modelu prehodnoť explicitný logOnly([...])
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    protected function casts(): array
    {
        return [
            'phase' => ProjectPhase::class,
            'health' => ProjectHealth::class,
            'next_deadline' => 'date',
            'budget_total' => 'decimal:2',
            'grant_requested' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function gates(): HasMany
    {
        return $this->hasMany(Gate::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function nextStep(): ?ProjectTask
    {
        return $this->tasks()
            ->where('status', '!=', TaskStatus::Hotova)
            ->orderByUrgency()
            ->first();
    }

    /**
     * @return array{type: string, label: string, detail: string, overdue: bool}|null
     */
    public function waitingOn(): ?array
    {
        $question = $this->questions()
            ->where('status', QuestionStatus::Otvorena)
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->oldest('asked_at')
            ->first();

        if ($question) {
            return [
                'type' => 'odpoved',
                'label' => 'Odpoveď od '.$question->asked_to,
                'detail' => $question->body,
                'overdue' => $question->isOverdue(),
            ];
        }

        $version = DocumentVersion::whereHas('document', fn ($q) => $q->where('project_id', $this->id))
            ->where('status', DocumentVersionStatus::Nepotvrdena)
            ->with('document')
            ->oldest()
            ->first();

        if ($version) {
            return [
                'type' => 'dokument',
                'label' => 'Potvrdenie: '.$version->document->title.' — '.$version->version_label,
                'detail' => $version->document->title,
                'overdue' => false,
            ];
        }

        return null;
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class);
    }

    public function discrepancies(): HasMany
    {
        return $this->hasMany(Discrepancy::class);
    }

    public function advancePhase(User $by): void
    {
        if ($this->phase === ProjectPhase::Udrzatelnost) {
            throw new DomainException('Projekt je v poslednej fáze.');
        }

        $gate = $this->gates()
            ->where('phase', $this->phase->value)
            ->where('status', GateStatus::Prejdena)
            ->first();

        if ($gate === null) {
            throw new DomainException('Projekt nemôže postúpiť: kontrolná brána neprešla.');
        }

        activity()->causedBy($by)->performedOn($this)
            ->withProperties(['from' => $this->phase->value, 'gate_id' => $gate->id])
            ->log('Postup do ďalšej fázy');

        $this->update(['phase' => ProjectPhase::from($this->phase->value + 1)]);
    }
}
