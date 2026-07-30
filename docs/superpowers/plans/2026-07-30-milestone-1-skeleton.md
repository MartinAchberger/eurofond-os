# EUROFOND OS — Milestone 1: Skeleton Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Runnable Laravel app with auth, Orchid admin, the complete EUROFOND OS domain model (migrations + models + domain rules + tests) and seeded demo data.

**Architecture:** Laravel 12 monolith. Domain rules live as methods on Eloquent models (e.g. `Task::complete()`, `Project::advancePhase()`, `DocumentVersion::activate()`) and throw `DomainException` when invariants would break. Orchid serves `/admin`; the PM-facing Livewire UI comes in Milestone 2.

**Tech Stack:** PHP 8.3+, Laravel 12, Livewire 3 (via Breeze), Tailwind, Orchid Platform, Pest, spatie/laravel-activitylog. SQLite for dev/test, MySQL in production (migrations must stay DB-agnostic).

## Global Constraints

- All user-facing strings in Slovak (English identifiers in code).
- Enum values stored as strings, Slovak slugs without diacritics (e.g. `aktualna`, `zavazne`) — exact values below.
- Old document versions are archived, never deleted. No hard deletes of domain records.
- Domain invariants enforced in model methods, never only in UI.
- Project codes follow `PRJ-NNN` format.
- If `orchid/platform` conflicts with Laravel 12 at install time, pin `laravel/framework:^11.0` instead — everything else in this plan is unchanged.

---

### Task 1: Laravel scaffold

**Files:**
- Create: entire Laravel app at repo root (composer create-project into temp dir, then move — repo already has `docs/` and `.git`)

**Interfaces:**
- Produces: standard Laravel 12 app skeleton with Pest, SQLite database at `database/database.sqlite`.

- [ ] **Step 1: Scaffold Laravel into the existing repo**

```bash
cd ~/Projekty/eurofond-os
composer create-project laravel/laravel tmp-laravel
rsync -a tmp-laravel/ ./ --exclude .git
rm -rf tmp-laravel
```

- [ ] **Step 2: Install Pest**

```bash
composer remove phpunit/phpunit --dev --no-update
composer require pestphp/pest pestphp/pest-plugin-laravel --dev --with-all-dependencies
./vendor/bin/pest --init
```

(If `--init` complains about existing tests, delete `tests/` first and re-run; then re-create `tests/Feature/ExampleTest.php` via `pest --init` defaults.)

- [ ] **Step 3: Verify the app boots and tests pass**

Run: `php artisan migrate && ./vendor/bin/pest`
Expected: default example tests PASS.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "feat: scaffold Laravel 12 with Pest"
```

---

### Task 2: Auth via Breeze (Livewire stack)

**Files:**
- Create: Breeze-generated auth (routes, Livewire/Volt components, Tailwind config)
- Test: Breeze ships Pest auth tests in `tests/Feature/Auth/`

**Interfaces:**
- Produces: login/register/logout at `/login` etc., `auth` middleware usable by later milestones, Tailwind + Livewire installed.

- [ ] **Step 1: Install Breeze**

```bash
composer require laravel/breeze --dev
php artisan breeze:install livewire --pest
npm install && npm run build
```

- [ ] **Step 2: Run the shipped auth tests**

Run: `./vendor/bin/pest tests/Feature/Auth`
Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add -A
git commit -m "feat: add auth via Breeze (Livewire + Pest)"
```

---

### Task 3: Orchid admin

**Files:**
- Create: Orchid scaffolding (`app/Orchid/`, config, platform routes)

**Interfaces:**
- Produces: `/admin` panel guarded by login; `User` model extends `Orchid\Platform\Models\User`-compatible traits.

- [ ] **Step 1: Install Orchid**

```bash
composer require orchid/platform
php artisan orchid:install
```

If composer reports a Laravel-version conflict, apply the Global Constraints fallback (pin Laravel `^11.0`, `composer update`).

