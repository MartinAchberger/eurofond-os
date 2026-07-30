<?php

namespace App\Models;

use App\Enums\DocumentVersionStatus;
use Database\Factories\DocumentVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class DocumentVersion extends Model
{
    /** @use HasFactory<DocumentVersionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'nepotvrdena',
    ];

    protected function casts(): array
    {
        return [
            'status' => DocumentVersionStatus::class,
            'issued_at' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function activate(User $by): void
    {
        DB::transaction(function () use ($by) {
            $this->document->versions()
                ->where('id', '!=', $this->id)
                ->where('status', DocumentVersionStatus::Aktualna)
                ->update(['status' => DocumentVersionStatus::Nahradena]);

            $this->update([
                'status' => DocumentVersionStatus::Aktualna,
                'confirmed_by' => $by->id,
                'confirmed_at' => now(),
            ]);
        });
    }
}
