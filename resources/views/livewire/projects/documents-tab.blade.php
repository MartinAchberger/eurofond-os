<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Dokumenty</h2>
    </div>

    @if ($this->documents->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne dokumenty.</p>
    @else
        <div class="divide-y divide-gray-100">
            @foreach ($this->documents as $document)
                <div class="px-5 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $document->title }}</h3>
                        @if ($document->type)
                            <span class="text-xs text-gray-500">{{ $document->type->name }}</span>
                        @endif
                    </div>

                    <div class="mt-3 divide-y divide-gray-50 rounded-lg border border-gray-100">
                        @forelse ($document->versions as $version)
                            @php
                                $statusClasses = match ($version->status) {
                                    \App\Enums\DocumentVersionStatus::Aktualna => 'bg-emerald-100 text-emerald-700',
                                    \App\Enums\DocumentVersionStatus::Nepotvrdena => 'bg-amber-100 text-amber-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">{{ $version->version_label }}</span>
                                    <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses }}">
                                        {{ $version->status->label() }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if ($version->issued_at)
                                        {{ $version->issued_at->format('j. n. Y') }}
                                    @endif
                                    @if ($version->confirmedBy && $version->confirmed_at)
                                        &bull; Potvrdil {{ $version->confirmedBy->name }} dňa {{ $version->confirmed_at->format('j. n. Y') }}
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-3 py-4 text-center text-sm text-gray-500">Žiadne verzie.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