- [ ] **Step 2: Make `App\Models\User` Orchid-aware**

Per Orchid install docs, ensure `App\Models\User` extends `Orchid\Platform\Models\User` (keep Breeze's `HasFactory`, `Notifiable`; merge `$fillable` so it contains `name`, `email`, `password`, `permissions`).

- [ ] **Step 3: Create admin user and verify**

```bash
php artisan orchid:admin admin admin@example.com password
php artisan serve &
curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/admin  # expect 302 (redirect to login)
```

- [ ] **Step 4: Run full test suite** — `./vendor/bin/pest` — Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: add Orchid admin panel"
```

---

### Task 4: Domain enums

**Files:**
- Create: `app/Enums/ProjectPhase.php`, `ProjectHealth.php`, `DocumentVersionStatus.php`, `InboxItemStatus.php`, `InboxSource.php`, `QuestionStatus.php`, `AnswerBindingness.php`, `TaskPriority.php`, `TaskStatus.php`, `RiskLevel.php`, `RiskStatus.php`, `GateStatus.php`, `DiscrepancyStatus.php`, `AiSuggestionKind.php`, `AiSuggestionStatus.php`
- Test: `tests/Unit/EnumsTest.php`

**Interfaces:**
- Produces: string-backed enums used by every later task. Key definitions:

```php
// app/Enums/ProjectPhase.php — int-backed, 1..12
enum ProjectPhase: int
{
    case Screening = 1;
    case RozhodnutieOPriprave = 2;
    case ZberPodkladov = 3;
    case TechnickaFinancnaKontrola = 4;
    case PripravaZiadosti = 5;
    case Podanie = 6;
    case SchvalenieAZmluva = 7;
    case VerejneObstaravanie = 8;
    case Realizacia = 9;
    case PlatbyAMonitorovanie = 10;
    case Ukoncenie = 11;
    case Udrzatelnost = 12;

    public function label(): string
    {
        return match ($this) {
            self::Screening => 'Prvotný screening',
            self::RozhodnutieOPriprave => 'Rozhodnutie o príprave',
            self::ZberPodkladov => 'Zber podkladov',
            self::TechnickaFinancnaKontrola => 'Technická a finančná kontrola',
            self::PripravaZiadosti => 'Príprava žiadosti',
            self::Podanie => 'Podanie',
            self::SchvalenieAZmluva => 'Schválenie a zmluva',
            self::VerejneObstaravanie => 'Verejné obstarávanie',
            self::Realizacia => 'Realizácia',
            self::PlatbyAMonitorovanie => 'Platby a monitorovanie',
            self::Ukoncenie => 'Ukončenie',
            self::Udrzatelnost => 'Udržateľnosť',
        };
    }
}
```

String-backed enums (each with a `label(): string` returning the Slovak label with diacritics):

| Enum | Values |
|---|---|
| `ProjectHealth` | `dobre`, `stredne`, `riziko` |
| `DocumentVersionStatus` | `aktualna`, `historicka`, `nepotvrdena`, `nahradena` |
| `InboxItemStatus` | `nove`, `klasifikovane`, `schvalene`, `zamietnute` |
| `InboxSource` | `email`, `poznamka`, `subor`, `telefonat`, `ine` |
| `QuestionStatus` | `otvorena`, `zodpovedana`, `uzavreta` |
| `AnswerBindingness` | `zavazne`, `pracovne`, `neformalne` |
| `TaskPriority` | `nizka`, `stredna`, `vysoka`, `blokator` |
| `TaskStatus` | `otvorena`, `caka`, `hotova` |
| `RiskLevel` | `nizky`, `stredny`, `vysoky` |
| `RiskStatus` | `otvorene`, `mitigovane`, `uzavrete`, `prejavilo_sa` |
| `GateStatus` | `cakajuca`, `prejdena`, `zamietnuta` |
| `DiscrepancyStatus` | `otvoreny`, `vyrieseny`, `zamietnuty` |
| `AiSuggestionKind` | `inbox_klasifikacia`, `krizova_kontrola`, `navrh_textu`, `priorizacia` |
| `AiSuggestionStatus` | `navrhnute`, `schvalene`, `upravene`, `zamietnute` |

- [ ] **Step 1: Write the failing test**

```php
// tests/Unit/EnumsTest.php
use App\Enums\ProjectPhase;
use App\Enums\DocumentVersionStatus;

test('project has 12 phases with slovak labels', function () {
    expect(ProjectPhase::cases())->toHaveCount(12)
        ->and(ProjectPhase::Screening->label())->toBe('Prvotný screening')
        ->and(ProjectPhase::Udrzatelnost->value)->toBe(12);
});

test('document version statuses', function () {
    expect(array_column(DocumentVersionStatus::cases(), 'value'))
        ->toBe(['aktualna', 'historicka', 'nepotvrdena', 'nahradena']);
});
```

- [ ] **Step 2: Run test to verify it fails** — `./vendor/bin/pest tests/Unit/EnumsTest.php` — Expected: FAIL (class not found).
- [ ] **Step 3: Implement all 15 enums** per the table above (every enum gets `label()`).
- [ ] **Step 4: Run test to verify it passes** — Expected: PASS.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: add domain enums"`

---

### Task 5: Clients, DocumentTypes, Projects

**Files:**
- Create: migrations `create_clients_table`, `create_document_types_table`, `create_projects_table`; models `app/Models/Client.php`, `DocumentType.php`, `Project.php`; factories for each
- Test: `tests/Feature/ProjectModelTest.php`

**Interfaces:**
- Produces:
  - `Client` — fields `name`, `type` (string: `obec|firma|ine`), `ico?`, `contact_name?`, `contact_email?`, `contact_phone?`; relation `projects()`.
  - `DocumentType` — fields `name`, `slug` (unique).
  - `Project` — fields `code` (unique), `name`, `client_id`, `call_name?`, `budget_total?` decimal(14,2), `grant_requested?` decimal(14,2), `phase` (cast `ProjectPhase`), `status_label?` string, `health` (cast `ProjectHealth`, default `dobre`), `next_deadline?` date, `main_blocker?` text, `next_step?` text, `owner_id` → users.
  - Relations: `Project::client()`, `Project::owner()`, `Client::projects()`.
  - Method (implemented in Task 9): `Project::advancePhase(User $by): void`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/ProjectModelTest.php
use App\Enums\ProjectHealth;
use App\Enums\ProjectPhase;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project belongs to client and owner, casts enums', function () {
    $owner = User::factory()->create();
    $client = Client::factory()->create(['name' => 'Obec Malé Hoste', 'type' => 'obec']);
    $project = Project::factory()->create([
        'code' => 'PRJ-001',
        'client_id' => $client->id,
        'owner_id' => $owner->id,
        'phase' => ProjectPhase::ZberPodkladov,
        'health' => ProjectHealth::Dobre,
    ]);

    expect($project->client->name)->toBe('Obec Malé Hoste')
        ->and($project->owner->id)->toBe($owner->id)
        ->and($project->phase)->toBe(ProjectPhase::ZberPodkladov)
        ->and($project->health)->toBe(ProjectHealth::Dobre)
        ->and($client->projects)->toHaveCount(1);
});

