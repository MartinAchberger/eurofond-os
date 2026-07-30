# EUROFOND OS — Milestone 2: PM UI (Livewire) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** The PM-facing UI per the dashboard mockup — app shell with Slovak sidebar navigation, dashboard (stat cards, portfolio, priorities, audit history, new-task modal), project workspace with tabs, Dnes view and global read-only pages.

**Architecture:** Livewire 3 class components. Full-page components live in `app/Livewire/Pages/`, reusable panels in `app/Livewire/Dashboard/` and `app/Livewire/Projects/`. All pages share one Blade layout (`layouts.eurofund`) with the mockup's sidebar + topbar. Data access goes through the Milestone-1 models; domain mutations only via domain methods (`ProjectTask::complete()`, `Project::advancePhase()`), never raw status writes.

**Tech Stack:** Laravel 13.8 (see plan-1 Deviations), Livewire 3, Tailwind 3, Pest 5 + `Livewire::test()`. No new composer/npm packages.

**Design reference:** `docs/reference/mockup-dashboard.png` — **every implementer building Blade markup must Read this image first** and match it: light `bg-gray-100` canvas, white cards `rounded-xl border border-gray-200 shadow-sm`, blue accent `blue-600`, sidebar with blue active item, status chips, colored health dots, red near deadlines.

## Global Constraints

- All user-facing strings in Slovak WITH diacritics, hardcoded in Blade (no i18n framework — YAGNI). Code identifiers in English.
- Domain rules must never be bypassed in UI: task completion only via `ProjectTask::complete()`, phase advance only via `Project::advancePhase()`; catch `DomainException` and show its message.
- Livewire mutations validate input (`$this->validate()`) — mass-assignment from raw request data is forbidden.
- Route names and URLs exactly as defined in Task 1 (Slovak URLs).
- Tests: Pest feature tests using `Livewire::test()` / `actingAs()`. Run `npm run build` once before the suite if the Vite manifest is missing.
- Demo data (`php artisan migrate:fresh --seed`) must look right on every page — verify visually via `php artisan serve` where a step says so.

## Component & Route Map (defined in Task 1, used by all tasks)

| Route name | URL | Page component (`App\Livewire\Pages\`) |
|---|---|---|
| `dashboard` | `/dashboard` | `Dashboard` |
| `dnes` | `/dnes` | `Dnes` |
| `inbox` | `/inbox` | `InboxPage` |
| `projekty.index` | `/projekty` | `ProjectsIndex` |
| `projekty.show` | `/projekty/{project}` | `ProjectShow` |
| `dokumenty.index` | `/dokumenty` | `DocumentsIndex` |
| `poziadavky.index` | `/poziadavky` | `QuestionsIndex` |
| `ulohy.index` | `/ulohy` | `TasksIndex` |
| `rizika.index` | `/rizika` | `RisksIndex` |
| `rozhodnutia.index` | `/rozhodnutia` | `DecisionsIndex` |
| `nastavenia` | `/nastavenia` | `SettingsPage` |

Sidebar items (order, label → route): Dashboard→`dashboard`, Dnes→`dnes`, Inbox→`inbox` (badge: count of InboxItem status `nove`), Projekty→`projekty.index`, Dokumenty→`dokumenty.index`, Požiadavky→`poziadavky.index`, Úlohy→`ulohy.index`, Riziká→`rizika.index`, Rozhodnutia→`rozhodnutia.index`, Nastavenia→`nastavenia`. (Academy is out of scope — v2.)

---

### Task 1: App shell — layout, routes, page stubs

**Files:**
- Create: `resources/views/layouts/eurofund.blade.php`
- Create: `app/Livewire/Pages/Dashboard.php`, `Dnes.php`, `InboxPage.php`, `ProjectsIndex.php`, `ProjectShow.php`, `DocumentsIndex.php`, `QuestionsIndex.php`, `TasksIndex.php`, `RisksIndex.php`, `DecisionsIndex.php`, `SettingsPage.php` + one Blade view each under `resources/views/livewire/pages/` (kebab-case: `dashboard.blade.php`, `dnes.blade.php`, `inbox-page.blade.php`, `projects-index.blade.php`, `project-show.blade.php`, `documents-index.blade.php`, `questions-index.blade.php`, `tasks-index.blade.php`, `risks-index.blade.php`, `decisions-index.blade.php`, `settings-page.blade.php`)
- Modify: `routes/web.php` (replace the Breeze dashboard/profile routes block with the full route map; keep `profile` route), `resources/views/welcome.blade.php` route in `routes/web.php` → replace `/` with `Route::redirect('/', '/dashboard');` and delete `welcome.blade.php`
- Modify: `.env.example` (`APP_NAME="EUROFOND OS"`, `APP_LOCALE=sk`, `APP_FAKER_LOCALE=sk_SK`), `.env` likewise
- Test: `tests/Feature/AppShellTest.php`

**Interfaces:**
- Produces: layout view `layouts.eurofund` (plain Blade with `{{ $slot }}`); every page component uses `#[Layout('layouts.eurofund')]` and `#[Title('… — EUROFUND OS')]`. All route names from the Component & Route Map exist. Each stub page renders `<h1>` with its Slovak name (Dashboard, Dnes, Inbox, Projekty, Dokumenty, Požiadavky, Úlohy, Riziká, Rozhodnutia, Nastavenia; ProjectShow renders `{{ $project->code }} {{ $project->name }}`).
- `ProjectShow` signature: `public Project $project;` (route-model bound), `#[Url] public string $tab = 'prehlad';`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/AppShellTest.php
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login from app pages', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('root redirects to dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});

test('sidebar shows all slovak sections for authenticated user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['EUROFUND OS', 'Dashboard', 'Dnes', 'Inbox', 'Projekty', 'Dokumenty', 'Požiadavky', 'Úlohy', 'Riziká', 'Rozhodnutia', 'Nastavenia']);
});

test('every page route renders', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(route($route))
        ->assertOk();
})->with(['dnes', 'inbox', 'projekty.index', 'dokumenty.index', 'poziadavky.index', 'ulohy.index', 'rizika.index', 'rozhodnutia.index', 'nastavenia']);

