<div class="mt-3">
    @if ($question->status !== \App\Enums\QuestionStatus::Uzavreta)
        <button type="button" wire:click="toggle"
                class="text-sm font-medium text-blue-600 hover:text-blue-700">
            {{ $open ? 'Skryť formulár' : 'Zaznamenať odpoveď' }}
        </button>
    @endif

    @if ($open)
        <div class="mt-3 rounded-lg bg-gray-50 px-4 py-4 ring-1 ring-gray-100">
            <form wire:submit="save">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="answer-answered-by-{{ $question->id }}" class="mb-1 block text-sm font-medium text-gray-700">Kto odpovedal</label>
                        <input type="text" id="answer-answered-by-{{ $question->id }}" wire:model="answeredBy"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('answeredBy') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="answer-answered-at-{{ $question->id }}" class="mb-1 block text-sm font-medium text-gray-700">Kedy</label>
                        <input type="date" id="answer-answered-at-{{ $question->id }}" wire:model="answeredAt"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('answeredAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="answer-body-{{ $question->id }}" class="mb-1 block text-sm font-medium text-gray-700">Presné znenie odpovede</label>
                        <textarea id="answer-body-{{ $question->id }}" wire:model="body" rows="2"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="answer-source-{{ $question->id }}" class="mb-1 block text-sm font-medium text-gray-700">Zdroj odpovede</label>
                        <input type="text" id="answer-source-{{ $question->id }}" wire:model="source"
                               placeholder="e-mail, telefonát, zápisnica…"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('source') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="answer-bindingness-{{ $question->id }}" class="mb-1 block text-sm font-medium text-gray-700">Záväznosť</label>
                        <select id="answer-bindingness-{{ $question->id }}" wire:model="bindingness"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach (\App\Enums\AnswerBindingness::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                        @error('bindingness') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="toggle"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Zrušiť
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        Uložiť odpoveď
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