test('project code is unique', function () {
    Project::factory()->create(['code' => 'PRJ-002']);
    Project::factory()->create(['code' => 'PRJ-002']);
})->throws(Illuminate\Database\QueryException::class);
```

- [ ] **Step 2: Run test to verify it fails** — Expected: FAIL (table/model missing).
- [ ] **Step 3: Implement migrations, models, factories**

Migration columns exactly per Interfaces above (`foreignId(...)->constrained()`, `decimal('budget_total', 14, 2)->nullable()`, `unsignedTinyInteger('phase')`, `string('health')->default('dobre')`). Model casts:

```php
// app/Models/Project.php (excerpt)
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
```

`ProjectFactory` generates `code` via `'PRJ-' . str_pad((string) fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT)`, creates `client_id => Client::factory()`, `owner_id => User::factory()`, `phase => ProjectPhase::Screening`, `health => 'dobre'`.

- [ ] **Step 4: Run test to verify it passes** — Expected: PASS.
- [ ] **Step 5: Commit** — `git commit -am "feat: add clients, document types and projects"` (use `git add -A` first).

---

### Task 6: Documents with versioning + supersede rule

**Files:**
- Create: migrations `create_documents_table`, `create_document_versions_table`; models `Document.php`, `DocumentVersion.php`; factories
- Test: `tests/Feature/DocumentVersioningTest.php`

**Interfaces:**
- Produces:
  - `Document` — `project_id`, `document_type_id`, `title`; relations `project()`, `type()`, `versions()`, `currentVersion(): ?DocumentVersion` (helper returning version with status `aktualna`).
  - `DocumentVersion` — `document_id`, `version_label`, `file_path?`, `issued_at?` date, `author?`, `status` (cast `DocumentVersionStatus`, default `nepotvrdena`), `confirmed_by?` → users, `confirmed_at?`, `uploaded_by` → users.
  - `DocumentVersion::activate(User $by): void` — sets this version to `aktualna` with `confirmed_by/at`; any previously `aktualna` version of the same document becomes `nahradena`. Old versions are never deleted.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/DocumentVersioningTest.php
use App\Enums\DocumentVersionStatus;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('activating a new version supersedes the old one, nothing is deleted', function () {
    $user = User::factory()->create();
    $document = Document::factory()->create();
    $v1 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v1.0']);
    $v2 = DocumentVersion::factory()->for($document)->create(['version_label' => 'v2.0']);

    $v1->activate($user);
    expect($v1->fresh()->status)->toBe(DocumentVersionStatus::Aktualna)
        ->and($v1->fresh()->confirmed_by)->toBe($user->id);

    $v2->activate($user);
    expect($v2->fresh()->status)->toBe(DocumentVersionStatus::Aktualna)
        ->and($v1->fresh()->status)->toBe(DocumentVersionStatus::Nahradena)
        ->and($document->versions()->count())->toBe(2)
        ->and($document->currentVersion()->id)->toBe($v2->id);
});

test('new versions start as nepotvrdena', function () {
    $v = DocumentVersion::factory()->create();
    expect($v->status)->toBe(DocumentVersionStatus::Nepotvrdena);
});
```

