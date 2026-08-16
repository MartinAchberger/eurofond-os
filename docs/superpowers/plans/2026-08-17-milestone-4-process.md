# EUROFOND OS — Milestone 4: Proces Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** The process layer becomes fully manageable from the UI — create questions, record answers, record decisions born from them, close questions, edit gate checklists (create gate, add conditions, tick conditions), and create tasks directly in the project workspace — all enforcing the M1 domain rules.

**Architecture:** Extends the read-only `QuestionsTab` with three child form components (create question, record answer, record decision) and a close action; extends `PhasesTab` with gate/condition editing backed by two new domain methods (`Gate::addItem()`, `GateItem::markMet()`) that protect passed gates; adds one new domain method `Question::close()`. Task creation in the workspace reuses the existing `Tasks\CreateTaskModal` with a project preset. No new pages, no new routes, no schema changes except none — all columns already exist from M1.

**Tech Stack:** Laravel 13, Livewire 3, Pest 5. No new packages, no new migrations.

**Spec:** `docs/superpowers/specs/2026-07-30-eurofond-os-design.md` (section „Dátový model" — Question/Answer/Decision/Task/Gate; section „Poradie výstavby" bod 4) + `docs/reference/EUROFOND-OS-poznamky.md` (sections „Otázky, odpovede a rozhodnutia", „Úloha nie je to-do list", „Kontrolné brány").

## Global Constraints

- All user-facing strings Slovak with diacritics. Product name EUROFOND OS.
- Domain rules live in model methods, never in UI: closing questions via `Question::close()`, gate conditions via `Gate::addItem()` / `GateItem::markMet()`, task completion via the existing `ProjectTask::complete()`; UI catches `DomainException` into an `$error` property (pattern from `TasksTab`/`PhasesTab`/`DocumentsTab`).
- Nothing is ever deleted; no delete UI anywhere. Questions are closed, not removed.
- Livewire mutations validate all input; every action resolving a model by id scopes it to the project (`$this->project->questions()->findOrFail($id)` style). `wire:key` on any row loop that injects panels or child components.
- Child form components follow the house pattern: `bool $open = false`, `toggle()`, `save()` with `$this->validate()`, then `$this->reset(...)` + `$this->resetValidation()` + `$this->dispatch('<x>-created')`; parent tab listens with `#[On(...)]` and busts its computed cache via `unset($this->...)`.
- Status chip visual language follows M2: `QuestionStatus` otvorena=amber „Otvorená", zodpovedana=emerald „Zodpovedaná", uzavreta=gray „Uzavretá"; `AnswerBindingness` zavazne=emerald „Záväzné", pracovne=blue „Pracovné", neformalne=gray „Neformálne".
- Do NOT add or reorder enum cases — `tests/Unit/EnumsTest.php` asserts exact case ordering for every enum.
- Tests: Pest + `Livewire::test()`, `uses(RefreshDatabase::class)`, `$this->actingAs(User::factory()->create())` before Livewire tests.

## Component & File Map

| Unit | Path |
|---|---|
| Domain: close question | `app/Models/Question.php` (+ `LogsActivity`) |
| Domain: gate editing rules | `app/Models/Gate.php` (`addItem()`), `app/Models/GateItem.php` (`markMet()`) |
| Create question UI | `app/Livewire/Projects/CreateQuestionForm.php` (+ view) |
| Record answer UI | `app/Livewire/Projects/RecordAnswerForm.php` (+ view) |
| Record decision UI | `app/Livewire/Projects/RecordDecisionForm.php` (+ view) |
| Question actions & richer view | extend `app/Livewire/Projects/QuestionsTab.php` (+ view) |
| Gate editing UI | extend `app/Livewire/Projects/PhasesTab.php` (+ view) |
| Workspace task creation | `app/Livewire/Tasks/CreateTaskModal.php`, `app/Livewire/Projects/TasksTab.php`, `resources/views/livewire/pages/project-show.blade.php` |
| Decisions index polish | `app/Livewire/Pages/DecisionsIndex.php` (+ view) |
| Seeder polish | `database/seeders/DemoSeeder.php` |

---

### Task 1: Domain — `Question::close()` + activity log

**Files:**
- Modify: `app/Models/Question.php`
- Test: `tests/Feature/QuestionCloseTest.php`

**Interfaces:**
- Produces: `Question::close(): void` — sets status `QuestionStatus::Uzavreta`; throws `DomainException('Otázka je už uzavretá.')` when status is already `Uzavreta`. Closing is allowed from `Otvorena` (question became moot) and from `Zodpovedana` (normal flow). `Question` gains `use LogsActivity;` with the same options as `Decision` (`LogOptions::defaults()->logAll()->logOnlyDirty()`) so closing is auditable. Also add the missing relation `decisions(): HasMany` (`$this->hasMany(Decision::class)`) — Task 4 consumes it.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/QuestionCloseTest.php
use App\Enums\QuestionStatus;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('close marks the question uzavreta', function () {
    $question = Question::factory()->create();

    $question->close();

    expect($question->fresh()->status)->toBe(QuestionStatus::Uzavreta);
});

test('an answered question can be closed', function () {
    $question = Question::factory()->create();
    Answer::factory()->for($question)->create();
    expect($question->fresh()->status)->toBe(QuestionStatus::Zodpovedana);

    $question->fresh()->close();

    expect($question->fresh()->status)->toBe(QuestionStatus::Uzavreta);
});

test('closing an already closed question throws', function () {
    $question = Question::factory()->create();
    $question->close();
    $question->fresh()->close();
})->throws(DomainException::class, 'Otázka je už uzavretá.');

test('question has a decisions relation', function () {
    $question = Question::factory()->create();
    expect($question->decisions)->toBeEmpty();
});
```

- [ ] **Step 2: Run test to verify it fails** — `./vendor/bin/pest tests/Feature/QuestionCloseTest.php` — Expected: FAIL (`close()` undefined).
- [ ] **Step 3: Implement**

```php
// app/Models/Question.php (add — imports: DomainException, HasMany,
// Spatie\Activitylog\Traits\LogsActivity, Spatie\Activitylog\LogOptions)
use LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()->logAll()->logOnlyDirty();
}

