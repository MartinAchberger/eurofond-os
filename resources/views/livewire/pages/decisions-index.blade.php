<div class="space-y-6">
    <h1 class="text-2xl font-bold">Rozhodnutia</h1>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        @if ($this->decisions->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne rozhodnutia.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($this->decisions as $decision)
                    <li class="px-5 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ $decision->body }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            Schválil: {{ $decision->approved_by }} &bull; {{ $decision->approved_at?->format('j. n. Y') }}
                            @if ($decision->project)
                                &bull;
                                <a href="{{ route('projekty.show', $decision->project) }}" class="hover:text-blue-600">
                                    {{ $decision->project->code }}
                                </a>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
