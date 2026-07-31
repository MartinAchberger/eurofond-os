<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Fázy</h2>
        <button type="button" wire:click="advance"
                class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-500">
            Postúpiť do ďalšej fázy
        </button>
    </div>

    <div class="px-5 py-4">
        @if ($error)
            <div class="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 ring-1 ring-red-200">
                {{ $error }}
            </div>
        @endif

        @php
            $statusClasses = fn ($status) => match ($status) {
                \App\Enums\GateStatus::Cakajuca => 'bg-amber-100 text-amber-700',
                \App\Enums\GateStatus::Prejdena => 'bg-green-100 text-green-700',
                \App\Enums\GateStatus::Zamietnuta => 'bg-red-100 text-red-700',
            };
        @endphp

        <ol class="space-y-6">
            @foreach (\App\Enums\ProjectPhase::cases() as $phase)
                @php
                    $isDone = $phase->value < $project->phase->value;
                    $isCurrent = $phase->value === $project->phase->value;
                    $gate = $this->gates->get($phase->value);
                @endphp
                <li class="flex gap-4">
                    <div class="flex flex-col items-center">
                        @if ($isDone)
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white">
                                &check;
                            </span>
                        @elseif ($isCurrent)
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full ring-2 ring-blue-600 text-xs font-semibold text-blue-600">
                                {{ $phase->value }}
                            </span>
                        @else
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500">
                                {{ $phase->value }}
                            </span>
                        @endif
                        @unless ($phase === \App\Enums\ProjectPhase::Udrzatelnost)
                            <span class="mt-1 w-px flex-1 bg-gray-200"></span>
                        @endunless
                    </div>

                    <div class="flex-1 pb-2">
                        <p @class([
                            'text-sm font-medium',
                            'text-gray-900' => $isDone || $isCurrent,
                            'text-gray-400' => !$isDone && !$isCurrent,
                        ])>
                            {{ $phase->label() }}
                        </p>

                        @if ($gate)
                            <div class="mt-2 rounded-lg bg-gray-50 p-3 ring-1 ring-gray-100">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-medium text-gray-800">{{ $gate->name }}</span>
                                    <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses($gate->status) }}">
                                        {{ $gate->status->label() }}
                                    </span>
                                </div>

                                @if ($gate->items->isNotEmpty())
                                    <ul class="mt-2 space-y-1">
                                        @foreach ($gate->items as $item)
                                            <li class="flex items-center gap-2 text-xs text-gray-600">
                                                <span>{{ $item->is_met ? '✓' : '○' }}</span>
                                                <span>{{ $item->label }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($gate->status === \App\Enums\GateStatus::Cakajuca)
                                    <button type="button" wire:click="passGate({{ $gate->id }})"
                                            class="mt-3 rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-500">
                                        Označiť bránu ako prejdenú
                                    </button>
                                @elseif ($gate->status === \App\Enums\GateStatus::Prejdena && $gate->checked_at)
                                    <p class="mt-2 text-xs text-gray-500">Prejdená {{ $gate->checked_at->format('j. n. Y') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
