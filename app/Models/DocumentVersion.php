<?php

namespace App\Models;

use App\Enums\DocumentVersionStatus;
use Database\Factories\DocumentVersionFactory;
use DomainException;
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
            'file_size' => 'integer',
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

    // Re-activating a version currently in Historicka status is a deliberate affordance, not a
    // bug: per the spec workflow, an archived PD (project document) may need to return to being
    // the current version — e.g. a superseding revision is later found invalid. There is no
    // separate "unarchive" action; confirming a historicka version does the same thing as
    // confirming any other non-current version.
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

    public function archive(User $by): void
    {
        if ($this->status === DocumentVersionStatus::Historicka) {
            throw new DomainException('Verzia je už archivovaná.');
        }

        $this->update([
            'status' => DocumentVersionStatus::Historicka,
            'confirmed_by' => $by->id,
            'confirmed_at' => now(),
        ]);
    }
}