test('project show renders project code', function () {
    $project = Project::factory()->create(['code' => 'PRJ-777']);
    $this->actingAs(User::factory()->create())
        ->get(route('projekty.show', $project))
        ->assertOk()
        ->assertSee('PRJ-777');
});
```

- [ ] **Step 2: Run test to verify it fails** — `./vendor/bin/pest tests/Feature/AppShellTest.php` — Expected: FAIL (routes missing).
- [ ] **Step 3: Read the mockup** — Read `docs/reference/mockup-dashboard.png` before writing the layout.
- [ ] **Step 4: Implement the layout**

`resources/views/layouts/eurofund.blade.php` — structure (fill in real markup, match mockup):

```blade
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'EUROFUND OS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900">
<div class="flex min-h-screen">
    <aside class="w-60 shrink-0 border-r border-gray-200 bg-white flex flex-col">
        <div class="flex items-center gap-2 px-5 py-5">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-blue-600 font-bold text-white">E</span>
            <span class="text-lg font-bold tracking-tight">EUROFUND OS</span>
        </div>
        <nav class="flex-1 space-y-1 px-3">
            @foreach ([
                ['Dashboard', 'dashboard'], ['Dnes', 'dnes'], ['Inbox', 'inbox'],
                ['Projekty', 'projekty.index'], ['Dokumenty', 'dokumenty.index'],
                ['Požiadavky', 'poziadavky.index'], ['Úlohy', 'ulohy.index'],
                ['Riziká', 'rizika.index'], ['Rozhodnutia', 'rozhodnutia.index'],
                ['Nastavenia', 'nastavenia'],
            ] as [$label, $routeName])
                <a href="{{ route($routeName) }}"
                   class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium
                          {{ request()->routeIs($routeName.'*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <span>{{ $label }}</span>
                    @if ($routeName === 'inbox')
                        @php $noveCount = \App\Models\InboxItem::where('status', \App\Enums\InboxItemStatus::Nove)->count(); @endphp
                        @if ($noveCount > 0)
                            <span class="rounded-full bg-blue-100 px-2 text-xs font-semibold text-blue-700">{{ $noveCount }}</span>
                        @endif
                    @endif
                </a>
            @endforeach
        </nav>
        <div class="border-t border-gray-200 p-4 text-xs text-gray-500">
            <p class="font-semibold text-gray-700">Lokálne / bezpečné</p>
            <p>Všetky dáta sú uložené lokálne.</p>
        </div>
    </aside>
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-4 border-b border-gray-200 bg-white px-6 py-3">
            <input type="search" placeholder="Vyhľadajte projekty, dokumenty, úlohy…"
                   class="w-full max-w-md rounded-lg border-gray-200 bg-gray-50 text-sm" disabled>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium">{{ auth()->user()?->name }}</span>
                <livewire:layout-logout />
            </div>
        </header>
        <main class="flex-1 p-6">{{ $slot }}</main>
    </div>
</div>
@livewireScripts
</body>
</html>
```

Logout: reuse Breeze's `App\Livewire\Actions\Logout` — create a tiny inline component `app/Livewire/LayoutLogout.php` with a `logout(Logout $logout)` action rendering a button „Odhlásiť sa“. (If `@livewireScripts` duplicates auto-injection, drop it — Livewire 3 auto-injects.)

- [ ] **Step 5: Implement page stubs + routes**

Each page component follows this pattern (Dashboard shown; the others identical with their own view/title/heading):

```php
// app/Livewire/Pages/Dashboard.php
namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.eurofund')]
#[Title('Dashboard — EUROFUND OS')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.pages.dashboard');
    }
}
```

`ProjectShow` additionally:

```php
use App\Models\Project;
use Livewire\Attributes\Url;

public Project $project;

#[Url]
public string $tab = 'prehlad';
```

Stub views: `<div><h1 class="text-2xl font-bold">Dashboard</h1></div>` etc. `routes/web.php`:

```php
use App\Livewire\Pages\{Dashboard, Dnes, InboxPage, ProjectsIndex, ProjectShow, DocumentsIndex, QuestionsIndex, TasksIndex, RisksIndex, DecisionsIndex, SettingsPage};

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/dnes', Dnes::class)->name('dnes');
    Route::get('/inbox', InboxPage::class)->name('inbox');
    Route::get('/projekty', ProjectsIndex::class)->name('projekty.index');
    Route::get('/projekty/{project}', ProjectShow::class)->name('projekty.show');
    Route::get('/dokumenty', DocumentsIndex::class)->name('dokumenty.index');
    Route::get('/poziadavky', QuestionsIndex::class)->name('poziadavky.index');
    Route::get('/ulohy', TasksIndex::class)->name('ulohy.index');
    Route::get('/rizika', RisksIndex::class)->name('rizika.index');
    Route::get('/rozhodnutia', DecisionsIndex::class)->name('rozhodnutia.index');
    Route::get('/nastavenia', SettingsPage::class)->name('nastavenia');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
```

Delete `resources/views/dashboard.blade.php` and `resources/views/welcome.blade.php` (both replaced). Keep Breeze auth tests passing: `AuthenticationTest` expects redirect to `route('dashboard', absolute: false)` after login — unchanged.

- [ ] **Step 6: Build assets & run tests** — `npm run build && ./vendor/bin/pest` — Expected: all PASS (fix any Breeze test that referenced the deleted welcome view).
- [ ] **Step 7: Commit** — `git add -A && git commit -m "feat: app shell with sidebar layout, slovak routes and page stubs"`

---

### Task 2: Dashboard stat cards

**Files:**
- Create: `app/Livewire/Dashboard/StatCards.php`, `resources/views/livewire/dashboard/stat-cards.blade.php`
- Modify: `resources/views/livewire/pages/dashboard.blade.php` (embed `<livewire:dashboard.stat-cards />` at top)
- Test: `tests/Feature/Dashboard/StatCardsTest.php`

**Interfaces:**
- Consumes: models + enums from M1.
- Produces: component `App\Livewire\Dashboard\StatCards` exposing computed properties `activeProjects`, `upcomingDeadlines`, `openRisks`, `waitingOnClient` (all `int`).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Dashboard/StatCardsTest.php
use App\Enums\ProjectPhase;
use App\Enums\RiskStatus;
use App\Enums\TaskStatus;
use App\Livewire\Dashboard\StatCards;
use App\Models\{Project, ProjectTask, Risk, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('stat cards count active projects, deadlines, risks and waiting tasks', function () {
    $this->actingAs(User::factory()->create());
    Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov, 'next_deadline' => now()->addDays(5)]);
    Project::factory()->create(['phase' => ProjectPhase::Udrzatelnost, 'next_deadline' => now()->addDays(3)]);
    Project::factory()->create(['phase' => ProjectPhase::Realizacia, 'next_deadline' => now()->addDays(30)]);
    Risk::factory()->create();                                    // default otvorene
    Risk::factory()->create(['status' => RiskStatus::Uzavrete]);
    ProjectTask::factory()->create(['status' => TaskStatus::Caka]);
    ProjectTask::factory()->create();                             // otvorena

    Livewire::test(StatCards::class)
        ->assertSet('activeProjects', 2)      // phase < 11
        ->assertSet('upcomingDeadlines', 2)   // within 14 days (incl. the udrzatelnost one)
        ->assertSet('openRisks', 1)
        ->assertSet('waitingOnClient', 1)
        ->assertSee('Aktívne projekty')
        ->assertSee('Blížiace sa termíny')
        ->assertSee('Otvorené riziká')
        ->assertSee('Čaká sa na klienta');
});
```

- [ ] **Step 2: Run test to verify it fails** — Expected: FAIL (class missing).
- [ ] **Step 3: Implement**

```php
// app/Livewire/Dashboard/StatCards.php
namespace App\Livewire\Dashboard;

use App\Enums\ProjectPhase;
use App\Enums\RiskStatus;
use App\Enums\TaskStatus;
use App\Models\{Project, ProjectTask, Risk};
use Livewire\Component;

class StatCards extends Component
{
    public int $activeProjects;
    public int $upcomingDeadlines;
    public int $openRisks;
    public int $waitingOnClient;

    public function mount(): void
    {
        $this->activeProjects = Project::where('phase', '<', ProjectPhase::Ukoncenie->value)->count();
        $this->upcomingDeadlines = Project::whereBetween('next_deadline', [today(), today()->addDays(14)])->count();
        $this->openRisks = Risk::where('status', RiskStatus::Otvorene)->count();
        $this->waitingOnClient = ProjectTask::where('status', TaskStatus::Caka)->count();
    }

    public function render()
    {
        return view('livewire.dashboard.stat-cards');
    }
}
```

View: grid `grid gap-4 md:grid-cols-2 xl:grid-cols-4`; each card = white rounded-xl with a colored icon square (blue/green/orange/violet per mockup), label, big number, and footer link („Zobraziť všetky projekty →“ → `projekty.index`, „Zobraziť termíny →“ → `dnes`, „Zobraziť riziká →“ → `rizika.index`, „Zobraziť požiadavky →“ → `ulohy.index`).

- [ ] **Step 4: Run test** — Expected: PASS. Run full suite once.
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: dashboard stat cards"`

---

### Task 3: Dashboard portfolio table + audit history

**Files:**
- Create: `app/Livewire/Dashboard/PortfolioTable.php` + `resources/views/livewire/dashboard/portfolio-table.blade.php`
- Create: `app/Livewire/Dashboard/AuditHistory.php` + `resources/views/livewire/dashboard/audit-history.blade.php`
- Modify: `resources/views/livewire/pages/dashboard.blade.php` (grid: portfolio left 2/3, right column placeholder for Task 4; audit history full-width below)
- Test: `tests/Feature/Dashboard/PortfolioTableTest.php`

**Interfaces:**
- Produces: `PortfolioTable` computed `projects` (active projects ordered by `next_deadline`); `AuditHistory` computed `activities` (latest 6 `Spatie\Activitylog\Models\Activity` with causer+subject eager-loaded).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Dashboard/PortfolioTableTest.php
use App\Enums\ProjectPhase;
use App\Livewire\Dashboard\{AuditHistory, PortfolioTable};
use App\Models\{Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('portfolio lists active projects with status, phase and health', function () {
    $this->actingAs(User::factory()->create());
    Project::factory()->create([
        'code' => 'PRJ-001', 'name' => 'Malé Hoste',
        'status_label' => 'Čaká na PD', 'phase' => ProjectPhase::TechnickaFinancnaKontrola,
        'health' => 'dobre', 'next_deadline' => now()->addDays(3),
    ]);
    Project::factory()->create(['code' => 'PRJ-099', 'phase' => ProjectPhase::Udrzatelnost]);

    Livewire::test(PortfolioTable::class)
        ->assertSee('PRJ-001')->assertSee('Malé Hoste')->assertSee('Čaká na PD')
        ->assertSee('Technická a finančná kontrola')
        ->assertDontSee('PRJ-099'); // inactive phase 12
});

test('audit history shows recent activity', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create(['code' => 'PRJ-001']);
    $project->update(['main_blocker' => 'Čaká sa na PD']);

    Livewire::test(AuditHistory::class)->assertSee('Auditná história');
});
```

- [ ] **Step 2: Run to verify FAIL.**
- [ ] **Step 3: Implement**

```php
// app/Livewire/Dashboard/PortfolioTable.php
namespace App\Livewire\Dashboard;

use App\Enums\ProjectPhase;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PortfolioTable extends Component
{
    #[Computed]
    public function projects()
    {
        return Project::with('client')
            ->where('phase', '<', ProjectPhase::Ukoncenie->value)
            ->orderBy('next_deadline')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.portfolio-table');
    }
}
```

```php
// app/Livewire/Dashboard/AuditHistory.php — computed:
#[Computed]
public function activities()
{
    return \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])->latest()->limit(6)->get();
}
```

Portfolio view: card „Portfólio projektov“ with link „Zobraziť všetky“ → `projekty.index`; table columns Kód projektu / Názov projektu / Status / Fáza / Blížiaci sa termín / Zdravie. Row: code muted, name is `<a href="{{ route('projekty.show', $project) }}" class="font-medium hover:text-blue-600">`, status chip `rounded-md bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800`, phase `$project->phase->label()`, deadline `$project->next_deadline?->format('j. n. Y')` with `text-red-600 font-medium` when `$project->next_deadline?->lte(today()->addDays(7))`, health dot `h-2.5 w-2.5 rounded-full` + `bg-emerald-500|bg-amber-400|bg-red-500` via `match($project->health->value)` + label `$project->health->label()`. Empty state: „Žiadne aktívne projekty.“

Audit view: card „Auditná história“; rows: `{{ $activity->created_at->format('j. n. Y H:i') }}` · `{{ $activity->causer?->name ?? 'Systém' }}` · Slovak event via `match($activity->event){'created' => 'Vytvorené', 'updated' => 'Upravené', default => $activity->event}` + `class_basename($activity->subject_type)` + subject identifier (`$activity->subject?->code ?? $activity->subject?->title ?? $activity->subject?->name ?? '#'.$activity->subject_id`). Link „Zobraziť celú históriu →“ (href `#`, disabled — full log page is out of scope).

