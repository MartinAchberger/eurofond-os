<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="flex flex-wrap items-center justify-between gap-y-3 border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Požiadavky</h2>
        <livewire:projects.create-question-form :project="$project" />
    </div>

    @if ($error)
        <div class="mx-5 mt-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 ring-1 ring-red-200">
            {{ $error }}
        </div>
    @endif

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
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses }}">
                                {{ $question->status->label() }}
                            </span>
                            @if ($question->status !== \App\Enums\QuestionStatus::Uzavreta)
                                <button type="button" wire:click="closeQuestion({{ $question->id }})"
                                        wire:confirm="Naozaj uzavrieť túto otázku?"
                                        class="rounded-md px-2 py-0.5 text-xs font-medium text-gray-500 ring-1 ring-gray-200 hover:bg-gray-50 hover:text-gray-700">
                                    Uzavrieť
                                </button>
                            @endif
                        </div>
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

                    <livewire:projects.answer-question-form :question="$question" :wire:key="'answer-form-'.$question->id" />
                </div>
            @endforeach
        </div>
    @endif
</div>
