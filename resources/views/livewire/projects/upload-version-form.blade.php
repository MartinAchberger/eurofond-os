<div>
    @if (! $open)
        <button type="button" wire:click="toggle"
                class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14m-7-7h14" />
            </svg>
            Nahrať novú verziu
        </button>
    @else
        <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <form wire:submit="save">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="upload-version-file-{{ $document->id }}" class="mb-1 block text-sm font-medium text-gray-700">Súbor</label>
                    <input type="file" id="upload-version-file-{{ $document->id }}" wire:model="file"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                           class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-blue-700">
                    <p class="mt-1 text-xs text-gray-500">PDF, Word, Excel alebo obrázok, max. 20 MB</p>
                    <div wire:loading wire:target="file" class="mt-1 text-xs text-gray-500">Nahráva sa…</div>
                    @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="upload-version-label-{{ $document->id }}" class="mb-1 block text-sm font-medium text-gray-700">Označenie verzie</label>
                    <input type="text" id="upload-version-label-{{ $document->id }}" wire:model="versionLabel" placeholder="napr. v1.0"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('versionLabel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="upload-version-issued-at-{{ $document->id }}" class="mb-1 block text-sm font-medium text-gray-700">Dátum vydania</label>
                    <input type="date" id="upload-version-issued-at-{{ $document->id }}" wire:model="issuedAt"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('issuedAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="upload-version-author-{{ $document->id }}" class="mb-1 block text-sm font-medium text-gray-700">Autor</label>
                    <input type="text" id="upload-version-author-{{ $document->id }}" wire:model="author"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('author') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-3">
                <button type="button" wire:click="close"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Zrušiť
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="file,save"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Nahrať verziu
                </button>
            </div>
            </form>
        </div>
    @endif
</div>
