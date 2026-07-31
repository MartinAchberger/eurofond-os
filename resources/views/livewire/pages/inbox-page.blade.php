<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Inbox</h1>
        <p class="mt-1 text-sm text-gray-500">AI klasifikácia príde v Míľniku 5.</p>
    </div>

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
                    <li class="px-5 py-4">
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
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
