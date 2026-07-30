<?php

namespace App\Models;

use App\Enums\GateStatus;
use Database\Factories\GateFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Gate extends Model
{
    /** @use HasFactory<GateFactory> */
    use HasFactory;

    use LogsActivity;

    protected $guarded = [];

    protected $attributes = [
        'status' => GateStatus::Cakajuca->value,
    ];

    // logAll(): pri pridaní citlivého stĺpca do modelu prehodnoť explicitný logOnly([...])
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

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
