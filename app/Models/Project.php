<?php

namespace App\Models;

use App\Enums\GateStatus;
use App\Enums\ProjectHealth;
use App\Enums\ProjectPhase;
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
