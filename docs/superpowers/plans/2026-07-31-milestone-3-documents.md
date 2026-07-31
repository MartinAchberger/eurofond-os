# EUROFOND OS — Milestone 3: Documents UX Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Documents become fully manageable from the UI — create a document, upload file versions, confirm a version as current (supersede), archive a version, and download files — all enforcing the M1 domain rules.

**Architecture:** Extends the existing `DocumentsTab` Livewire component in the project workspace with two child components (create document, upload version) and per-version actions. Files live on the local `private` disk under `documents/{project_id}/`; downloads go through an authenticated controller. One new domain method: `DocumentVersion::archive(User $by)` → status `historicka` (covers the real-world case from the spec: „stará PD a PEH sa archivujú, čaká sa na novú PD" — an active version may be archived, leaving the document temporarily without a current version).

**Tech Stack:** Laravel 13, Livewire 3 (`WithFileUploads`), local `private` disk, Pest 5 (`Storage::fake`, `UploadedFile::fake`). No new packages.

## Global Constraints

- All user-facing strings Slovak with diacritics. Product name EUROFOND OS.
- Domain rules in model methods, never in UI: activation only via `DocumentVersion::activate()`, archiving only via the new `DocumentVersion::archive()`; UI catches `DomainException` into an `$error` property (pattern from `TasksTab`/`PhasesTab`).
- Files are never deleted; no delete UI anywhere. Old versions are archived, not removed.
- Uploads: max 20 MB; accepted extensions pdf, doc, docx, xls, xlsx, png, jpg, jpeg. Stored on disk `local` under `documents/{project_id}/` with a hashed name; original client filename preserved in a new `original_filename` column.
- Downloads only for authenticated users, only via the download route (files are on the private disk, never web-accessible directly).
- Livewire mutations validate all input; `wire:key` on any row loop that injects panels.
- Chip/status visual language follows M2 (`DocumentVersionStatus`: aktualna=green „Aktuálna", nahradena=gray „Nahradená", nepotvrdena=amber „Nepotvrdená", historicka=gray „Historická").
- Tests: Pest + `Livewire::test()`; file tests use `Storage::fake('local')`.

## Component & File Map

| Unit | Path |
|---|---|
| Domain: archive method + file columns | `app/Models/DocumentVersion.php`, migration `add_file_columns_to_document_versions` |
| Download controller | `app/Http/Controllers/DocumentVersionDownloadController.php`, route `dokumenty.stiahnut` |
| Create document UI | `app/Livewire/Projects/CreateDocumentForm.php` (+ view) |
| Upload version UI | `app/Livewire/Projects/UploadVersionForm.php` (+ view) |
| Version actions | extend `app/Livewire/Projects/DocumentsTab.php` (+ view) |

---

### Task 1: Domain — `archive()` + file metadata columns

**Files:**
- Create: migration `database/migrations/<ts>_add_file_columns_to_document_versions_table.php`
- Modify: `app/Models/DocumentVersion.php`
- Test: `tests/Feature/DocumentArchiveTest.php`

**Interfaces:**
- Produces: `document_versions` gains `original_filename` (string, nullable) and `file_size` (unsignedBigInteger, nullable) columns (added via `Schema::table`, both after `file_path`). `DocumentVersion::archive(User $by): void` — sets status `historicka` + `confirmed_by`/`confirmed_at` (records who archived); throws `DomainException('Verzia je už archivovaná.')` if status is already `historicka`. Archiving an `aktualna` version is allowed (document may end with no current version). Activity log records the change automatically (`logAll`).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/DocumentArchiveTest.php
use App\Enums\DocumentVersionStatus;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('archive marks version historicka and records who archived', function () {
    $user = User::factory()->create();
    $version = DocumentVersion::factory()->create();

    $version->archive($user);

    expect($version->fresh()->status)->toBe(DocumentVersionStatus::Historicka)
        ->and($version->fresh()->confirmed_by)->toBe($user->id);
});

test('an active version can be archived, leaving the document without a current version', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();
    $version = DocumentVersion::factory()->for($document)->create();
    $version->activate($user);

    $version->archive($user);

    expect($version->fresh()->status)->toBe(DocumentVersionStatus::Historicka)
        ->and($document->currentVersion())->toBeNull();
});