- [ ] **Step 2: Run test to verify it fails** — Expected: FAIL.
- [ ] **Step 3: Implement**

```php
// app/Models/DocumentVersion.php (excerpt)
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
```

```php
// app/Models/Document.php (excerpt)
public function currentVersion(): ?DocumentVersion
{
    return $this->versions()->where('status', DocumentVersionStatus::Aktualna)->first();
}
```

- [ ] **Step 4: Run test to verify it passes** — Expected: PASS.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: document versioning with supersede rule"`

---

### Task 7: Questions, Answers, Decisions

**Files:**
- Create: migrations + models + factories for `Question`, `Answer`, `Decision`
- Test: `tests/Feature/QuestionAnswerDecisionTest.php`

**Interfaces:**
- Produces:
  - `Question` — `project_id`, `document_id?`, `asked_by` string, `asked_to` string, `asked_at` datetime, `reason?` text, `body` text, `due_at?` date, `status` (cast `QuestionStatus`, default `otvorena`), `created_by` → users. Relations: `project()`, `document()`, `answers()`.
  - `Answer` — `question_id`, `answered_by` string, `answered_at` datetime, `body` text, `source?`, `bindingness` (cast `AnswerBindingness`), `recorded_by` → users. Creating an answer sets its question's status to `zodpovedana` (model `created` event or explicit `Question::recordAnswer()` — use a `booted()` created-hook on `Answer`).
  - `Decision` — `project_id`, `question_id?`, `answer_id?`, `body` text, `approved_by` string, `approved_at` datetime, `rationale?` text, `recorded_by` → users.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/QuestionAnswerDecisionTest.php
