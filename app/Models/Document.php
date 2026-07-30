<?php

namespace App\Models;

use App\Enums\DocumentVersionStatus;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function currentVersion(): ?DocumentVersion
    {
        return $this->versions()->where('status', DocumentVersionStatus::Aktualna)->first();
    }
}
