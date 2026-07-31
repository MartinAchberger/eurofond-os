<div class="space-y-4">
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex flex-wrap items-center gap-3 px-5 py-4">
            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-violet-500"></span>
            <h1 class="text-lg font-bold text-gray-900">{{ $project->code }} {{ $project->name }}</h1>
            @if ($project->status_label)
                <span class="rounded-md bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800">
                    {{ $project->status_label }}
                </span>
            @endif
            <div class="ml-auto flex flex-wrap items-center gap-4 text-sm text-gray-500">
                @if ($project->client)
                    <span>{{ $project->client->name }}</span>
                @endif
                @if ($project->owner)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Vlastník: {{ $project->owner->name }}
                    </span>
                @endif
            </div>
        </div>

        @if ($project->budget_total !== null || $project->grant_requested !== null)
            <div class="flex flex-wrap gap-4 border-t border-gray-100 px-5 py-2 text-xs text-gray-500">
                @if ($project->budget_total !== null)
                    <span>Rozpočet: {{ number_format($project->budget_total, 2, ',', ' ') }} €</span>
                @endif
                @if ($project->grant_requested !== null)
                    <span>Žiadaný grant: {{ number_format($project->grant_requested, 2, ',', ' ') }} €</span>
                @endif
            </div>
        @endif

        <div class="flex gap-1 overflow-x-auto border-t border-gray-100 px-5">
            @php
                $tabs = [
                    'prehlad' => 'Prehľad',
                    'dokumenty' => 'Dokumenty',
                    'poziadavky' => 'Požiadavky',
                    'ulohy' => 'Úlohy',
                    'rizika' => 'Riziká',
                    'fazy' => 'Fázy',
                ];
            @endphp
            @foreach ($tabs as $key => $label)
                <button type="button" wire:click="$set('tab', '{{ $key }}')"
                        @class([
                            'shrink-0 border-b-2 px-3 py-3 text-sm font-medium',
                            'border-blue-600 text-blue-600' => $tab === $key,
                            'border-transparent text-gray-500 hover:text-gray-700' => $tab !== $key,
                        ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($tab === 'prehlad')
        <livewire:projects.overview-tab :project="$project" />
    @else
        <div class="p-6 text-sm text-gray-500">Pripravujeme…</div>
    @endif
</div>