test('archiving an already archived version throws', function () {
    $user = User::factory()->create();
    $version = DocumentVersion::factory()->create();
    $version->archive($user);
    $version->fresh()->archive($user);
})->throws(DomainException::class, 'Verzia je už archivovaná.');

test('file metadata columns are fillable', function () {
    $version = DocumentVersion::factory()->create([
        'original_filename' => 'Rozpocet_v3.xlsx',
        'file_size' => 123456,
    ]);
    expect($version->original_filename)->toBe('Rozpocet_v3.xlsx')
        ->and($version->file_size)->toBe(123456);
});
```

- [ ] **Step 2: Run test to verify it fails** — `./vendor/bin/pest tests/Feature/DocumentArchiveTest.php` — Expected: FAIL (no columns, no method).
- [ ] **Step 3: Implement**

Migration: `Schema::table('document_versions', fn (Blueprint $t) => $t->string('original_filename')->nullable()->after('file_path') . $t->unsignedBigInteger('file_size')->nullable()->after('original_filename'))` (two statements inside the closure; add matching `down()` dropping both).

```php
// app/Models/DocumentVersion.php (add)
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
```

(`use DomainException;` import; `file_size` needs an `'integer'` cast.)

- [ ] **Step 4: Run test to verify it passes** — Expected: PASS; run full suite.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: document version archive rule and file metadata columns"`

---

### Task 2: Authenticated download

**Files:**
- Create: `app/Http/Controllers/DocumentVersionDownloadController.php`
- Modify: `routes/web.php` (inside the `auth` group)
- Test: `tests/Feature/DocumentDownloadTest.php`

**Interfaces:**
- Consumes: `original_filename` from Task 1.
- Produces: route `GET /dokumenty/verzie/{version}/stiahnut` named `dokumenty.stiahnut` → invokable controller returning `Storage::disk('local')->download($version->file_path, $version->original_filename ?? basename($version->file_path))`; 404 when `file_path` is null or the file is missing; guests redirected to login.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/DocumentDownloadTest.php
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guest cannot download', function () {
    $version = DocumentVersion::factory()->create(['file_path' => 'documents/1/x.pdf']);
    $this->get(route('dokumenty.stiahnut', $version))->assertRedirect(route('login'));
});

