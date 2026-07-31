<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Požiadavky</h2>
    </div>

    @if ($this->questions->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne požiadavky.</p>
    @else
        <div class="divide-y divide-gray-100">
            @foreach ($this->questions as $question)
                @php
                    $statusClasses = match ($question->status) {
                        \App\Enums\QuestionStatus::Otvorena => 'bg-amber-100 text-amber-700',
                        \App\Enums\QuestionStatus::Zodpovedana => 'bg-emerald-100 text-emerald-700',
                        \App\Enums\QuestionStatus::Uzavreta => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <div class="px-5 py-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">{{ $question->body }}</p>
                        <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses }}">
                            {{ $question->status->label() }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $question->asked_by }} &rarr; {{ $question->asked_to }} &bull; {{ $question->asked_at->format('j. n. Y') }}
                    </p>

                    @if ($question->answers->isNotEmpty())
                        <div class="mt-3 space-y-2 border-l-2 border-gray-100 pl-4">
                            @foreach ($question->answers as $answer)
                                @php
                                    $bindingClasses = match ($answer->bindingness) {
                                        \App\Enums\AnswerBindingness::Zavazne => 'bg-emerald-100 text-emerald-700',
                                        \App\Enums\AnswerBindingness::Pracovne => 'bg-blue-100 text-blue-700',
                                        \App\Enums\AnswerBindingness::Neformalne => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm text-gray-900">{{ $answer->body }}</p>
                                        <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium {{ $bindingClasses }}">
                                            {{ $answer->bindingness->label() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{ $answer->answered_by }} &bull; {{ $answer->answered_at->format('j. n. Y') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
