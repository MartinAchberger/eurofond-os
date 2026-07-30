<?php

namespace App\Models;

use App\Enums\GateStatus;
use Database\Factories\GateFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gate extends Model
{
    /** @use HasFactory<GateFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'cakajuca',
    ];

    protected function casts(): array
    {
        return [
            'status' => GateStatus::class,
            'checked_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GateItem::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function pass(User $by): void
    {
        if ($this->items()->where('is_met', false)->exists()) {
            throw new DomainException('Brána má nesplnené podmienky.');
        }

        $this->update([
            'status' => GateStatus::Prejdena,
            'checked_by' => $by->id,
            'checked_at' => now(),
        ]);
    }
}