use App\Enums\AnswerBindingness;
use App\Enums\QuestionStatus;
use App\Models\Answer;
use App\Models\Decision;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('recording an answer marks question as zodpovedana', function () {
    $question = Question::factory()->create();
    expect($question->status)->toBe(QuestionStatus::Otvorena);

    Answer::factory()->for($question)->create(['bindingness' => AnswerBindingness::Zavazne]);

    expect($question->fresh()->status)->toBe(QuestionStatus::Zodpovedana)
        ->and($question->answers()->first()->bindingness)->toBe(AnswerBindingness::Zavazne);
});

test('decision can reference question and answer', function () {
    $answer = Answer::factory()->create();
    $decision = Decision::factory()->create([
        'question_id' => $answer->question_id,
        'answer_id' => $answer->id,
    ]);
    expect($decision->answer->id)->toBe($answer->id)
        ->and($decision->question->id)->toBe($answer->question_id);
});
```

- [ ] **Step 2: Run test to verify it fails** — Expected: FAIL.
- [ ] **Step 3: Implement** migrations/models/factories per Interfaces; on `Answer`:

```php
protected static function booted(): void
{
    static::created(function (Answer $answer) {
        $answer->question->update(['status' => QuestionStatus::Zodpovedana]);
    });
}
```

- [ ] **Step 4: Run test to verify it passes** — Expected: PASS.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: questions, answers and decisions"`

---

### Task 8: Tasks with evidence rule

**Files:**
- Create: migration `create_project_tasks_table` (model `ProjectTask` — avoid clashing with any `Task` naming; table `project_tasks`), factory
- Test: `tests/Feature/TaskEvidenceTest.php`

**Interfaces:**
- Produces:
  - `ProjectTask` — `project_id?`, `title`, `note?`, `assignee_id?` → users, `priority` (cast `TaskPriority`, default `stredna`), `due_at?` date, `status` (cast `TaskStatus`, default `otvorena`), `required_evidence?` text, `evidence_document_version_id?` → document_versions, `evidence_note?` text, `completed_at?`.
  - `ProjectTask::complete(?DocumentVersion $evidence = null, ?string $evidenceNote = null): void` — throws `DomainException('Úlohu nemožno uzavrieť bez dôkazu.')` when both arguments are empty; otherwise sets status `hotova`, `completed_at`, and stores the evidence.
  - Direct `update(['status' => 'hotova'])` is not the supported path — completion goes through `complete()` (UI in Milestone 2 will only call `complete()`).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/TaskEvidenceTest.php
use App\Enums\TaskStatus;
use App\Models\DocumentVersion;
use App\Models\ProjectTask;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('task cannot be completed without evidence', function () {
    $task = ProjectTask::factory()->create();
    $task->complete();
})->throws(DomainException::class, 'Úlohu nemožno uzavrieť bez dôkazu.');

