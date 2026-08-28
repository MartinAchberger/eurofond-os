<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Portfólio projektov</h2>
        <a href="{{ route('projekty.index') }}"
           class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700">
            Zobraziť všetky
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </a>
    </div>

    @if ($this->projects->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne aktívne projekty.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Kód projektu</th>
                        <th class="px-5 py-3">Názov projektu</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Fáza</th>
                        <th class="px-5 py-3">Najbližší krok</th>
                        <th class="px-5 py-3">Blížiaci sa termín</th>
                        <th class="px-5 py-3">Zdravie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($this->projects as $project)
                        <tr>
                            <td class="px-5 py-3 text-gray-500">{{ $project->code }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('projekty.show', $project) }}" class="font-medium hover:text-blue-600">
                                    {{ $project->name }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                @if ($project->status_label)
                                    <span class="rounded-md bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800">
                                        {{ $project->status_label }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $project->phase->label() }}</td>
                            <td class="px-5 py-3">
                                @php $nextStep = $project->nextStep(); @endphp
                                @if ($nextStep)
                                    <span @class([
                                        'text-gray-700',
                                        'font-medium text-red-600' => $nextStep->priority === \App\Enums\TaskPriority::Blokator,
                                    ])>{{ $nextStep->title }}</span>
                                    @if ($nextStep->priority === \App\Enums\TaskPriority::Blokator)
                                        <span class="ml-1 rounded-md bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-700">Blokátor</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td @class([
                                'px-5 py-3',
                                'text-red-600 font-medium' => $project->next_deadline?->lte(today()->addDays(7)),
                                'text-gray-700' => ! $project->next_deadline?->lte(today()->addDays(7)),
                            ])>
                                {{ $project->next_deadline?->format('j. n. Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <span @class([
                                        'h-2.5 w-2.5 rounded-full',
                                        'bg-emerald-500' => $project->health->value === 'dobre',
                                        'bg-amber-400' => $project->health->value === 'stredne',
                                        'bg-red-500' => $project->health->value === 'riziko',
                                    ])></span>
                                    <span class="text-gray-700">{{ $project->health->label() }}</span>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
