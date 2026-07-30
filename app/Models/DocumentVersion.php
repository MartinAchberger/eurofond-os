<?php

namespace App\Models;

use App\Enums\DocumentVersionStatus;
use Database\Factories\DocumentVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DocumentVersion extends Model
{
    /** @use HasFactory<DocumentVersionFactory> */
    use HasFactory;

    use LogsActivity;

    protected $guarded = [];

    protected $attributes = [
        'status' => DocumentVersionStatus::Nepotvrdena->value,
    ];

    // logAll(): pri pridaní citlivého stĺpca do modelu prehodnoť explicitný logOnly([...])
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

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
                ->lockForUpdate()
                ->get()
                ->each->update(['status' => DocumentVersionStatus::Nahradena]);

            $this->update([
                'status' => DocumentVersionStatus::Aktualna,
                'confirmed_by' => $by->id,
                'confirmed_at' => now(),
            ]);
        });
    }
}
