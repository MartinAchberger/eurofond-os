<?php

namespace App\Models;

use App\Enums\ProjectHealth;
use App\Enums\ProjectPhase;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
