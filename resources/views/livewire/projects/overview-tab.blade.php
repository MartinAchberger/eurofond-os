<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
            <svg class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M7.5 3.75h6l4.5 4.5v10.5a1.5 1.5 0 01-1.5 1.5h-9a1.5 1.5 0 01-1.5-1.5V5.25a1.5 1.5 0 011.5-1.5zM13.5 3.75V8.25h4.5" />
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Zdroj pravdy</h3>
        </div>
        <div class="flex-1 divide-y divide-gray-100">
            @forelse ($this->currentVersions as $document)
                @php $version = $document->versions->first(); @endphp
                <div class="px-4 py-3">
                    <p class="truncate text-sm font-medium text-gray-900">{{ $document->title }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $version->version_label }}
                        @if ($version->issued_at)
                            &bull; {{ $version->issued_at->format('j. n. Y') }}
                        @endif
                    </p>
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-gray-500">Žiadne aktuálne dokumenty.</p>
            @endforelse
        </div>
        <div class="border-t border-gray-100 px-4 py-3">
            <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">
                Otvoriť dokumenty
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </span>
        </div>
    </div>

    <div class="flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
            <svg class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM3.75 18.75h16.5a1.5 1.5 0 001.32-2.21L13.32 4.5a1.5 1.5 0 00-2.64 0L2.43 16.54a1.5 1.5 0 001.32 2.21z" />
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Chýbajúce podklady</h3>
        </div>
        <div class="flex-1 divide-y divide-gray-100">
            @forelse ($this->missingEvidence as $task)
                @php
                    $priorityClasses = match ($task->priority->value) {
                        'vysoka' => 'bg-red-100 text-red-700',
                        'stredna' => 'bg-amber-100 text-amber-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <div class="flex items-center justify-between gap-2 px-4 py-3">
                    <p class="truncate text-sm font-medium text-gray-900">{{ $task->title }}</p>
                    <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium {{ $priorityClasses }}">
                        {{ $task->priority->label() }}
                    </span>
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-gray-500">Všetky podklady doložené.</p>
            @endforelse
        </div>
        <div class="border-t border-gray-100 px-4 py-3">
            <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">
                Zobraziť všetky
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </span>
        </div>
    </div>

    <div class="flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
            <svg class="h-4 w-4 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Otvorené otázky</h3>
        </div>
        <div class="flex-1 divide-y divide-gray-100">
            @forelse ($this->openQuestions as $question)
                <div class="px-4 py-3">
                    <p class="text-sm font-medium text-gray-900">{{ $question->body }}</p>
                    <p class="text-xs text-gray-500">{{ $question->asked_by }} &bull; {{ $question->asked_at->format('j. n. Y') }}</p>
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-gray-500">Žiadne otvorené otázky.</p>
            @endforelse
        </div>
        <div class="border-t border-gray-100 px-4 py-3">
            <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">
                Zobraziť všetky
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </span>
        </div>
    </div>

    <div class="flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
            <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 12.75l2.25 2.25L15 10.5m6 1.5a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Kontrolná brána</h3>
        </div>
        <div class="flex-1 px-4 py-3">
            @if ($this->currentGate)
                @php
                    $gate = $this->currentGate;
                    $gateLabel = match ($gate->status) {
                        \App\Enums\GateStatus::Prejdena => 'Prejdené',
                        \App\Enums\GateStatus::Cakajuca => 'Čakajúca',
                        default => $gate->status->label(),
                    };
                    $gateClasses = match ($gate->status) {
                        \App\Enums\GateStatus::Prejdena => 'bg-emerald-100 text-emerald-700',
                        \App\Enums\GateStatus::Zamietnuta => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <p class="text-sm font-medium text-gray-900">{{ $gate->name }}</p>
                <span class="mt-2 inline-block rounded-md px-2 py-0.5 text-xs font-medium {{ $gateClasses }}">
                    {{ $gateLabel }}
                </span>
                @if ($gate->checked_at)
                    <p class="mt-2 text-xs text-gray-500">Skontrolované dňa {{ $gate->checked_at->format('j. n. Y') }}</p>
                @endif
            @else
                <p class="py-3 text-center text-sm text-gray-500">Brána pre túto fázu nie je definovaná.</p>
            @endif
        </div>
        <div class="border-t border-gray-100 px-4 py-3">
            <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600">
                Zobraziť brány
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </span>
        </div>
    </div>
</div>