test('task completes with document evidence', function () {
    $task = ProjectTask::factory()->create();
    $version = DocumentVersion::factory()->create();

    $task->complete(evidence: $version);

    expect($task->fresh()->status)->toBe(TaskStatus::Hotova)
        ->and($task->fresh()->evidence_document_version_id)->toBe($version->id)
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

test('task completes with written evidence note', function () {
    $task = ProjectTask::factory()->create();
    $task->complete(evidenceNote: 'Bankový výpis priložený v spise č. 12');
    expect($task->fresh()->status)->toBe(TaskStatus::Hotova);
});
```

- [ ] **Step 2: Run test to verify it fails** — Expected: FAIL.
- [ ] **Step 3: Implement**

```php
// app/Models/ProjectTask.php (excerpt)
public function complete(?DocumentVersion $evidence = null, ?string $evidenceNote = null): void
{
    if ($evidence === null && blank($evidenceNote)) {
        throw new DomainException('Úlohu nemožno uzavrieť bez dôkazu.');
    }

    $this->update([
        'status' => TaskStatus::Hotova,
        'evidence_document_version_id' => $evidence?->id,
        'evidence_note' => $evidenceNote,
        'completed_at' => now(),
    ]);
}
```

- [ ] **Step 4: Run test to verify it passes** — Expected: PASS.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: tasks closable only with evidence"`

---

### Task 9: Risks, Gates + phase-advance rule

**Files:**
- Create: migrations + models + factories for `Risk`, `Gate`, `GateItem`; add `advancePhase()` to `Project`
- Test: `tests/Feature/GateTest.php`

**Interfaces:**
- Produces:
  - `Risk` — `project_id`, `title`, `description?`, `impact` (cast `RiskLevel`), `likelihood` (cast `RiskLevel`), `mitigation?`, `status` (cast `RiskStatus`, default `otvorene`).
  - `Gate` — `project_id`, `phase` unsignedTinyInteger (the phase this gate guards leaving), `name`, `status` (cast `GateStatus`, default `cakajuca`), `checked_by?` → users, `checked_at?`. Relation `items()`.
  - `GateItem` — `gate_id`, `label`, `is_met` bool default false, `evidence?` text.
  - `Gate::pass(User $by): void` — throws `DomainException('Brána má nesplnené podmienky.')` if any item has `is_met = false`; else sets `prejdena` + `checked_by/at`.
  - `Project::advancePhase(User $by): void` — finds the gate for the current phase; throws `DomainException('Projekt nemôže postúpiť: kontrolná brána neprešla.')` when the gate is missing or not `prejdena`; otherwise increments `phase` (no-op guard: throws `DomainException` if already phase 12).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/GateTest.php
use App\Enums\GateStatus;
use App\Enums\ProjectPhase;
use App\Models\Gate;
use App\Models\GateItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gate cannot pass with unmet items', function () {
    $gate = Gate::factory()->create();
    GateItem::factory()->for($gate)->create(['is_met' => false]);
    $gate->pass(User::factory()->create());
})->throws(DomainException::class, 'Brána má nesplnené podmienky.');

test('project cannot advance phase without passed gate', function () {
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);
    $project->advancePhase(User::factory()->create());
})->throws(DomainException::class);

test('project advances phase after gate passes', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);
    $gate = Gate::factory()->for($project)->create(['phase' => 3]);
    GateItem::factory()->for($gate)->create(['is_met' => true]);

    $gate->pass($user);
    expect($gate->fresh()->status)->toBe(GateStatus::Prejdena);

    $project->advancePhase($user);
    expect($project->fresh()->phase)->toBe(ProjectPhase::TechnickaFinancnaKontrola);
});
```

- [ ] **Step 2: Run test to verify it fails** — Expected: FAIL.
- [ ] **Step 3: Implement**

```php
// app/Models/Gate.php (excerpt)
public function pass(User $by): void
{
    if ($this->items()->where('is_met', false)->exists()) {
        throw new DomainException('Brána má nesplnené podmienky.');
    }
    $this->update([
        'status' => GateStatus::Prejdena,
        'checked_by' => $by->id,
        'checked_at' => now(),
    ]);
}
```

```php
// app/Models/Project.php (excerpt)
public function advancePhase(User $by): void
{
    if ($this->phase === ProjectPhase::Udrzatelnost) {
        throw new DomainException('Projekt je v poslednej fáze.');
    }

    $gate = $this->gates()
        ->where('phase', $this->phase->value)
        ->where('status', GateStatus::Prejdena)
        ->first();

    if ($gate === null) {
        throw new DomainException('Projekt nemôže postúpiť: kontrolná brána neprešla.');
    }

    $this->update(['phase' => ProjectPhase::from($this->phase->value + 1)]);
}
```

(`Project::gates()` hasMany added alongside.)

- [ ] **Step 4: Run test to verify it passes** — Expected: PASS.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: risks, gates and phase-advance rule"`

