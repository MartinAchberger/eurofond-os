<div class="mt-1">
    <button type="button" wire:click="toggle"
            class="text-xs font-medium text-blue-600 hover:text-blue-700">
        {{ $open ? 'Skryť formulár' : 'Vytvoriť úlohu' }}
    </button>

    @if ($open)
        <div class="mt-2 rounded-lg bg-gray-50 px-4 py-4 ring-1 ring-gray-100">
            <form wire:submit="save">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="task-title-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Názov úlohy</label>
                        <input type="text" id="task-title-{{ $answer->id }}" wire:model="title"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="task-assignee-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Zodpovedný</label>
                        <select id="task-assignee-{{ $answer->id }}" wire:model="assigneeId"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">— Nepriradené —</option>
                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assigneeId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="task-priority-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Priorita</label>
                        <select id="task-priority-{{ $answer->id }}" wire:model="priority"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach (\App\Enums\TaskPriority::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                        @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="task-due-at-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Termín</label>
                        <input type="date" id="task-due-at-{{ $answer->id }}" wire:model="dueAt"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('dueAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="task-evidence-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Požadovaný dôkaz</label>
                        <input type="text" id="task-evidence-{{ $answer->id }}" wire:model="requiredEvidence"
                               placeholder="napr. priložený výpis z katastra"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('requiredEvidence') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="toggle"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Zrušiť
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        Uložiť úlohu
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
