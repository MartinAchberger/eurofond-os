<div class="space-y-6">
    <h1 class="text-2xl font-bold">Dokumenty</h1>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        @if ($this->documents->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne dokumenty.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Dokument</th>
                            <th class="px-5 py-3">Typ</th>
                            <th class="px-5 py-3">Projekt</th>
                            <th class="px-5 py-3">Aktuálna verzia</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $statusClasses = fn ($status) => match ($status) {
                                \App\Enums\DocumentVersionStatus::Aktualna => 'bg-emerald-100 text-emerald-700',
                                \App\Enums\DocumentVersionStatus::Nepotvrdena => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        @foreach ($this->documents as $document)
                            @php $version = $document->versions->first(); @endphp
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $document->title }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $document->type?->name }}</td>
                                <td class="px-5 py-3 text-gray-500">
                                    @if ($document->project)
                                        <a href="{{ route('projekty.show', $document->project) }}" class="hover:text-blue-600">
                                            {{ $document->project->code }}
                                        </a>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if ($version)
                                        <span class="text-gray-900">{{ $version->version_label }}</span>
                                        <span class="ml-2 rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClasses($version->status) }}">
                                            {{ $version->status->label() }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if ($version && $version->file_path)
                                        <a href="{{ route('dokumenty.stiahnut', $version) }}"
                                           wire:key="download-{{ $version->id }}"
                                           class="rounded-md border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            Stiahnuť
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
