<div class="space-y-6">
    <h1 class="text-2xl font-bold">Požiadavky</h1>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        @if ($this->questions->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne požiadavky.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @php
                    $statusClasses = fn ($status) => match ($status) {
                        \App\Enums\QuestionStatus::Otvorena => 'bg-amber-100 text-amber-700',
                        \App\Enums\QuestionStatus::Zodpovedana => 'bg-emerald-100 text-emerald-700',
                        \App\Enums\QuestionStatus::Uzavreta => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                @foreach ($this->questions as $question)
                    <li class="px-5 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="text-sm font-medium text-gray-900">{{ $question->body }}</p>
                            <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses($question->status) }}">
                                {{ $question->status->label() }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $question->asked_by }} &rarr; {{ $question->asked_to }} &bull; {{ $question->asked_at->format('j. n. Y') }}
                            @if ($question->project)
                                &bull;
                                <a href="{{ route('projekty.show', $question->project) }}" class="hover:text-blue-600">
                                    {{ $question->project->code }}
                                </a>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