test('authenticated user downloads with original filename', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documents/1/abc.pdf', 'obsah');
    $version = DocumentVersion::factory()->create([
        'file_path' => 'documents/1/abc.pdf',
        'original_filename' => 'Projektová dokumentácia v1.2.pdf',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk()
        ->assertDownload('Projektová dokumentácia v1.2.pdf');
});

test('version without file returns 404', function () {
    $version = DocumentVersion::factory()->create(['file_path' => null]);
    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertNotFound();
});
```

- [ ] **Step 2: Run to verify FAIL** (route missing).
- [ ] **Step 3: Implement**

```php
// app/Http/Controllers/DocumentVersionDownloadController.php
namespace App\Http\Controllers;

use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentVersionDownloadController extends Controller
{
    public function __invoke(DocumentVersion $version): StreamedResponse
    {
        abort_if($version->file_path === null, 404);
        abort_unless(Storage::disk('local')->exists($version->file_path), 404);

        return Storage::disk('local')->download(
            $version->file_path,
            $version->original_filename ?? basename($version->file_path),
        );
    }
}
```

Route (in the `auth` group): `Route::get('/dokumenty/verzie/{version}/stiahnut', DocumentVersionDownloadController::class)->name('dokumenty.stiahnut');`

- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: authenticated document version download"`

---

### Task 3: Create document form

**Files:**
- Create: `app/Livewire/Projects/CreateDocumentForm.php` + `resources/views/livewire/projects/create-document-form.blade.php`
- Modify: `resources/views/livewire/projects/documents-tab.blade.php` (header row gets „Nový dokument" button toggling the inline form; component embedded with `:project="$project"`)
- Test: `tests/Feature/Projects/CreateDocumentFormTest.php`

**Interfaces:**
- Consumes: `Document`, `DocumentType` models.
- Produces: `CreateDocumentForm` — props `public Project $project`, `bool $open = false`, `string $title = ''`, `?int $documentTypeId = null`; actions `toggle()`, `save()` (validates `title` required max 255, `documentTypeId` required exists:document_types,id; creates `Document` for the project; resets + dispatches `document-created`). `DocumentsTab` listens: `#[On('document-created')] public function refreshDocuments()` → `unset($this->documents)` (bust the computed cache; use whatever the computed property is actually named in DocumentsTab — check the file; it is `documents`).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/CreateDocumentFormTest.php
use App\Livewire\Projects\CreateDocumentForm;
use App\Models\{Document, DocumentType, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('validates and creates a document for the project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $type = DocumentType::factory()->create(['name' => 'Rozpočet']);

    Livewire::test(CreateDocumentForm::class, ['project' => $project])
        ->call('save')
        ->assertHasErrors(['title' => 'required']);

    Livewire::test(CreateDocumentForm::class, ['project' => $project])
        ->set('title', 'Rozpočet stavby')
        ->set('documentTypeId', $type->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('document-created');

    expect(Document::count())->toBe(1)
        ->and(Document::first()->project_id)->toBe($project->id)
        ->and(Document::first()->title)->toBe('Rozpočet stavby');
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement** per Interfaces. View: inline card (shown when `$open`) with fields Názov dokumentu (input), Typ dokumentu (select from `DocumentType::orderBy('name')`), buttons Zrušiť (`toggle`)/Vytvoriť dokument. `save()` also calls `$this->resetValidation()` after reset (M2 lesson). Empty-string select coerces to null on `?int` (established Livewire behavior).
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: create document form in workspace"`

---

### Task 4: Upload version form

**Files:**
- Create: `app/Livewire/Projects/UploadVersionForm.php` + `resources/views/livewire/projects/upload-version-form.blade.php`
- Modify: `resources/views/livewire/projects/documents-tab.blade.php` (each document card gets „Nahrať novú verziu" button revealing the form for that document; embed one `UploadVersionForm` per document with `:document="$doc"` and `wire:key="upload-{{ $doc->id }}"`)
- Test: `tests/Feature/Projects/UploadVersionFormTest.php`

**Interfaces:**
- Consumes: `original_filename`/`file_size` columns (Task 1).
- Produces: `UploadVersionForm` — `use WithFileUploads;` props `public Document $document`, `bool $open = false`, `$file = null`, `string $versionLabel = ''`, `?string $issuedAt = null`, `string $author = ''`; actions `toggle()`, `save()`: validates `file` required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg, `versionLabel` required max 50, `issuedAt` nullable date, `author` nullable max 255; stores via `$this->file->store('documents/'.$this->document->project_id, 'local')`; creates `DocumentVersion` (document_id, version_label, file_path, original_filename = `$this->file->getClientOriginalName()`, file_size = `$this->file->getSize()`, issued_at ?: null, author ?: null, uploaded_by = auth id) — status defaults to `nepotvrdena`; resets + `resetValidation()` + dispatches `version-uploaded`. `DocumentsTab` also listens `#[On('version-uploaded')]` → same cache bust.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/UploadVersionFormTest.php
use App\Enums\DocumentVersionStatus;
use App\Livewire\Projects\UploadVersionForm;
use App\Models\{Document, DocumentVersion, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('uploads a file and creates an unconfirmed version', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $this->actingAs($user);
    $document = Document::factory()->create();

    Livewire::test(UploadVersionForm::class, ['document' => $document])
        ->set('file', UploadedFile::fake()->create('Rozpocet_v3.xlsx', 120, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))
        ->set('versionLabel', 'v3.0')
        ->set('issuedAt', today()->toDateString())
        ->set('author', 'Ing. Novák')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('version-uploaded');

    $version = DocumentVersion::first();
    expect($version->status)->toBe(DocumentVersionStatus::Nepotvrdena)
        ->and($version->original_filename)->toBe('Rozpocet_v3.xlsx')
        ->and($version->uploaded_by)->toBe($user->id)
        ->and($version->file_path)->toStartWith('documents/'.$document->project_id.'/');
    Storage::disk('local')->assertExists($version->file_path);
});

test('rejects disallowed file types and missing label', function () {
    Storage::fake('local');
    $this->actingAs(User::factory()->create());
    $document = Document::factory()->create();

    Livewire::test(UploadVersionForm::class, ['document' => $document])
        ->set('file', UploadedFile::fake()->create('script.sh', 10))
        ->call('save')
        ->assertHasErrors(['file', 'versionLabel']);
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement** per Interfaces. View (shown when `$open`): fields Súbor (input type=file + hint „PDF, Word, Excel alebo obrázok, max. 20 MB", `wire:loading` indicator „Nahráva sa…"), Označenie verzie (napr. v1.0), Dátum vydania (date), Autor; buttons Zrušiť/Nahrať verziu.
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: upload document version with file"`

---

### Task 5: Version actions in DocumentsTab (confirm, archive, download)

**Files:**
- Modify: `app/Livewire/Projects/DocumentsTab.php`, `resources/views/livewire/projects/documents-tab.blade.php`
- Test: `tests/Feature/Projects/DocumentsTabActionsTest.php`

**Interfaces:**
- Consumes: `DocumentVersion::activate()` (M1), `archive()` (Task 1), route `dokumenty.stiahnut` (Task 2), events `document-created`/`version-uploaded` (Tasks 3-4).
- Produces: `DocumentsTab` gains `?string $error = null`; actions `confirmVersion(int $versionId)` and `archiveVersion(int $versionId)` — both resolve the version scoped to the project (`DocumentVersion::whereHas('document', fn ($q) => $q->where('project_id', $this->project->id))->findOrFail($id)`), call the domain method with `auth()->user()` in try/catch `DomainException` → `$error`, then bust the computed cache. Listeners `#[On('document-created')]` and `#[On('version-uploaded')]` refresh the list. View per version row: „Stiahnuť" link (only when `file_path`, `href="{{ route('dokumenty.stiahnut', $version) }}"`), „Potvrdiť ako aktuálnu" button (only when status is `nepotvrdena` or `historicka`... show for any non-`aktualna` status), „Archivovať" button (only when status is not `historicka`); red error alert above the list bound to `$error`; buttons carry `wire:key`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/DocumentsTabActionsTest.php
use App\Enums\DocumentVersionStatus;
use App\Livewire\Projects\DocumentsTab;
use App\Models\{Document, DocumentVersion, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('confirm version activates it and supersedes the old one', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    $v1 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v1.0']);
    $v2 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v2.0']);
    $v1->activate($user);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('confirmVersion', $v2->id)
        ->assertSet('error', null);

    expect($v2->fresh()->status)->toBe(DocumentVersionStatus::Aktualna)
        ->and($v1->fresh()->status)->toBe(DocumentVersionStatus::Nahradena);
});

test('archive version from UI', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    $version = DocumentVersion::factory()->for($document)->create();

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('archiveVersion', $version->id)
        ->assertSet('error', null);

    expect($version->fresh()->status)->toBe(DocumentVersionStatus::Historicka);
});

test('archiving an archived version surfaces the domain error', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    $version = DocumentVersion::factory()->for($document)->create();
    $version->archive($user);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('archiveVersion', $version->id)
        ->assertSet('error', 'Verzia je už archivovaná.');
});

test('cannot act on another projects version', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreign = DocumentVersion::factory()->create(); // different project via factory chain

    expect(fn () => Livewire::test(DocumentsTab::class, ['project' => $project])
        ->call('confirmVersion', $foreign->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);

    expect($foreign->fresh()->status)->not->toBe(DocumentVersionStatus::Aktualna);
});

test('download link renders only for versions with a file', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();
    DocumentVersion::factory()->for($document)->create(['file_path' => 'documents/1/a.pdf', 'version_label' => 'v9.9']);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->assertSee('Stiahnuť');
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement** per Interfaces (follow the `TasksTab` action/error pattern exactly). Wire the „Nový dokument" button + `CreateDocumentForm` embed (Task 3) and per-document `UploadVersionForm` embeds (Task 4) into the tab view if not already done in those tasks — final state: header (Dokumenty + Nový dokument button + error alert), per document card: title, type, versions table (label, status chip, dátum vydania, autor, potvrdil, veľkosť `number_format($v->file_size / 1024, 0, ',', ' ') . ' kB'` when set, actions).
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Visual check** — `php artisan migrate:fresh --seed`; open http://eurofond-os.test → PRJ-001 → Dokumenty; create a document, upload a version, confirm it, archive it (manual sanity, seeded versions have no files so Stiahnuť hidden for them — correct).
- [ ] **Step 6: Commit** — `git add -A && git commit -m "feat: version actions — confirm, archive, download"`

---

### Task 6: Global Dokumenty page + seeder polish

**Files:**
- Modify: `resources/views/livewire/pages/documents-index.blade.php` (+ its component if needed), `database/seeders/DemoSeeder.php`
- Test: `tests/Feature/Pages/DocumentsIndexDownloadTest.php`

**Interfaces:**
- Consumes: route `dokumenty.stiahnut`.
- Produces: global Dokumenty list shows a „Stiahnuť" link for the current version when it has a file. `DemoSeeder`: PRJ-001's `Rozpočet` v3.0 version gets a real seeded file — write a small text placeholder to the private disk (`Storage::disk('local')->put($path, 'Demo súbor — Rozpočet v3.0')`, path `documents/{projectId}/demo-rozpocet-v3.txt`... use extension `.pdf` name in `original_filename` = `Rozpocet_v3.pdf` but store plain text content; set `file_path`, `original_filename`, `file_size`). Seeder remains idempotent under `migrate:fresh --seed`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Pages/DocumentsIndexDownloadTest.php
use App\Models\{Document, DocumentVersion, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pages\DocumentsIndex;

uses(RefreshDatabase::class);

test('documents index shows download link for current version with file', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $document = Document::factory()->create();
    $version = DocumentVersion::factory()->for($document)->create(['file_path' => 'documents/1/a.pdf']);
    $version->activate($user);

    Livewire::test(DocumentsIndex::class)->assertSee('Stiahnuť');
});

test('demo seed provides a downloadable file', function () {
    $this->seed();
    $version = App\Models\DocumentVersion::whereNotNull('file_path')->first();
    expect($version)->not->toBeNull();
    $this->actingAs(User::factory()->create())
        ->get(route('dokumenty.stiahnut', $version))
        ->assertOk();
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement.** Note the seeded-file test hits the real local disk (no `Storage::fake`) — write into `storage/app/private` via the seeder; ensure the path is gitignored (it is, `storage/app/.gitignore`).
- [ ] **Step 4: PASS + full suite; `php artisan migrate:fresh --seed` manual check.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: global documents download link and seeded demo file"`

---

## Out of scope (later milestones)

Question/decision creation UI, gate item editing (M4); AI cross-checks over uploaded files (M5); file previews, versioned diffs, S3/remote storage, per-user file permissions (client layer, v2).
