<div class="contents">
    <button type="button" wire:click="toggle"
            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14m-7-7h14" />
        </svg>
        Nová otázka
    </button>

    @if ($open)
        <div class="w-full basis-full rounded-lg bg-gray-50 px-4 py-4 ring-1 ring-gray-100">
            <form wire:submit="save">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="create-question-asked-by" class="mb-1 block text-sm font-medium text-gray-700">Kto sa pýta</label>
                    <input type="text" id="create-question-asked-by" wire:model="askedBy"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('askedBy') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="create-question-asked-to" class="mb-1 block text-sm font-medium text-gray-700">Komu</label>
                    <input type="text" id="create-question-asked-to" wire:model="askedTo"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('askedTo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="create-question-body" class="mb-1 block text-sm font-medium text-gray-700">Presné znenie</label>
                    <textarea id="create-question-body" wire:model="body" rows="2"
                              class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="create-question-reason" class="mb-1 block text-sm font-medium text-gray-700">Dôvod otázky</label>
                    <textarea id="create-question-reason" wire:model="reason" rows="2"
                              class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="create-question-due-at" class="mb-1 block text-sm font-medium text-gray-700">Termín na odpoveď</label>
                    <input type="date" id="create-question-due-at" wire:model="dueAt"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('dueAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="create-question-document" class="mb-1 block text-sm font-medium text-gray-700">Dokument</label>
                    <select id="create-question-document" wire:model="documentId"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— Bez dokumentu —</option>
                        @foreach ($this->documents as $document)
                            <option value="{{ $document->id }}">{{ $document->title }}</option>
                        @endforeach
                    </select>
                    @error('documentId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-3">
                <button type="button" wire:click="toggle"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Zrušiť
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Vytvoriť otázku
                </button>
            </div>
            </form>
        </div>
    @endif
</div>
