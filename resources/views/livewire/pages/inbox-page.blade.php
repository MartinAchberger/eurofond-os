<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Inbox</h1>
        <p class="mt-1 text-sm text-gray-500">AI klasifikácia príde v Míľniku 5.</p>
    </div>

    @if ($error)
        <div class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 ring-1 ring-red-200">
            {{ $error }}
        </div>
    @endif

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        @if ($this->items->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne položky v inboxe.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @php
                    $statusClasses = fn ($status) => match ($status) {
                        \App\Enums\InboxItemStatus::Nove => 'bg-blue-100 text-blue-700',
                        \App\Enums\InboxItemStatus::Klasifikovane => 'bg-amber-100 text-amber-700',
                        \App\Enums\InboxItemStatus::Schvalene => 'bg-green-100 text-green-700',
                        \App\Enums\InboxItemStatus::Zamietnute => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                @foreach ($this->items as $item)
                    <li class="px-5 py-4" wire:key="inbox-item-{{ $item->id }}">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium text-gray-500">{{ $item->source->label() }}</span>
                            <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses($item->status) }}">
                                {{ $item->status->label() }}
                            </span>
                            @if ($item->unconfirmed)
                                <span class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                    Nepotvrdené
                                </span>
                            @endif
                            @if ($item->suggestedProject)
                                <a href="{{ route('projekty.show', $item->suggestedProject) }}"
                                   class="ml-auto text-xs text-gray-500 hover:text-blue-600">
                                    {{ $item->suggestedProject->code }}
                                </a>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($item->raw_content, 160) }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $item->created_at->format('j. n. Y H:i') }}</p>

                        @if (! in_array($item->status, [\App\Enums\InboxItemStatus::Schvalene, \App\Enums\InboxItemStatus::Zamietnute], true))
                            <div class="mt-2 flex items-center gap-4">
                                <button type="button" wire:click="startTriage({{ $item->id }})"
                                        class="text-xs font-medium text-blue-600 hover:text-blue-700">
                                    Spracovať
                                </button>
                                <button type="button" wire:click="reject({{ $item->id }})"
                                        wire:confirm="Naozaj zamietnuť túto položku?"
                                        class="text-xs font-medium text-gray-500 hover:text-gray-700">
                                    Zamietnuť
                                </button>
                            </div>
                        @endif

                        @if ($triagingId === $item->id)
                            <form wire:submit="confirmTriage" class="mt-3 rounded-lg bg-gray-50 px-4 py-4 ring-1 ring-gray-100">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="triage-type" class="mb-1 block text-sm font-medium text-gray-700">Typ záznamu</label>
                                        <select id="triage-type" wire:model.live="type"
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="uloha">Úloha</option>
                                            <option value="otazka">Otázka</option>
                                            <option value="riziko">Riziko</option>
                                        </select>
                                        @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="triage-project" class="mb-1 block text-sm font-medium text-gray-700">Projekt</label>
                                        <select id="triage-project" wire:model="projectId"
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">— Vyberte projekt —</option>
                                            @foreach ($this->projects as $project)
                                                <option value="{{ $project->id }}">{{ $project->code }} — {{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('projectId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="triage-body" class="mb-1 block text-sm font-medium text-gray-700">
                                            {{ $type === 'otazka' ? 'Znenie otázky' : ($type === 'riziko' ? 'Názov rizika' : 'Názov úlohy') }}
                                        </label>
                                        <textarea id="triage-body" wire:model="body" rows="2"
                                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                        @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    @if ($type === 'otazka')
                                        <div>
                                            <label for="triage-asked-to" class="mb-1 block text-sm font-medium text-gray-700">Komu</label>
                                            <input type="text" id="triage-asked-to" wire:model="askedTo"
                                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('askedTo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    @endif

                                    @if ($type === 'uloha')
                                        <div>
                                            <label for="triage-priority" class="mb-1 block text-sm font-medium text-gray-700">Priorita</label>
                                            <select id="triage-priority" wire:model="priority"
                                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach (\App\Enums\TaskPriority::cases() as $case)
                                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                                @endforeach
                                            </select>
                                            @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    @endif

                                    @if ($type !== 'riziko')
                                        <div>
                                            <label for="triage-due-at" class="mb-1 block text-sm font-medium text-gray-700">
                                                {{ $type === 'otazka' ? 'Termín na odpoveď' : 'Termín' }}
                                            </label>
                                            <input type="date" id="triage-due-at" wire:model="dueAt"
                                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error('dueAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    @endif

                                    @if ($type === 'riziko')
                                        <div>
                                            <label for="triage-impact" class="mb-1 block text-sm font-medium text-gray-700">Dopad</label>
                                            <select id="triage-impact" wire:model="impact"
                                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach (\App\Enums\RiskLevel::cases() as $case)
                                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                                @endforeach
                                            </select>
                                            @error('impact') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label for="triage-likelihood" class="mb-1 block text-sm font-medium text-gray-700">Pravdepodobnosť</label>
                                            <select id="triage-likelihood" wire:model="likelihood"
                                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                                @foreach (\App\Enums\RiskLevel::cases() as $case)
                                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                                @endforeach
                                            </select>
                                            @error('likelihood') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 flex items-center justify-end gap-3">
                                    <button type="button" wire:click="cancelTriage"
                                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                        Zrušiť
                                    </button>
                                    <button type="submit" wire:loading.attr="disabled" wire:target="confirmTriage"
                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                        Vytvoriť záznam
                                    </button>
                                </div>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
