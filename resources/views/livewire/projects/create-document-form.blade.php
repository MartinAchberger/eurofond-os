<div>
    @if ($open)
        <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="create-document-title" class="mb-1 block text-sm font-medium text-gray-700">Názov dokumentu</label>
                    <input type="text" id="create-document-title" wire:model="title"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="create-document-type" class="mb-1 block text-sm font-medium text-gray-700">Typ dokumentu</label>
                    <select id="create-document-type" wire:model="documentTypeId"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— vyberte typ —</option>
                        @foreach ($this->documentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('documentTypeId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-3">
                <button type="button" wire:click="toggle"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Zrušiť
                </button>
                <button type="button" wire:click="save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Vytvoriť dokument
                </button>
            </div>
        </div>
    @endif
</div>
