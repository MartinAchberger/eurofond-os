<div class="space-y-6">
    <h1 class="text-2xl font-bold">Dnes</h1>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">Po termíne</h2>
        </div>
        @if ($this->overdueTasks->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne úlohy po termíne.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($this->overdueTasks as $task)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $task->title }}</p>
                            @if ($task->project)
                                <a href="{{ route('projekty.show', $task->project) }}" class="text-xs text-gray-500 hover:text-blue-600">
                                    {{ $task->project->code }}
                                </a>
                            @endif
                        </div>
                        <span class="shrink-0 text-sm font-medium text-red-600">{{ $task->due_at->format('j. n. Y') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">Dnes a najbližšie dni</h2>
        </div>
        @if ($this->upcomingTasks->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne úlohy v najbližších dňoch.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($this->upcomingTasks as $task)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $task->title }}</p>
                            @if ($task->project)
                                <a href="{{ route('projekty.show', $task->project) }}" class="text-xs text-gray-500 hover:text-blue-600">
                                    {{ $task->project->code }}
                                </a>
                            @endif
                        </div>
                        <span class="shrink-0 text-sm text-gray-500">{{ $task->due_at->format('j. n. Y') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">Blížiace sa termíny projektov</h2>
        </div>
        @if ($this->upcomingProjectDeadlines->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne blížiace sa termíny projektov.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($this->upcomingProjectDeadlines as $project)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-4">
                        <a href="{{ route('projekty.show', $project) }}" class="text-sm font-semibold text-gray-900 hover:text-blue-600">
                            {{ $project->code }} {{ $project->name }}
                        </a>
                        <span class="shrink-0 text-sm text-gray-500">{{ $project->next_deadline->format('j. n. Y') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
