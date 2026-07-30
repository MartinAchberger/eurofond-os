<?php

namespace App\Models;

use App\Enums\InboxItemStatus;
use App\Enums\InboxSource;
use Database\Factories\InboxItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxItem extends Model
{
    /** @use HasFactory<InboxItemFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => InboxItemStatus::Nove->value,
        'unconfirmed' => true,
    ];

    protected function casts(): array
    {
        return [
            'source' => InboxSource::class,
            'status' => InboxItemStatus::class,
            'suggested_deadline' => 'date',
            'ai_confidence' => 'decimal:2',
            'unconfirmed' => 'boolean',
        ];
    }

    public function suggestedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'suggested_project_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