public function decisions(): HasMany
{
    return $this->hasMany(Decision::class);
}

public function close(): void
{
    if ($this->status === QuestionStatus::Uzavreta) {
        throw new DomainException('Otázka je už uzavretá.');
    }

    $this->update(['status' => QuestionStatus::Uzavreta]);
}
```

(Copy the exact `getActivitylogOptions()` body from `app/Models/Decision.php` so options stay identical.)

- [ ] **Step 4: Run test to verify it passes** — Expected: PASS; run full suite.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: question close rule and activity logging"`

---

### Task 2: Create question form

**Files:**
- Create: `app/Livewire/Projects/CreateQuestionForm.php` + `resources/views/livewire/projects/create-question-form.blade.php`
- Modify: `app/Livewire/Projects/QuestionsTab.php`, `resources/views/livewire/projects/questions-tab.blade.php` (header row gets „Nová otázka" button; component embedded with `:project="$project"`)
- Test: `tests/Feature/Projects/CreateQuestionFormTest.php`

**Interfaces:**
- Consumes: `Question` model (M1).
- Produces: `CreateQuestionForm` — props `public Project $project`, `bool $open = false`, `string $askedBy = ''`, `string $askedTo = ''`, `string $body = ''`, `string $reason = ''`, `?string $dueAt = null`, `?int $documentId = null`; computed `documents()` = `$this->project->documents()->orderBy('title')->get()`; actions `toggle()`, `save()` — validates `askedBy` required max 255, `askedTo` required max 255, `body` required, `reason` nullable, `dueAt` nullable date, `documentId` nullable + `Rule::exists('documents', 'id')->where('project_id', $this->project->id)`; creates `Question` with `asked_at => now()`, `created_by => auth()->id()`, `reason => $this->reason ?: null`, `due_at => $this->dueAt ?: null`, `document_id => $this->documentId`; then reset + `resetValidation()` + dispatches `question-created`. `QuestionsTab` gains `#[On('question-created')] public function refreshQuestions(): void { unset($this->questions); }`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/CreateQuestionFormTest.php
use App\Enums\QuestionStatus;
use App\Livewire\Projects\CreateQuestionForm;
use App\Models\{Document, Project, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('validates required fields', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(CreateQuestionForm::class, ['project' => $project])
        ->call('save')
        ->assertHasErrors(['askedBy' => 'required', 'askedTo' => 'required', 'body' => 'required']);
});

test('creates a question for the project with optional document link', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $document = Document::factory()->for($project)->create();

    Livewire::test(CreateQuestionForm::class, ['project' => $project])
        ->set('askedBy', 'Denis')
        ->set('askedTo', 'Obec Malé Hoste')
        ->set('body', 'Je list vlastníctva aktuálny?')
        ->set('reason', 'Podklad pre prílohu č. 3')
        ->set('dueAt', today()->addDays(7)->toDateString())
        ->set('documentId', $document->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('question-created');

    $question = Question::sole();
    expect($question->project_id)->toBe($project->id)
        ->and($question->status)->toBe(QuestionStatus::Otvorena)
        ->and($question->document_id)->toBe($document->id)
        ->and($question->created_by)->toBe($user->id)
        ->and($question->asked_at)->not->toBeNull();
});

test('rejects a document from another project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreign = Document::factory()->create();

    Livewire::test(CreateQuestionForm::class, ['project' => $project])
        ->set('askedBy', 'Denis')
        ->set('askedTo', 'Obec')
        ->set('body', 'Otázka?')
        ->set('documentId', $foreign->id)
        ->call('save')
        ->assertHasErrors(['documentId']);
});
```

- [ ] **Step 2: Run to verify FAIL** (class missing).
- [ ] **Step 3: Implement** per Interfaces. View: inline card shown when `$open`, fields Kto sa pýta / Komu (inputs), Presné znenie (textarea), Dôvod otázky (textarea, optional), Termín na odpoveď (date, optional), Dokument (select „— Bez dokumentu —" + project documents), buttons Zrušiť (`toggle`) / Vytvoriť otázku. Empty-string select coerces to null on `?int` (established Livewire behavior). Embed in `questions-tab.blade.php` header next to the „Požiadavky" heading: button „Nová otázka" via `wire:click="$dispatch(...)"` is NOT needed — follow the `DocumentsTab`/`CreateDocumentForm` pattern instead (button lives inside the child component's own view, toggling `$open`; mount `<livewire:projects.create-question-form :project="$project" />` in the tab header row — copy the exact layout from `documents-tab.blade.php`).
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: create question form in workspace"`