- [ ] **Step 4: Run tests** — PASS; run full suite.
- [ ] **Step 5: Visual check** — `php artisan migrate:fresh --seed && php artisan serve` → dashboard matches mockup layout (portfolio populated with the 4 demo projects).
- [ ] **Step 6: Commit** — `git add -A && git commit -m "feat: dashboard portfolio table and audit history"`

---

### Task 4: Dnešné priority + Nová úloha modal

**Files:**
- Create: `app/Livewire/Dashboard/TodayPriorities.php` + `resources/views/livewire/dashboard/today-priorities.blade.php`
- Create: `app/Livewire/Tasks/CreateTaskModal.php` + `resources/views/livewire/tasks/create-task-modal.blade.php`
- Modify: `resources/views/livewire/pages/dashboard.blade.php` (right column = TodayPriorities; header row gets button „Nová úloha“ opening the modal via Livewire event `open-create-task`)
- Test: `tests/Feature/Dashboard/TodayPrioritiesTest.php`, `tests/Feature/Tasks/CreateTaskModalTest.php`

**Interfaces:**
- Produces: `TodayPriorities` computed `tasks` (open tasks with due dates, soonest first, limit 5; label helper `dueLabel(ProjectTask $task): string` returning `Dnes`/`Zajtra`/`j. n. Y`). `CreateTaskModal` public props `bool $open = false`, `string $title = ''`, `?int $projectId = null`, `?int $assigneeId = null`, `string $priority = 'stredna'`, `?string $dueAt = null`, `string $note = ''`, `string $requiredEvidence = ''`; listener `#[On('open-create-task')] public function open()`; action `save()` validating (`title` required max 255, `projectId` nullable exists:projects,id, `assigneeId` nullable exists:users,id, `priority` in TaskPriority values, `dueAt` nullable date) then `ProjectTask::create([...])`, dispatching `task-created`, closing the modal.