---

### Task 10: Inbox, Discrepancies, AiSuggestions, audit log

**Files:**
- Create: migrations + models + factories for `InboxItem`, `Discrepancy`, `DiscrepancySource`, `AiSuggestion`; install `spatie/laravel-activitylog`
- Test: `tests/Feature/InboxAndAuditTest.php`

**Interfaces:**
- Produces:
  - `InboxItem` — `source` (cast `InboxSource`), `raw_content` text, `file_path?`, `status` (cast `InboxItemStatus`, default `nove`), `suggested_project_id?` → projects, `suggested_type?` string, `suggested_deadline?` date, `ai_confidence?` decimal(3,2), `unconfirmed` bool default true, `created_by` → users.
  - `Discrepancy` — `project_id`, `title`, `description` text, `status` (cast `DiscrepancyStatus`, default `otvoreny`), `assignee_id?` → users; relation `sources()`.
  - `DiscrepancySource` — `discrepancy_id`, `document_version_id` → document_versions, `citation?` text.
  - `AiSuggestion` — `kind` (cast `AiSuggestionKind`), `project_id?`, `suggestable_type?`/`suggestable_id?` (morphs, nullable), `input_summary?` text, `payload` json cast `array`, `status` (cast `AiSuggestionStatus`, default `navrhnute`), `reviewed_by?` → users, `reviewed_at?`.
  - Audit: `Project`, `DocumentVersion`, `ProjectTask`, `Decision`, `Gate` use `Spatie\Activitylog\Traits\LogsActivity` with `LogOptions::defaults()->logFillable()->logOnlyDirty()`.

- [ ] **Step 1: Install activitylog**

```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

- [ ] **Step 2: Write the failing test**

```php
// tests/Feature/InboxAndAuditTest.php
use App\Enums\AiSuggestionStatus;
use App\Enums\InboxItemStatus;
use App\Models\AiSuggestion;
use App\Models\InboxItem;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

test('inbox item defaults to nove and unconfirmed', function () {
    $item = InboxItem::factory()->create();
    expect($item->status)->toBe(InboxItemStatus::Nove)
        ->and($item->unconfirmed)->toBeTrue();
});

test('ai suggestion stores payload and starts as navrhnute', function () {
    $s = AiSuggestion::factory()->create(['payload' => ['typ' => 'dokument', 'istota' => 0.4]]);
    expect($s->status)->toBe(AiSuggestionStatus::Navrhnute)
        ->and($s->payload['typ'])->toBe('dokument');
});

test('project changes are audit-logged', function () {
    $project = Project::factory()->create();
    $project->update(['main_blocker' => 'Čaká sa na novú PD']);
    expect(Activity::where('subject_type', Project::class)->count())->toBeGreaterThanOrEqual(1);
});
```

- [ ] **Step 3: Run test to verify it fails** — Expected: FAIL.
- [ ] **Step 4: Implement** migrations/models/factories per Interfaces; add `LogsActivity` trait + `getActivitylogOptions()` to the five audited models.
- [ ] **Step 5: Run full suite** — `./vendor/bin/pest` — Expected: all PASS.
- [ ] **Step 6: Commit** — `git add -A && git commit -m "feat: inbox, discrepancies, AI suggestions and audit log"`

---

### Task 11: Demo data seeders

**Files:**
- Create: `database/seeders/DocumentTypeSeeder.php`, `DemoSeeder.php`; modify `DatabaseSeeder.php`
- Test: `tests/Feature/DemoSeederTest.php`

**Interfaces:**
- Consumes: every model above.
- Produces: `php artisan db:seed` yields the demo portfolio from the mockup.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/DemoSeederTest.php
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo seed creates the mockup portfolio', function () {
    $this->seed();

    expect(User::where('email', 'denis@eurofond.test')->exists())->toBeTrue()
        ->and(Project::count())->toBeGreaterThanOrEqual(4)
        ->and(Project::where('code', 'PRJ-001')->first()->name)->toBe('Malé Hoste')
        ->and(Project::where('code', 'PRJ-005')->first()->name)->toBe('Hronská Dúbrava');
});
```

