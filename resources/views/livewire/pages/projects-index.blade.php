<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">Projekty</h1>
        <input type="search" wire:model.live.debounce.300ms="q" placeholder="Hľadať projekt…"
               class="w-full max-w-xs rounded-lg border-gray-200 bg-white text-sm shadow-sm">
    </div>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        @if ($this->projects->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne projekty nezodpovedajú hľadaniu.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Kód</th>
                            <th class="px-5 py-3">Názov</th>
                            <th class="px-5 py-3">Žiadateľ</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Fáza</th>
                            <th class="px-5 py-3">Termín</th>
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
                                <td class="px-5 py-3 text-gray-700">{{ $project->client?->name }}</td>
                                <td class="px-5 py-3">
                                    @if ($project->status_label)
                                        <span class="rounded-md bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800">
                                            {{ $project->status_label }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-700">{{ $project->phase->label() }}</td>
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
</div>