- [ ] **Step 1: Write the failing tests**

```php
// tests/Feature/Dashboard/TodayPrioritiesTest.php
use App\Enums\TaskStatus;
use App\Livewire\Dashboard\TodayPriorities;
use App\Models\{ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('priorities list open tasks by due date with Dnes/Zajtra labels', function () {
    $this->actingAs(User::factory()->create());
    ProjectTask::factory()->create(['title' => 'Dnešná úloha', 'due_at' => today()]);
    ProjectTask::factory()->create(['title' => 'Zajtrajšia úloha', 'due_at' => today()->addDay()]);
    ProjectTask::factory()->create(['title' => 'Hotová vec', 'due_at' => today(), 'status' => TaskStatus::Hotova, 'completed_at' => now()]);

    Livewire::test(TodayPriorities::class)
        ->assertSeeInOrder(['Dnešná úloha', 'Zajtrajšia úloha'])
        ->assertSee('Dnes')->assertSee('Zajtra')
        ->assertDontSee('Hotová vec');
});
```

```php
// tests/Feature/Tasks/CreateTaskModalTest.php
use App\Livewire\Tasks\CreateTaskModal;
use App\Models\{Project, ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('create task modal validates and creates a task', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $this->actingAs($user);

    Livewire::test(CreateTaskModal::class)
        ->call('save')
        ->assertHasErrors(['title' => 'required']);

    Livewire::test(CreateTaskModal::class)
        ->set('title', 'Skontrolovať PD a doplniť podklady')
        ->set('projectId', $project->id)
        ->set('assigneeId', $user->id)
        ->set('priority', 'vysoka')
        ->set('dueAt', today()->addDays(5)->toDateString())
        ->set('note', 'Podľa checklistu.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('open', false)
        ->assertDispatched('task-created');

    expect(ProjectTask::count())->toBe(1)
        ->and(ProjectTask::first()->title)->toBe('Skontrolovať PD a doplniť podklady')
        ->and(ProjectTask::first()->priority->value)->toBe('vysoka');
});

test('invalid priority is rejected', function () {
    $this->actingAs(User::factory()->create());
    Livewire::test(CreateTaskModal::class)
        ->set('title', 'X')->set('priority', 'extra')
        ->call('save')
        ->assertHasErrors(['priority']);
});
```

- [ ] **Step 2: Run to verify FAIL.**
- [ ] **Step 3: Implement**

`TodayPriorities` computed:

```php
#[Computed]
public function tasks()
{
    return ProjectTask::with('project')
        ->where('status', '!=', TaskStatus::Hotova)
        ->whereNotNull('due_at')
        ->orderBy('due_at')
        ->limit(5)
        ->get();
}

public function dueLabel(ProjectTask $task): string
{
    return match (true) {
        $task->due_at->isToday() => 'Dnes',
        $task->due_at->isTomorrow() => 'Zajtra',
        default => $task->due_at->format('j. n. Y'),
    };
}
```

`CreateTaskModal::save()`:

```php
public function save(): void
{
    $validated = $this->validate([
        'title' => ['required', 'string', 'max:255'],
        'projectId' => ['nullable', 'integer', 'exists:projects,id'],
        'assigneeId' => ['nullable', 'integer', 'exists:users,id'],
        'priority' => ['required', Rule::enum(TaskPriority::class)],
        'dueAt' => ['nullable', 'date'],
        'note' => ['nullable', 'string'],
        'requiredEvidence' => ['nullable', 'string'],
    ]);

    ProjectTask::create([
        'title' => $validated['title'],
        'project_id' => $validated['projectId'],
        'assignee_id' => $validated['assigneeId'],
        'priority' => $validated['priority'],
        'due_at' => $validated['dueAt'],
        'note' => $validated['note'] ?: null,
        'required_evidence' => $validated['requiredEvidence'] ?: null,
    ]);

    $this->reset('open', 'title', 'projectId', 'assigneeId', 'dueAt', 'note', 'requiredEvidence');
    $this->priority = 'stredna';
    $this->dispatch('task-created');
}
```