---

### Task 3: Record answer + close question in QuestionsTab

**Files:**
- Create: `app/Livewire/Projects/RecordAnswerForm.php` + `resources/views/livewire/projects/record-answer-form.blade.php`
- Modify: `app/Livewire/Projects/QuestionsTab.php`, `resources/views/livewire/projects/questions-tab.blade.php`
- Test: `tests/Feature/Projects/RecordAnswerFormTest.php`, `tests/Feature/Projects/QuestionsTabActionsTest.php`

**Interfaces:**
- Consumes: `Answer` created-event domain rule (M1: creating an Answer flips its question to `Zodpovedana`), `Question::close()` (Task 1), `question-created` listener (Task 2).
- Produces: `RecordAnswerForm` — props `public Question $question`, `bool $open = false`, `string $answeredBy = ''`, `string $body = ''`, `string $source = ''`, `string $bindingness = 'pracovne'`; actions `toggle()`, `save()` — validates `answeredBy` required max 255, `body` required, `source` nullable max 255, `bindingness` `Rule::enum(AnswerBindingness::class)`; creates `Answer` with `answered_at => now()`, `recorded_by => auth()->id()`, `source => $this->source ?: null`; reset + `resetValidation()` + dispatches `answer-recorded`. `QuestionsTab` gains `public ?string $error = null`, listener `#[On('answer-recorded')]` (same cache bust as `question-created`), and action `closeQuestion(int $questionId)` — resolves via `$this->project->questions()->findOrFail($questionId)`, calls `close()` in try/catch `DomainException` → `$error` (on success `$this->error = null`), then `unset($this->questions)`.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Projects/RecordAnswerFormTest.php
use App\Enums\{AnswerBindingness, QuestionStatus};
use App\Livewire\Projects\RecordAnswerForm;
use App\Models\{Answer, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('records an answer and the question becomes zodpovedana', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $question = Question::factory()->create();

    Livewire::test(RecordAnswerForm::class, ['question' => $question])
        ->set('answeredBy', 'Ing. Krajčí')
        ->set('body', 'Áno, hodnotenie je priložené v prílohe č. 2.')
        ->set('source', 'E-mail z 12. 8. 2026')
        ->set('bindingness', 'zavazne')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('answer-recorded');

    $answer = Answer::sole();
    expect($answer->bindingness)->toBe(AnswerBindingness::Zavazne)
        ->and($answer->recorded_by)->toBe($user->id)
        ->and($question->fresh()->status)->toBe(QuestionStatus::Zodpovedana);
});

