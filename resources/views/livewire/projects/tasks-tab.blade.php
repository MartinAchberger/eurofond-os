<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Úlohy</h2>
    </div>

    @if ($this->tasks->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne úlohy.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Úloha</th>
                        <th class="px-5 py-3">Priorita</th>
                        <th class="px-5 py-3">Termín</th>
                        <th class="px-5 py-3">Zodpovedný</th>
                        <th class="px-5 py-3">Stav</th>
                        <th class="px-5 py-3"></th>
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
                        <tr>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $task->title }}</p>
                                @if ($task->status === \App\Enums\TaskStatus::Hotova)
                                    <p class="text-xs text-gray-500">
                                        Dôkaz:
                                        @if ($task->evidenceDocumentVersion)
                                            {{ $task->evidenceDocumentVersion->document->title }} — {{ $task->evidenceDocumentVersion->version_label }}
                                        @elseif ($task->evidence_note)
                                            {{ $task->evidence_note }}
                                        @else
                                            —
                                        @endif
                                    </p>
                                @elseif ($task->required_evidence)
                                    <p class="text-xs text-gray-500">Požadovaný dôkaz: {{ $task->required_evidence }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $priorityClasses($task->priority) }}">
                                    {{ $task->priority->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $task->due_at?->format('j. n. Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $task->assignee?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses($task->status) }}">
                                    {{ $task->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if ($task->status !== \App\Enums\TaskStatus::Hotova)
                                    <button type="button" wire:click="startComplete({{ $task->id }})"
                                            class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-500">
                                        Uzavrieť
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if ($completingTaskId === $task->id)
                            <tr>
                                <td colspan="6" class="bg-gray-50 px-5 py-4">
                                    <div class="space-y-3">
                                        @if ($task->required_evidence)
                                            <p class="text-xs text-gray-500">Požadovaný dôkaz: {{ $task->required_evidence }}</p>
                                        @endif

                                        @if ($error)
                                            <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 ring-1 ring-red-200">
                                                {{ $error }}
                                            </div>
                                        @endif

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Dôkaz — verzia dokumentu</label>
                                            <select wire:model="evidenceVersionId"
                                                    class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">— Vyberte verziu dokumentu —</option>
                                                @foreach ($this->evidenceVersions as $version)
                                                    <option value="{{ $version->id }}">{{ $version->document->title }} — {{ $version->version_label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Alebo písomný dôkaz</label>
                                            <textarea wire:model="evidenceNote" rows="2"
                                                      class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <button type="button" wire:click="cancelComplete"
                                                    class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">
                                                Zrušiť
                                            </button>
                                            <button type="button" wire:click="confirmComplete"
                                                    class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-500">
                                                Potvrdiť uzavretie
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