- [ ] **Step 2: Run test to verify it fails** — Expected: FAIL.
- [ ] **Step 3: Implement seeders**

`DocumentTypeSeeder`: PD, Rozpočet, Energetické hodnotenie, LV, Zmluva, Faktúra, Stanovisko VO, Iné (slugs without diacritics).

`DemoSeeder` (called from `DatabaseSeeder` after `DocumentTypeSeeder`):
- User `Denis` (`denis@eurofond.test`, password `password`).
- Clients: Obec Malé Hoste, Mesto Tornaľa, Obec Hronská Dúbrava, Obec Galantský kaštieľ vlastník (typ obec).
- Projects (owner Denis): PRJ-001 „Malé Hoste" (fáza 4, status_label „Čaká na PD", health dobre, deadline 2026-08-28), PRJ-002 „Tornaľa" (fáza 10, „Monitoring", dobre, 2026-09-15), PRJ-005 „Hronská Dúbrava" (fáza 9, „Rozpočet / audit", stredne, 2026-08-31), PRJ-006 „Galantský kaštieľ" (fáza 5, „Príprava žiadosti", dobre, 2026-09-10).
- For PRJ-001: dokument „Projektová dokumentácia" with v1.0 (`nahradena`) and v1.2 (`aktualna`), dokument „Rozpočet" v3.0 (`aktualna`); open question „Je rozpočet v súlade s usmernením?"; task „Skontrolovať PD a doplniť podklady" (priorita vysoka, required_evidence „Checklist podkladov"); risk „Stará PD nesedí s novým rozpočtom" (impact vysoky).
- For PRJ-005: gate fázy 9 „Brána 3 – Rozpočet a oprávnenosť" with two met items, status `prejdena` (checked_by Denis); open questions per mockup; 2 open risks.
- Spread further tasks/risks so dashboard counts in Milestone 2 look alive (≥ 4 upcoming deadlines, ≥ 7 open risks total, ≥ 3 tasks with status `caka`).

- [ ] **Step 4: Run test + full suite** — `./vendor/bin/pest` — Expected: PASS.
- [ ] **Step 5: Verify manually** — `php artisan migrate:fresh --seed` then `php artisan tinker --execute="echo App\Models\Project::count();"` → ≥ 4.
- [ ] **Step 6: Commit and push**

```bash
git add -A
git commit -m "feat: demo data seeders (mockup portfolio)"
git push
```

---

## After this milestone

Milestone 2 (dashboard + project workspace UI in Livewire), 3 (document upload/archive UX), 4 (process flows), 5 (AI layer via `anthropic-ai/sdk`, model `claude-opus-5`) get their own plans once Milestone 1 is merged and reviewed.

## Deviations

- Actual installed stack is Laravel 13.8 + Orchid 14.53 (plan said Laravel 12 with a fallback to 11 if `orchid/platform` conflicted); the forward-compatible install succeeded and all other constraints in this plan were honored unchanged.
- spatie/laravel-activitylog installed at v5, whose `Activity` model uses namespaced `Spatie\Activitylog\...` classes and stores attribute diffs on a dedicated `attribute_changes` column (not `properties['attributes']` as in older majors); models use `logAll()` for full-attribute coverage, documented in the Task 10 report.