test('validates required fields and bindingness', function () {
    $this->actingAs(User::factory()->create());
    $question = Question::factory()->create();

    Livewire::test(RecordAnswerForm::class, ['question' => $question])
        ->set('bindingness', 'neplatne')
        ->call('save')
        ->assertHasErrors(['answeredBy', 'body', 'bindingness']);
});
```

```php
// tests/Feature/Projects/QuestionsTabActionsTest.php
use App\Enums\QuestionStatus;
use App\Livewire\Projects\QuestionsTab;
use App\Models\{Project, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('close question from UI', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = Question::factory()->for($project)->create();

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->call('closeQuestion', $question->id)
        ->assertSet('error', null);

    expect($question->fresh()->status)->toBe(QuestionStatus::Uzavreta);
});

test('closing a closed question surfaces the domain error', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $question = Question::factory()->for($project)->create();
    $question->close();

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->call('closeQuestion', $question->id)
        ->assertSet('error', 'Otázka je už uzavretá.');
});

test('cannot close another projects question', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreign = Question::factory()->create();

    expect(fn () => Livewire::test(QuestionsTab::class, ['project' => $project])
        ->call('closeQuestion', $foreign->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('question meta renders reason, due date and document', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $document = App\Models\Document::factory()->for($project)->create(['title' => 'Projektová dokumentácia']);
    Question::factory()->for($project)->create([
        'reason' => 'Podklad pre prílohu č. 3',
        'due_at' => '2026-09-01',
        'document_id' => $document->id,
    ]);

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->assertSee('Podklad pre prílohu č. 3')
        ->assertSee('1. 9. 2026')
        ->assertSee('Projektová dokumentácia');
});
```

- [ ] **Step 2: Run to verify FAIL.**
- [ ] **Step 3: Implement** per Interfaces. `QuestionsTab::questions()` computed gains eager loads: `->with(['answers', 'document', 'decisions'])` (decisions used by Task 4; adding now avoids touching the query twice). View changes in `questions-tab.blade.php`: red error alert bound to `$error` above the list (copy markup from `documents-tab.blade.php`); per question — meta line extended with `Dôvod: {{ $question->reason }}` (when set), `Termín: {{ $question->due_at?->format('j. n. Y') }}` (when set), `Dokument: {{ $question->document?->title }}` (when set); answers block shows `Zdroj: {{ $answer->source }}` when set; action row per question (only when status ≠ `Uzavreta`): mount `<livewire:projects.record-answer-form :question="$question" wire:key="answer-form-{{ $question->id }}" />` and an „Uzavrieť otázku" button `wire:click="closeQuestion({{ $question->id }})"`. `RecordAnswerForm` view: „Zaznamenať odpoveď" toggle button; when open — fields Kto odpovedal (input), Presné znenie (textarea), Zdroj (input, hint „napr. e-mail, telefonát, zápisnica"), Záväznosť (select from `AnswerBindingness::cases()` with `label()`), buttons Zrušiť / Uložiť odpoveď.
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: record answer and close question in workspace"`

---

### Task 4: Record decision from a question + Rozhodnutia index polish

**Files:**
- Create: `app/Livewire/Projects/RecordDecisionForm.php` + `resources/views/livewire/projects/record-decision-form.blade.php`
- Modify: `resources/views/livewire/projects/questions-tab.blade.php`, `app/Livewire/Projects/QuestionsTab.php` (listener), `app/Livewire/Pages/DecisionsIndex.php` + `resources/views/livewire/pages/decisions-index.blade.php`
- Test: `tests/Feature/Projects/RecordDecisionFormTest.php`

**Interfaces:**
- Consumes: `Question::decisions()` relation (Task 1), `Decision` model (M1).
- Produces: `RecordDecisionForm` — props `public Question $question`, `bool $open = false`, `string $body = ''`, `string $approvedBy = ''`, `string $rationale = ''`, `?int $answerId = null`; computed `answers()` = `$this->question->answers()->latest('answered_at')->get()`; actions `toggle()`, `save()` — validates `body` required, `approvedBy` required max 255, `rationale` nullable, `answerId` nullable + `Rule::exists('answers', 'id')->where('question_id', $this->question->id)`; creates `Decision` with `project_id => $this->question->project_id`, `question_id => $this->question->id`, `answer_id => $this->answerId`, `approved_at => now()`, `rationale => $this->rationale ?: null`, `recorded_by => auth()->id()`; reset + `resetValidation()` + dispatches `decision-created`. `QuestionsTab` gains `#[On('decision-created')]` listener (same cache bust). `DecisionsIndex::decisions()` becomes `Decision::with(['project', 'question'])->latest('approved_at')->get()`; its view additionally renders `rationale` (when set, gray text „Zdôvodnenie: …") and the linked question body (when set, „Otázka: …").

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/RecordDecisionFormTest.php
use App\Livewire\Pages\DecisionsIndex;
use App\Livewire\Projects\RecordDecisionForm;
use App\Models\{Answer, Decision, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('records a decision linked to the question and answer', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $question = Question::factory()->create();
    $answer = Answer::factory()->for($question)->create();

    Livewire::test(RecordDecisionForm::class, ['question' => $question])
        ->set('body', 'Rozpočet sa upraví podľa usmernenia.')
        ->set('approvedBy', 'Ing. Jana Slušná')
        ->set('rationale', 'Záväzné stanovisko poskytovateľa.')
        ->set('answerId', $answer->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('decision-created');

    $decision = Decision::sole();
    expect($decision->project_id)->toBe($question->project_id)
        ->and($decision->question_id)->toBe($question->id)
        ->and($decision->answer_id)->toBe($answer->id)
        ->and($decision->recorded_by)->toBe($user->id);
});

test('validates required fields and rejects a foreign answer', function () {
    $this->actingAs(User::factory()->create());
    $question = Question::factory()->create();
    $foreignAnswer = Answer::factory()->create();

    Livewire::test(RecordDecisionForm::class, ['question' => $question])
        ->set('answerId', $foreignAnswer->id)
        ->call('save')
        ->assertHasErrors(['body', 'approvedBy', 'answerId']);
});

test('decisions index shows rationale and linked question', function () {
    $this->actingAs(User::factory()->create());
    $question = Question::factory()->create(['body' => 'Súhlasí rozpočet so zmluvou?']);
    Decision::factory()->create([
        'project_id' => $question->project_id,
        'question_id' => $question->id,
        'rationale' => 'Overené audítorom.',
    ]);

    Livewire::test(DecisionsIndex::class)
        ->assertSee('Overené audítorom.')
        ->assertSee('Súhlasí rozpočet so zmluvou?');
});
```

- [ ] **Step 2: Run to verify FAIL.**
- [ ] **Step 3: Implement** per Interfaces. `RecordDecisionForm` view: „Zaznamenať rozhodnutie" toggle button; when open — fields Čo sa rozhodlo (textarea), Kto schválil (input), Zdôvodnenie (textarea, optional), Odpoveď (select „— Bez väzby na odpoveď —" + the question's answers labelled `{{ Str::limit($answer->body, 60) }}`), buttons Zrušiť / Uložiť rozhodnutie. Mount in `questions-tab.blade.php` in the same action row as `RecordAnswerForm` with `wire:key="decision-form-{{ $question->id }}"` — shown for every question regardless of status (a decision may follow a closed question). Below the answers block render the question's decisions: „Rozhodnutie: {{ $decision->body }} — {{ $decision->approved_by }}, {{ $decision->approved_at->format('j. n. Y') }}".
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: record decision from question and richer decisions index"`

---

### Task 5: Gate editing — create gate, add conditions, tick conditions

**Files:**
- Modify: `app/Models/Gate.php`, `app/Models/GateItem.php`, `app/Livewire/Projects/PhasesTab.php`, `resources/views/livewire/projects/phases-tab.blade.php`
- Test: `tests/Feature/GateEditingTest.php` (domain), `tests/Feature/Projects/PhasesTabEditingTest.php` (UI)

**Interfaces:**
- Consumes: `Gate::pass()` / `Project::advancePhase()` (M1), `PhasesTab` error pattern (M2).
- Produces: `Gate::addItem(string $label): GateItem` — throws `DomainException('Brána už prešla, podmienky nemožno meniť.')` when status is `Prejdena`, else `return $this->items()->create(['label' => $label]);`. `GateItem::markMet(bool $met): void` — same guard (`$this->gate->status === GateStatus::Prejdena` → same message), else `$this->update(['is_met' => $met]);`. `PhasesTab` gains props `?int $addingGatePhase = null`, `string $newGateName = ''`, `?int $addingItemGateId = null`, `string $newItemLabel = ''`; actions `startAddGate(int $phase)` / `cancelAddGate()`, `saveGate()` — validates `newGateName` required max 255; if `$this->project->gates()->where('phase', $this->addingGatePhase)->exists()` sets `$error = 'Brána pre túto fázu už existuje.'` and returns; else creates the gate (status defaults `cakajuca`), resets the add-gate props + `resetValidation()`, `unset($this->gates)`; `startAddItem(int $gateId)` / `cancelAddItem()`, `saveItem()` — validates `newItemLabel` required max 255, resolves `$this->project->gates()->findOrFail($this->addingItemGateId)`, calls `addItem()` in try/catch → `$error`; `toggleItem(int $itemId)` — resolves `GateItem::whereHas('gate', fn ($q) => $q->where('project_id', $this->project->id))->findOrFail($itemId)`, calls `markMet(! $item->is_met)` in try/catch → `$error`; every successful action sets `$this->error = null` and busts `unset($this->gates)`.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/GateEditingTest.php
use App\Models\{Gate, GateItem, User};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('addItem creates an unmet condition', function () {
    $gate = Gate::factory()->create();

    $item = $gate->addItem('Rozpočet odsúhlasený');

    expect($item->is_met)->toBeFalse()
        ->and($gate->items()->count())->toBe(1);
});

test('markMet toggles a condition', function () {
    $item = GateItem::factory()->create();

    $item->markMet(true);

    expect($item->fresh()->is_met)->toBeTrue();
});

test('a passed gate rejects new conditions and changes', function () {
    $gate = Gate::factory()->create();
    $item = GateItem::factory()->for($gate)->create(['is_met' => true]);
    $gate->pass(User::factory()->create());

    expect(fn () => $gate->fresh()->addItem('Nová podmienka'))
        ->toThrow(DomainException::class, 'Brána už prešla, podmienky nemožno meniť.')
        ->and(fn () => $item->fresh()->markMet(false))
        ->toThrow(DomainException::class, 'Brána už prešla, podmienky nemožno meniť.');
});
```

```php
// tests/Feature/Projects/PhasesTabEditingTest.php
use App\Enums\ProjectPhase;
use App\Livewire\Projects\PhasesTab;
use App\Models\{Gate, GateItem, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('creates a gate for a phase', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('startAddGate', 3)
        ->set('newGateName', 'Brána 1 – Kompletnosť podkladov')
        ->call('saveGate')
        ->assertSet('error', null)
        ->assertSet('addingGatePhase', null);

    expect($project->gates()->sole())
        ->phase->toBe(3)
        ->name->toBe('Brána 1 – Kompletnosť podkladov');
});

test('rejects a duplicate gate for the same phase', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    Gate::factory()->for($project)->create(['phase' => 3]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('startAddGate', 3)
        ->set('newGateName', 'Duplikát')
        ->call('saveGate')
        ->assertSet('error', 'Brána pre túto fázu už existuje.');

    expect($project->gates()->count())->toBe(1);
});

test('adds a condition to a waiting gate', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $gate = Gate::factory()->for($project)->create();

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('startAddItem', $gate->id)
        ->set('newItemLabel', 'LV nie starší ako 3 mesiace')
        ->call('saveItem')
        ->assertSet('error', null);

    expect($gate->items()->sole()->label)->toBe('LV nie starší ako 3 mesiace');
});

test('toggles a condition and surfaces the passed-gate error', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $gate = Gate::factory()->for($project)->create();
    $item = GateItem::factory()->for($gate)->create();

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('toggleItem', $item->id)
        ->assertSet('error', null);
    expect($item->fresh()->is_met)->toBeTrue();

    $gate->fresh()->pass($user);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('toggleItem', $item->id)
        ->assertSet('error', 'Brána už prešla, podmienky nemožno meniť.');
});

test('cannot edit gates of another project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $foreignItem = GateItem::factory()->create();

    expect(fn () => Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('toggleItem', $foreignItem->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
```

- [ ] **Step 2: Run to verify FAIL.**
- [ ] **Step 3: Implement** per Interfaces (domain first, then component). View changes in `phases-tab.blade.php`: for a `cakajuca` gate render each item as a clickable row `wire:click="toggleItem({{ $item->id }})"` with `wire:key="gate-item-{{ $item->id }}"` showing a checkbox-style box (`✓` emerald when met, empty ring when not) + label — keep the read-only `✓`/`○` text rendering for `prejdena` gates; under a `cakajuca` gate's items an „Pridať podmienku" button toggling the inline mini-form (input + Zrušiť/Uložiť) when `$addingItemGateId === $gate->id`; for a phase with no gate an „Pridať bránu" button (`startAddGate({{ $phase->value }})`) with inline mini-form (input for name + Zrušiť/Uložiť) when `$addingGatePhase === $phase->value`. All new interactive elements carry `wire:key`.
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: gate editing — create gate, add and tick conditions"`

---

### Task 6: Task creation from the workspace

**Files:**
- Modify: `app/Livewire/Tasks/CreateTaskModal.php`, `app/Livewire/Projects/TasksTab.php`, `resources/views/livewire/projects/tasks-tab.blade.php`, `resources/views/livewire/pages/project-show.blade.php`
- Test: `tests/Feature/Projects/TasksTabCreateTest.php`

**Interfaces:**
- Consumes: `Tasks\CreateTaskModal` (M2) — its `open()` action and the event it dispatches after save (verify the exact name in the component; expected `task-created`).
- Produces: `CreateTaskModal::open(?int $projectId = null)` — when `$projectId` given, presets `$this->projectId = $projectId` (rest of behaviour unchanged). `TasksTab` gains header button „Nová úloha" — `wire:click="$dispatch('open-create-task', { projectId: {{ $project->id }} })"` — and listener `#[On('task-created')] public function refreshTasks(): void { unset($this->tasks); }`. `project-show.blade.php` mounts `<livewire:tasks.create-task-modal />` once at the bottom (same as `dashboard.blade.php` does).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/TasksTabCreateTest.php
use App\Livewire\Projects\TasksTab;
use App\Livewire\Tasks\CreateTaskModal;
use App\Models\{Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('open with a project preselects it', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(CreateTaskModal::class)
        ->call('open', $project->id)
        ->assertSet('open', true)
        ->assertSet('projectId', $project->id);
});

test('open without a project keeps the selector empty', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateTaskModal::class)
        ->call('open')
        ->assertSet('open', true)
        ->assertSet('projectId', null);
});

test('tasks tab renders the new task button and workspace mounts the modal', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();

    Livewire::test(TasksTab::class, ['project' => $project])
        ->assertSee('Nová úloha');

    $this->get(route('projekty.show', $project))
        ->assertSeeLivewire(CreateTaskModal::class);
});
```

- [ ] **Step 2: Run to verify FAIL.**

Note: if `CreateTaskModal::$projectId` is typed `?int` with a string default or `open()` already takes parameters, adapt minimally — the contract that matters is the three assertions above. Check the event dispatched by its `save()`; if it is not literally `task-created`, use the actual name in `TasksTab`'s `#[On(...)]`.

- [ ] **Step 3: Implement** per Interfaces. The „Nová úloha" button goes in the tasks-tab card header, right-aligned like the „Nový dokument" button in `documents-tab.blade.php`.
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: create task from project workspace"`

---

### Task 7: Demo seed polish + manual check

**Files:**
- Modify: `database/seeders/DemoSeeder.php`
- Test: extend `tests/Feature/DemoSeederTest.php`

**Interfaces:**
- Consumes: domain methods `ProjectTask::complete()`, `Question` answer flow, `Gate` (M1); seeder structure (`seedMaleHosteDetail()`, `seedHronskaDubravaDetail()`, `seedAdditionalTasksAndRisks()`).
- Produces: demo data exercises the whole M4 surface: (a) Hronská Dúbrava's question „Je energetické hodnotenie priložené k žiadosti o platbu?" gets an `Answer` (answered_by `'Ing. Krajčí, stavebný dozor'`, `answered_at => now()->subDay()`, body `'Áno, energetické hodnotenie je súčasťou prílohy č. 2 žiadosti o platbu.'`, source `'E-mail'`, bindingness `zavazne`, recorded_by Denis) — the created-event flips the question to `Zodpovedaná`; the existing Hronská Dúbrava decision linked to that question gets `answer_id` of this answer; (b) Malé Hoste gets a gate at phase 4: name `'Brána 1 – Kompletnosť podkladov'` with items `'PD v aktuálnej verzii potvrdená'` (`is_met => true`) and `'Aktuálny list vlastníctva priložený'` (`is_met => false`) — status stays `cakajuca` (tells the demo story: project blocked on LV); (c) Tornaľa gets one completed task: create `'Skontrolovať prílohy monitorovacej správy'` (priority stredna, due `today()->subDays(5)`, required_evidence `'Kontrolný protokol'`) then call `->complete(evidenceNote: 'Protokol z kontroly 12. 8. 2026 založený v spise.')`. Seeder remains idempotent under `migrate:fresh --seed`.

- [ ] **Step 1: Write the failing test** — add to `tests/Feature/DemoSeederTest.php` (keep existing tests untouched; fix any count-based assertions there that the new rows break — read the file first):

```php
test('demo seed covers the process layer', function () {
    $this->seed();

    expect(App\Models\Answer::count())->toBeGreaterThan(0)
        ->and(App\Models\Question::where('status', App\Enums\QuestionStatus::Zodpovedana)->count())->toBeGreaterThan(0)
        ->and(App\Models\Decision::whereNotNull('answer_id')->count())->toBeGreaterThan(0)
        ->and(App\Models\ProjectTask::where('status', App\Enums\TaskStatus::Hotova)->whereNotNull('evidence_note')->count())->toBeGreaterThan(0)
        ->and(App\Models\Gate::where('status', App\Enums\GateStatus::Cakajuca)->whereHas('items', fn ($q) => $q->where('is_met', false))->count())->toBeGreaterThan(0);
});
```

- [ ] **Step 2: Run to verify FAIL.**
- [ ] **Step 3: Implement** per Interfaces inside the existing per-project seed methods.
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Visual check** — `php artisan migrate:fresh --seed`; open the app → PRJ-001 → Požiadavky: create a question, record an answer, record a decision, close the question; Fázy: tick the LV condition, pass the gate, advance the phase; Úlohy: create a task via „Nová úloha", complete it with an evidence note; Rozhodnutia (global): rationale and linked questions visible.
- [ ] **Step 6: Commit** — `git add -A && git commit -m "feat: demo seed for process layer (answers, decision links, gate, evidence)"`

---

## Out of scope (later milestones)

AI layer entirely (inbox classification, cross-checks, question drafting, prioritisation — M5); gate templates per phase from the poznámky checklists (v2); editing/deleting questions, answers, decisions (append-only by design); task status transitions beyond completion (Čaká toggling); `GateStatus::Zamietnuta` and `gate_items.evidence` remain unused until a concrete workflow needs them.