Modal view mirrors the mockup „Nová úloha“ card: fields Názov úlohy, Projekt (`<select>` of `Project::orderBy('code')` — „— bez projektu —“ option), Zodpovedná osoba (`<select>` of users), Priorita (select from `TaskPriority::cases()` with `label()`), Termín (`input type=date`), Poznámka (textarea), Požadovaný dôkaz (input). Buttons Zrušiť / Vytvoriť úlohu (blue). Wrap in fixed overlay shown when `$open`. Priorities panel: card „Dnešné priority“ + „Zobraziť všetky“ → `ulohy.index`; each row: icon, bold title, project code+name muted, right-aligned label from `dueLabel()` (red when Dnes). Dashboard page header: `<h1>` hidden per mockup; add „Nová úloha“ button (top-right of priorities card header) with `wire:click="$dispatch('open-create-task')"`; TodayPriorities listens? No — mount `<livewire:tasks.create-task-modal />` once on the dashboard page and let the button dispatch globally.

- [ ] **Step 4: Run tests** — PASS; full suite; refresh `task-created`: PortfolioTable/StatCards need no live refresh (page reload is fine — YAGNI).
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: today priorities panel and new task modal"`

---

### Task 5: Projects index

**Files:**
- Modify: `app/Livewire/Pages/ProjectsIndex.php`, `resources/views/livewire/pages/projects-index.blade.php`
- Test: `tests/Feature/Pages/ProjectsIndexTest.php`

