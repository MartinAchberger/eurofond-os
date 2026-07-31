<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Auditná história</h2>
        <a href="#" class="pointer-events-none text-sm font-medium text-gray-400" aria-disabled="true">
            Zobraziť celú históriu &rarr;
        </a>
    </div>

    @if ($this->activities->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadna aktivita.</p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($this->activities as $activity)
                <li class="flex flex-wrap items-center gap-x-2 px-5 py-3 text-sm text-gray-700">
                    <span class="text-gray-500">{{ $activity->created_at->format('j. n. Y H:i') }}</span>
                    <span>&middot;</span>
                    <span>{{ $activity->causer?->name ?? 'Systém' }}</span>
                    <span>&middot;</span>
                    <span>
                        {{ match ($activity->event) {
                            'created' => 'Vytvorené',
                            'updated' => 'Upravené',
                            default => $activity->event,
                        } }}
                        {{ class_basename($activity->subject_type) }}
                        {{ $activity->subject?->code ?? $activity->subject?->title ?? $activity->subject?->name ?? '#'.$activity->subject_id }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
