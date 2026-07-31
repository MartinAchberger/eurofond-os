<div>
    @if ($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h2 class="text-base font-semibold text-gray-900">Nová úloha</h2>
                    <button type="button" wire:click="close" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-5 py-4">
                    <div>
                        <label for="create-task-title" class="mb-1 block text-sm font-medium text-gray-700">Názov úlohy</label>
                        <input type="text" id="create-task-title" wire:model="title"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="create-task-project" class="mb-1 block text-sm font-medium text-gray-700">Projekt</label>
                        <select id="create-task-project" wire:model="projectId"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— bez projektu —</option>
                            @foreach ($this->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->code }} {{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('projectId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="create-task-assignee" class="mb-1 block text-sm font-medium text-gray-700">Zodpovedná osoba</label>
                        <select id="create-task-assignee" wire:model="assigneeId"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— nepriradené —</option>
                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assigneeId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="create-task-priority" class="mb-1 block text-sm font-medium text-gray-700">Priorita</label>
                        <select id="create-task-priority" wire:model="priority"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach (\App\Enums\TaskPriority::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                        @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="create-task-due" class="mb-1 block text-sm font-medium text-gray-700">Termín</label>
                        <input type="date" id="create-task-due" wire:model="dueAt"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('dueAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="create-task-note" class="mb-1 block text-sm font-medium text-gray-700">Poznámka</label>
                        <textarea id="create-task-note" wire:model="note" rows="3"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('note') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="create-task-evidence" class="mb-1 block text-sm font-medium text-gray-700">Požadovaný dôkaz</label>
                        <input type="text" id="create-task-evidence" wire:model="requiredEvidence"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('requiredEvidence') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-5 py-4">
                    <button type="button" wire:click="close"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Zrušiť
                    </button>
                    <button type="button" wire:click="save"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Vytvoriť úlohu
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
