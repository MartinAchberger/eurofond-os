<?php

namespace App\Models;

use App\Enums\RiskLevel;
use App\Enums\RiskStatus;
use Database\Factories\RiskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Risk extends Model
{
    /** @use HasFactory<RiskFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => RiskStatus::Otvorene->value,
    ];

    protected function casts(): array
    {
        return [
            'impact' => RiskLevel::class,
            'likelihood' => RiskLevel::class,
            'status' => RiskStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