**Interfaces:**
- Produces: `ProjectsIndex` with `#[Url] public string $q = '';` and computed `projects` (all projects, filtered by `q` against code/name/client name, ordered by code). Reuses the same row visual language as the portfolio table (status chip, phase label, deadline, health dot).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Pages/ProjectsIndexTest.php
use App\Livewire\Pages\ProjectsIndex;
use App\Models\{Client, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('projects index lists and filters projects', function () {
    $this->actingAs(User::factory()->create());
    $obec = Client::factory()->create(['name' => 'Obec Malé Hoste']);
    Project::factory()->create(['code' => 'PRJ-001', 'name' => 'Malé Hoste', 'client_id' => $obec->id]);
    Project::factory()->create(['code' => 'PRJ-002', 'name' => 'Tornaľa']);

    Livewire::test(ProjectsIndex::class)
        ->assertSee('PRJ-001')->assertSee('PRJ-002')
        ->set('q', 'Malé')
        ->assertSee('PRJ-001')->assertDontSee('PRJ-002');
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement**

```php
#[Url]
public string $q = '';

#[Computed]
public function projects()
{
    return Project::with('client')
        ->when($this->q !== '', function ($query) {
            $query->where(fn ($w) => $w
                ->where('code', 'like', "%{$this->q}%")
                ->orWhere('name', 'like', "%{$this->q}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->q}%")));
        })
        ->orderBy('code')
        ->get();
}
```

View: heading „Projekty“, search input `wire:model.live.debounce.300ms="q"` placeholder „Hľadať projekt…“, table with columns Kód / Názov / Žiadateľ / Status / Fáza / Termín / Zdravie; name links to `projekty.show`. Empty state „Žiadne projekty nezodpovedajú hľadaniu.“

- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: projects index with search"`

---

### Task 6: Project workspace shell + Prehľad tab

**Files:**
- Modify: `app/Livewire/Pages/ProjectShow.php`, `resources/views/livewire/pages/project-show.blade.php`
- Create: `app/Livewire/Projects/OverviewTab.php` + `resources/views/livewire/projects/overview-tab.blade.php`
- Test: `tests/Feature/Pages/ProjectShowTest.php`

**Interfaces:**
- Consumes: `ProjectShow::$project`, `ProjectShow::$tab` from Task 1.
- Produces: workspace header (code + name, status chip, „Vlastník: {name}“, client, budget/grant formatted `number_format(..., 2, ',', ' ') . ' €'`); tab nav for keys `prehlad|dokumenty|poziadavky|ulohy|rizika|fazy` (labels Prehľad/Dokumenty/Požiadavky/Úlohy/Riziká/Fázy) setting `$tab` via `wire:click="$set('tab', '...')"`. Each tab renders its child component with `:project="$project"` (children created in Tasks 6–9; until then render only when class exists via `@if (class_exists(...))` is NOT allowed — instead ProjectShow renders only the tabs implemented so far; each later task adds its `@elseif` branch). `OverviewTab` (`public Project $project;`) shows four panels: Zdroj pravdy (current document versions), Chýbajúce podklady (open tasks having `required_evidence`), Otvorené otázky (status `otvorena`), Kontrolná brána (gate for current phase + risks count fallback text).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Pages/ProjectShowTest.php
use App\Enums\QuestionStatus;
use App\Livewire\Pages\ProjectShow;
use App\Livewire\Projects\OverviewTab;
use App\Models\{Document, DocumentVersion, Gate, GateItem, Project, ProjectTask, Question, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('workspace header shows project identity and tabs', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['code' => 'PRJ-005', 'name' => 'Hronská Dúbrava', 'status_label' => 'Rozpočet / audit']);

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->assertSee('PRJ-005')->assertSee('Hronská Dúbrava')->assertSee('Rozpočet / audit')
        ->assertSeeInOrder(['Prehľad', 'Dokumenty', 'Požiadavky', 'Úlohy', 'Riziká', 'Fázy']);
});

test('overview tab shows source of truth, missing evidence, questions and gate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $doc = Document::factory()->for($project)->create(['title' => 'Projektový zámer']);
    DocumentVersion::factory()->for($doc)->create(['version_label' => 'v1.2'])->activate($user);
    ProjectTask::factory()->for($project)->create(['title' => 'Doložiť LV', 'required_evidence' => 'Výpis z LV']);
    Question::factory()->for($project)->create(['body' => 'Je rozpočet v súlade s usmernením?', 'status' => QuestionStatus::Otvorena]);
    $gate = Gate::factory()->for($project)->create(['phase' => $project->phase->value, 'name' => 'Brána 1 – Screening']);
    GateItem::factory()->for($gate)->create(['is_met' => true]);

    Livewire::test(OverviewTab::class, ['project' => $project])
        ->assertSee('Zdroj pravdy')->assertSee('Projektový zámer')->assertSee('v1.2')
        ->assertSee('Chýbajúce podklady')->assertSee('Doložiť LV')
        ->assertSee('Otvorené otázky')->assertSee('Je rozpočet v súlade s usmernením?')
        ->assertSee('Kontrolná brána')->assertSee('Brána 1 – Screening');
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement**

`OverviewTab` computed data (all scoped to `$this->project`):

```php
#[Computed]
public function currentVersions()
{
    return $this->project->documents()->with(['type', 'versions' => fn ($q) => $q->where('status', DocumentVersionStatus::Aktualna)])->get()
        ->filter(fn ($d) => $d->versions->isNotEmpty());
}

#[Computed]
public function missingEvidence()
{
    return $this->project->tasks()->where('status', '!=', TaskStatus::Hotova)->whereNotNull('required_evidence')->get();
}

#[Computed]
public function openQuestions()
{
    return $this->project->questions()->where('status', QuestionStatus::Otvorena)->latest('asked_at')->get();
}

#[Computed]
public function currentGate(): ?Gate
{
    return $this->project->gates()->where('phase', $this->project->phase->value)->first();
}
```

View: 4-column grid (per mockup): „Zdroj pravdy“ (doc title + active version label + issued date, link „Otvoriť dokumenty →“ switching tab via `$parent`? — simpler: plain text list, tab switching stays in header), „Chýbajúce podklady“ (task title + priority chip: vysoka=red, stredna=amber, else gray), „Otvorené otázky“ (body + asked_by + asked_at `j. n. Y`), „Kontrolná brána“ (gate name, status chip prejdena=green „Prejdené“/cakajuca=gray „Čakajúca“/zamietnuta=red, checked date + „Skontrolované dňa …“). Empty states: „Žiadne aktuálne dokumenty.“ / „Všetky podklady doložené.“ / „Žiadne otvorené otázky.“ / „Brána pre túto fázu nie je definovaná.“

`ProjectShow` view: header card + tab nav (`border-b`, active tab `border-blue-600 text-blue-600 border-b-2`), then `@if ($tab === 'prehlad') <livewire:projects.overview-tab :project="$project" /> @else <div class="text-sm text-gray-500 p-6">Pripravujeme…</div> @endif`.

- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: project workspace shell and overview tab"`

---

### Task 7: Workspace tabs — Dokumenty, Požiadavky, Riziká

**Files:**
- Create: `app/Livewire/Projects/DocumentsTab.php` + view `resources/views/livewire/projects/documents-tab.blade.php`
- Create: `app/Livewire/Projects/QuestionsTab.php` + view `resources/views/livewire/projects/questions-tab.blade.php`
- Create: `app/Livewire/Projects/RisksTab.php` + view `resources/views/livewire/projects/risks-tab.blade.php`
- Modify: `resources/views/livewire/pages/project-show.blade.php` (add `@elseif` branches for `dokumenty`, `poziadavky`, `rizika`)
- Test: `tests/Feature/Projects/WorkspaceTabsTest.php`

**Interfaces:**
- Consumes: `:project` prop (each component: `public Project $project;`).
- Produces: `DocumentsTab` — documents grouped with ALL versions (newest first) showing per-version status badge (aktualna=green „Aktuálna“, nahradena=gray „Nahradená“, nepotvrdena=amber „Nepotvrdená“, historicka=gray „Historická“), confirmer name + date when set. `QuestionsTab` — questions list with status badge (otvorena=amber „Otvorená“, zodpovedana=green „Zodpovedaná“, uzavreta=gray „Uzavretá“), answers nested with bindingness chip (`zavazne`=green „Záväzné“, `pracovne`=blue „Pracovné“, `neformalne`=gray „Neformálne“). `RisksTab` — risks table: Riziko / Dopad / Pravdepodobnosť / Mitigácia / Stav (impact & likelihood via `label()`, vysoky=red chip, stredny=amber, nizky=gray).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/WorkspaceTabsTest.php
use App\Enums\AnswerBindingness;
use App\Enums\RiskLevel;
use App\Livewire\Projects\{DocumentsTab, QuestionsTab, RisksTab};
use App\Models\{Answer, Document, DocumentVersion, Project, Question, Risk, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('documents tab shows version history with statuses', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $doc = Document::factory()->for($project)->create(['title' => 'Projektová dokumentácia']);
    $v1 = DocumentVersion::factory()->for($doc)->create(['version_label' => 'v1.0']);
    $v2 = DocumentVersion::factory()->for($doc)->create(['version_label' => 'v1.2']);
    $v1->activate($user);
    $v2->activate($user);

    Livewire::test(DocumentsTab::class, ['project' => $project])
        ->assertSee('Projektová dokumentácia')
        ->assertSee('v1.0')->assertSee('Nahradená')
        ->assertSee('v1.2')->assertSee('Aktuálna');
});

test('questions tab shows answers with bindingness', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $q = Question::factory()->for($project)->create(['body' => 'Kedy dodáme stanovisko VO?']);
    Answer::factory()->for($q)->create(['body' => 'Do konca augusta.', 'bindingness' => AnswerBindingness::Zavazne]);

    Livewire::test(QuestionsTab::class, ['project' => $project])
        ->assertSee('Kedy dodáme stanovisko VO?')
        ->assertSee('Do konca augusta.')
        ->assertSee('Záväzné')->assertSee('Zodpovedaná');
});

test('risks tab lists project risks', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    Risk::factory()->for($project)->create(['title' => 'Stará PD nesedí s rozpočtom', 'impact' => RiskLevel::Vysoky]);

    Livewire::test(RisksTab::class, ['project' => $project])
        ->assertSee('Stará PD nesedí s rozpočtom')->assertSee('Vysoký');
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement.** Each component: `public Project $project;` + computed loading scoped data with eager loads (`documents()->with(['type', 'versions' => fn ($q) => $q->latest('id'), 'versions.confirmedBy'])` — add `DocumentVersion::confirmedBy(): BelongsTo(User::class, 'confirmed_by')` relation if missing; `questions()->with('answers')->latest('asked_at')`; `risks()->latest()`). Views per Interfaces; empty states „Žiadne dokumenty.“ / „Žiadne požiadavky.“ / „Žiadne riziká.“
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: workspace tabs for documents, questions and risks"`

---

### Task 8: Workspace tab Úlohy + dokončenie s dôkazom

**Files:**
- Create: `app/Livewire/Projects/TasksTab.php` + `resources/views/livewire/projects/tasks-tab.blade.php`
- Modify: `resources/views/livewire/pages/project-show.blade.php` (add `ulohy` branch)
- Test: `tests/Feature/Projects/TasksTabTest.php`

**Interfaces:**
- Consumes: `:project`; `ProjectTask::complete(?DocumentVersion $evidence, ?string $evidenceNote)` from M1.
- Produces: `TasksTab` with props `public Project $project;`, `?int $completingTaskId = null`, `?int $evidenceVersionId = null`, `string $evidenceNote = ''`, `?string $error = null`; actions `startComplete(int $taskId)` (opens inline completion panel), `cancelComplete()`, `confirmComplete()` — loads the task (must belong to `$this->project`), resolves optional `DocumentVersion` (must belong to the project via its document), calls `$task->complete($version, $this->evidenceNote ?: null)` inside try/catch `DomainException` → `$this->error = $e->getMessage()`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/TasksTabTest.php
use App\Enums\TaskStatus;
use App\Livewire\Projects\TasksTab;
use App\Models\{Document, DocumentVersion, Project, ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('completing a task without evidence shows the domain error', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $task = ProjectTask::factory()->for($project)->create();

    Livewire::test(TasksTab::class, ['project' => $project])
        ->call('startComplete', $task->id)
        ->call('confirmComplete')
        ->assertSet('error', 'Úlohu nemožno uzavrieť bez dôkazu.');

    expect($task->fresh()->status)->toBe(TaskStatus::Otvorena);
});

test('completing a task with evidence note closes it', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $task = ProjectTask::factory()->for($project)->create(['title' => 'Overiť úhradu faktúry']);

    Livewire::test(TasksTab::class, ['project' => $project])
        ->call('startComplete', $task->id)
        ->set('evidenceNote', 'Bankový výpis č. 7/2026 priložený.')
        ->call('confirmComplete')
        ->assertSet('error', null)
        ->assertSet('completingTaskId', null);

    expect($task->fresh()->status)->toBe(TaskStatus::Hotova);
});

test('completing with a document version stores the reference', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create();
    $task = ProjectTask::factory()->for($project)->create();
    $doc = Document::factory()->for($project)->create();
    $version = DocumentVersion::factory()->for($doc)->create();

    Livewire::test(TasksTab::class, ['project' => $project])
        ->call('startComplete', $task->id)
        ->set('evidenceVersionId', $version->id)
        ->call('confirmComplete')
        ->assertSet('error', null);

    expect($task->fresh()->evidence_document_version_id)->toBe($version->id);
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement**

```php
public function confirmComplete(): void
{
    $task = $this->project->tasks()->findOrFail($this->completingTaskId);

    $version = null;
    if ($this->evidenceVersionId !== null) {
        $version = DocumentVersion::whereHas('document', fn ($q) => $q->where('project_id', $this->project->id))
            ->findOrFail($this->evidenceVersionId);
    }

    try {
        $task->complete($version, $this->evidenceNote ?: null);
        $this->reset('completingTaskId', 'evidenceVersionId', 'evidenceNote', 'error');
    } catch (\DomainException $e) {
        $this->error = $e->getMessage();
    }
}
```

View: table Úloha / Priorita / Termín / Zodpovedný / Stav / akcia. Status chips: otvorena=blue „Otvorená“, caka=amber „Čaká“, hotova=green „Hotová“ (+ evidence summary under title when hotova: „Dôkaz: {version label / note}“). Action „Uzavrieť“ button for non-hotova rows → inline panel (when `$completingTaskId === $task->id`) with: select „Dôkaz — verzia dokumentu“ (project's document versions: `„{doc title} — {version_label}“`), textarea „Alebo písomný dôkaz“, red error box for `$error`, buttons Zrušiť / Potvrdiť uzavretie. Shows `required_evidence` hint: „Požadovaný dôkaz: …“.

- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: workspace tasks tab with evidence-required completion"`

---

### Task 9: Workspace tab Fázy + postup fázy

**Files:**
- Create: `app/Livewire/Projects/PhasesTab.php` + `resources/views/livewire/projects/phases-tab.blade.php`
- Modify: `resources/views/livewire/pages/project-show.blade.php` (add `fazy` branch — all six tabs now real; remove the „Pripravujeme…“ fallback)
- Test: `tests/Feature/Projects/PhasesTabTest.php`

**Interfaces:**
- Consumes: `:project`; `Project::advancePhase(User $by)`, `Gate::pass(User $by)` from M1.
- Produces: `PhasesTab` with `public Project $project;`, `?string $error = null;`; computed `gates` (project gates keyed by phase); action `advance()` → `$this->project->advancePhase(auth()->user())` in try/catch `DomainException` → `$error`; action `passGate(int $gateId)` → finds the project's gate, `->pass(auth()->user())` in try/catch.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Projects/PhasesTabTest.php
use App\Enums\GateStatus;
use App\Enums\ProjectPhase;
use App\Livewire\Projects\PhasesTab;
use App\Models\{Gate, GateItem, Project, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('phases tab lists all 12 phases and highlights the current one', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::PripravaZiadosti]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->assertSeeInOrder(['Prvotný screening', 'Príprava žiadosti', 'Udržateľnosť']);
});

test('advance without passed gate shows domain error', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('advance')
        ->assertSet('error', 'Projekt nemôže postúpiť: kontrolná brána neprešla.');
});

test('pass gate then advance moves the project to the next phase', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['phase' => ProjectPhase::ZberPodkladov]);
    $gate = Gate::factory()->for($project)->create(['phase' => 3]);
    GateItem::factory()->for($gate)->create(['is_met' => true]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('passGate', $gate->id)
        ->call('advance')
        ->assertSet('error', null);

    expect($project->fresh()->phase)->toBe(ProjectPhase::TechnickaFinancnaKontrola)
        ->and($gate->fresh()->status)->toBe(GateStatus::Prejdena);
});

test('gate with unmet items cannot pass', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create();
    $gate = Gate::factory()->for($project)->create(['phase' => $project->phase->value]);
    GateItem::factory()->for($gate)->create(['is_met' => false]);

    Livewire::test(PhasesTab::class, ['project' => $project])
        ->call('passGate', $gate->id)
        ->assertSet('error', 'Brána má nesplnené podmienky.');
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement.** `advance()`/`passGate()` per Interfaces (refresh `$this->project` after success: `$this->project->refresh();` and `unset($this->gates);` to bust the computed cache). View: vertical stepper of `ProjectPhase::cases()` — number circle (done=blue filled `<` current, current=blue ring, future=gray), phase `label()`; under each phase its gate (if any): name, status chip, item checklist (`✓`/`○` + label), „Označiť bránu ako prejdenú“ button for `cakajuca` gates, `checked_at` date for passed ones. Top-right: „Postúpiť do ďalšej fázy“ blue button + red error alert for `$error`.
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: phases tab with gate pass and phase advance"`

---

### Task 10: Dnes + globálne stránky (Inbox, Dokumenty, Požiadavky, Úlohy, Riziká, Rozhodnutia, Nastavenia)

**Files:**
- Modify: `app/Livewire/Pages/Dnes.php`, `InboxPage.php`, `DocumentsIndex.php`, `QuestionsIndex.php`, `TasksIndex.php`, `RisksIndex.php`, `DecisionsIndex.php`, `SettingsPage.php` + their views
- Test: `tests/Feature/Pages/GlobalPagesTest.php`

**Interfaces:**
- Produces (all read-only lists ordered newest-first, each row shows its project code as link to `projekty.show` when set):
  - `Dnes`: two sections — „Po termíne“ (open tasks `due_at < today`) and „Dnes a najbližšie dni“ (open tasks `due_at between today and today+7`), plus „Blížiace sa termíny projektov“ (projects with `next_deadline <= today+14`).
  - `InboxPage`: InboxItems with source label, status chip (nove=blue „Nové“, klasifikovane=amber, schvalene=green, zamietnute=gray), `unconfirmed` badge „Nepotvrdené“ (amber), raw_content excerpt (`Str::limit(…, 160)`). Note under heading: „AI klasifikácia príde v Míľniku 5.“
  - `DocumentsIndex`: all documents with project code, type name, current version label+status.
  - `QuestionsIndex`: all questions with status chip + project code.
  - `TasksIndex`: all tasks with priority chip, status chip, due date (red when overdue), project code.
  - `RisksIndex`: all risks with impact/likelihood chips + project code.
  - `DecisionsIndex`: all decisions with body, approved_by, approved_at `j. n. Y`, project code.
  - `SettingsPage`: card linking to `route('profile')` („Profil a heslo“) and to `/admin` („Administrácia (Orchid)“).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Pages/GlobalPagesTest.php
use App\Livewire\Pages\{Dnes, InboxPage, TasksIndex};
use App\Models\{InboxItem, Project, ProjectTask, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('dnes splits overdue and upcoming tasks', function () {
    $this->actingAs(User::factory()->create());
    ProjectTask::factory()->create(['title' => 'Zmeškaná úloha', 'due_at' => today()->subDays(2)]);
    ProjectTask::factory()->create(['title' => 'Blízka úloha', 'due_at' => today()->addDays(3)]);

    Livewire::test(Dnes::class)
        ->assertSeeInOrder(['Po termíne', 'Zmeškaná úloha', 'Dnes a najbližšie dni', 'Blízka úloha']);
});

test('inbox lists items with unconfirmed badge', function () {
    $this->actingAs(User::factory()->create());
    InboxItem::factory()->create(['raw_content' => 'Nová výzva pre obce na zateplenie budov.']);

    Livewire::test(InboxPage::class)
        ->assertSee('Nová výzva pre obce')
        ->assertSee('Nepotvrdené')
        ->assertSee('Nové');
});

test('tasks index links tasks to their project', function () {
    $this->actingAs(User::factory()->create());
    $project = Project::factory()->create(['code' => 'PRJ-001']);
    ProjectTask::factory()->for($project)->create(['title' => 'Skontrolovať PD']);

    Livewire::test(TasksIndex::class)
        ->assertSee('Skontrolovať PD')
        ->assertSee('PRJ-001');
});

test('remaining global pages render with demo seed', function () {
    $this->seed();
    $this->actingAs(User::factory()->create());
    foreach (['dokumenty.index', 'poziadavky.index', 'rizika.index', 'rozhodnutia.index', 'nastavenia'] as $route) {
        $this->get(route($route))->assertOk();
    }
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement** each page per Interfaces (computed properties with `with('project')` eager loads; shared visual language from earlier tasks; every page has a Slovak `<h1>` and an empty state). `Dnes` queries: `ProjectTask::where('status', '!=', TaskStatus::Hotova)->whereDate('due_at', '<', today())` and `->whereBetween('due_at', [today(), today()->addDays(7)])`; projects `whereBetween('next_deadline', [today(), today()->addDays(14)])`.
- [ ] **Step 4: PASS + full suite.**
- [ ] **Step 5: Visual check** — `php artisan migrate:fresh --seed && php artisan serve`; click through all sidebar items.
- [ ] **Step 6: Commit** — `git add -A && git commit -m "feat: dnes view and global list pages"`

---

### Task 11: Slovak auth polish + projekt identita

**Files:**
- Modify: `resources/views/livewire/pages/auth/login.blade.php` (Slovak labels: „E-mail“, „Heslo“, „Zapamätať si ma“, „Zabudli ste heslo?“, button „Prihlásiť sa“), `resources/views/livewire/pages/auth/forgot-password.blade.php` (Slovak intro + button „Odoslať odkaz na obnovenie hesla“), `resources/views/components/application-logo.blade.php` (replace with the blue „E“ square + „EUROFUND OS“ wordmark used in the sidebar)
- Modify: `README.md` (project readme: name, one-paragraph Slovak description, quickstart: `composer install && npm install && npm run build && cp .env.example .env && php artisan key:generate && php artisan migrate:fresh --seed && php artisan serve`, demo login, test command)
- Test: `tests/Feature/Auth/LoginPageSlovakTest.php`

**Interfaces:**
- Consumes: Breeze Volt login page. Only labels/texts change — form logic, routes and existing auth tests must stay green (they assert behavior, not copy; verify none asserts English strings — if one does, update its assertion to the Slovak copy).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/Auth/LoginPageSlovakTest.php
test('login page is in slovak', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Heslo')
        ->assertSee('Prihlásiť sa')
        ->assertSee('Zabudli ste heslo?');
});
```

- [ ] **Step 2: FAIL.**
- [ ] **Step 3: Implement** the copy changes + README rewrite.
- [ ] **Step 4: PASS + full suite** (`./vendor/bin/pest`).
- [ ] **Step 5: Commit** — `git add -A && git commit -m "feat: slovak auth pages and project readme"`

---

## Out of scope (later milestones)

Document upload UX and version confirmation UI (M3); creating/answering questions, decisions CRUD, gate item editing from UI (M4); AI inbox classification, cross-checks, drafts, prioritization (M5); global topbar search (enabled input), full audit-history page, Academy, notifications.
