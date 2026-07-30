<?php

namespace App\Models;

use App\Enums\DiscrepancyStatus;
use Database\Factories\DiscrepancyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discrepancy extends Model
{
    /** @use HasFactory<DiscrepancyFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => DiscrepancyStatus::Otvoreny->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => DiscrepancyStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(DiscrepancySource::class);
    }
}
