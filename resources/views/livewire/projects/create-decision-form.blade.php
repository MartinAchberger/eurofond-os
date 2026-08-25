<div class="mt-1">
    <button type="button" wire:click="toggle"
            class="text-xs font-medium text-blue-600 hover:text-blue-700">
        {{ $open ? 'Skryť formulár' : 'Vytvoriť rozhodnutie' }}
    </button>

    @if ($open)
        <div class="mt-2 rounded-lg bg-gray-50 px-4 py-4 ring-1 ring-gray-100">
            <form wire:submit="save">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="decision-body-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Znenie rozhodnutia</label>
                        <textarea id="decision-body-{{ $answer->id }}" wire:model="body" rows="2"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="decision-approved-by-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Kto rozhodol</label>
                        <input type="text" id="decision-approved-by-{{ $answer->id }}" wire:model="approvedBy"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('approvedBy') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="decision-approved-at-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Kedy</label>
                        <input type="date" id="decision-approved-at-{{ $answer->id }}" wire:model="approvedAt"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('approvedAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="decision-rationale-{{ $answer->id }}" class="mb-1 block text-sm font-medium text-gray-700">Odôvodnenie</label>
                        <textarea id="decision-rationale-{{ $answer->id }}" wire:model="rationale" rows="2"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('rationale') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="toggle"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Zrušiť
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        Uložiť rozhodnutie
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
