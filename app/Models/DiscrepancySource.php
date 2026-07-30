<?php

namespace App\Models;

use Database\Factories\DiscrepancySourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscrepancySource extends Model
{
    /** @use HasFactory<DiscrepancySourceFactory> */
    use HasFactory;

    protected $guarded = [];

    public function discrepancy(): BelongsTo
    {
        return $this->belongsTo(Discrepancy::class);
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }
}
