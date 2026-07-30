<?php

namespace App\Models;

use Database\Factories\GateItemFactory;
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
}
