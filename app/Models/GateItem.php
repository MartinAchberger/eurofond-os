<?php

namespace App\Models;

use App\Enums\GateStatus;
use Database\Factories\GateItemFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateItem extends Model
{
    /** @use HasFactory<GateItemFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'is_met' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_met' => 'boolean',
        ];
    }

    public function gate(): BelongsTo
    {
        return $this->belongsTo(Gate::class);
    }

    public function toggle(): void
    {
        if ($this->gate->status === GateStatus::Prejdena) {
            throw new DomainException('Prejdená brána sa už nemení.');
        }

        $this->update(['is_met' => ! $this->is_met]);
    }
}
