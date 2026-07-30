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

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $guarded = [];

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

        $this->update(['phase' => ProjectPhase::from($this->phase->value + 1)]);
    }
}
