<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Dnešné priority</h2>
        <div class="flex items-center gap-3">
            <button type="button" wire:click="$dispatch('open-create-task')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14m-7-7h14" />
                </svg>
                Nová úloha
            </button>
            <a href="{{ route('ulohy.index') }}"
               class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700">
                Zobraziť všetky
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>

    @if ($this->tasks->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne otvorené úlohy s termínom.</p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($this->tasks as $task)
                <li class="flex items-start gap-3 px-5 py-4">
                    <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-100 text-blue-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ $task->title }}</p>
                        @if ($task->project)
                            <p class="truncate text-xs text-gray-500">{{ $task->project->code }} {{ $task->project->name }}</p>
                        @endif
                    </div>
                    <span @class([
                        'shrink-0 text-sm font-medium',
                        'text-red-600' => $this->dueLabel($task) === 'Dnes',
                        'text-gray-500' => $this->dueLabel($task) !== 'Dnes',
                    ])>
                        {{ $this->dueLabel($task) }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
