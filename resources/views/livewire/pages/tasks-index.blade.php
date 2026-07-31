<div class="space-y-6">
    <h1 class="text-2xl font-bold">Úlohy</h1>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        @if ($this->tasks->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne úlohy.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Úloha</th>
                            <th class="px-5 py-3">Projekt</th>
                            <th class="px-5 py-3">Priorita</th>
                            <th class="px-5 py-3">Termín</th>
                            <th class="px-5 py-3">Stav</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $statusClasses = fn ($status) => match ($status) {
                                \App\Enums\TaskStatus::Otvorena => 'bg-blue-100 text-blue-700',
                                \App\Enums\TaskStatus::Caka => 'bg-amber-100 text-amber-700',
                                \App\Enums\TaskStatus::Hotova => 'bg-green-100 text-green-700',
                            };
                            $priorityClasses = fn ($priority) => match ($priority->value) {
                                'blokator', 'vysoka' => 'bg-red-100 text-red-700',
                                'stredna' => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        @foreach ($this->tasks as $task)
                            @php
                                $isOverdue = $task->due_at && $task->due_at->lt(today()) && $task->status !== \App\Enums\TaskStatus::Hotova;
                            @endphp
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $task->title }}</td>
                                <td class="px-5 py-3 text-gray-500">
                                    @if ($task->project)
                                        <a href="{{ route('projekty.show', $task->project) }}" class="hover:text-blue-600">
                                            {{ $task->project->code }}
                                        </a>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $priorityClasses($task->priority) }}">
                                        {{ $task->priority->label() }}
                                    </span>
                                </td>
                                <td @class([
                                    'px-5 py-3',
                                    'text-red-600 font-medium' => $isOverdue,
                                    'text-gray-700' => ! $isOverdue,
                                ])>
                                    {{ $task->due_at?->format('j. n. Y') ?? '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses($task->status) }}">
                                        {{ $task->status->label() }}
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
