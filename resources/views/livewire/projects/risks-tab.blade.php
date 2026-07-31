<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
    <div class="border-b border-gray-100 px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900">Riziká</h2>
    </div>

    @if ($this->risks->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-500">Žiadne riziká.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs font-medium uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Riziko</th>
                        <th class="px-5 py-3">Dopad</th>
                        <th class="px-5 py-3">Pravdepodobnosť</th>
                        <th class="px-5 py-3">Mitigácia</th>
                        <th class="px-5 py-3">Stav</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $levelClasses = fn ($level) => match ($level) {
                            \App\Enums\RiskLevel::Vysoky => 'bg-red-100 text-red-700',
                            \App\Enums\RiskLevel::Stredny => 'bg-amber-100 text-amber-700',
                            \App\Enums\RiskLevel::Nizky => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    @foreach ($this->risks as $risk)
                        <tr>
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $risk->title }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $levelClasses($risk->impact) }}">
                                    {{ $risk->impact->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $levelClasses($risk->likelihood) }}">
                                    {{ $risk->likelihood->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $risk->mitigation ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $risk->status->label() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
