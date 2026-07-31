<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    @php
        $cards = [
            [
                'label' => 'Aktívne projekty',
                'value' => $activeProjects,
                'icon' => 'M3.75 8.25h16.5v9.75a1.5 1.5 0 01-1.5 1.5h-13.5a1.5 1.5 0 01-1.5-1.5V8.25zM8.25 8.25V6a1.5 1.5 0 011.5-1.5h4.5A1.5 1.5 0 0115.75 6v2.25',
                'color' => 'blue',
                'link' => 'Zobraziť všetky projekty',
                'route' => 'projekty.index',
            ],
            [
                'label' => 'Blížiace sa termíny',
                'value' => $upcomingDeadlines,
                'icon' => 'M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M4.5 6h15a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75V6.75A.75.75 0 014.5 6z',
                'color' => 'green',
                'link' => 'Zobraziť termíny',
                'route' => 'dnes',
            ],
            [
                'label' => 'Otvorené riziká',
                'value' => $openRisks,
                'icon' => 'M12 2.75l7.5 3v6c0 4.5-3.15 8.25-7.5 9.5-4.35-1.25-7.5-5-7.5-9.5v-6l7.5-3z',
                'color' => 'orange',
                'link' => 'Zobraziť riziká',
                'route' => 'rizika.index',
            ],
            [
                'label' => 'Čaká sa na klienta',
                'value' => $waitingOnClient,
                'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
                'color' => 'violet',
                'link' => 'Zobraziť požiadavky',
                'route' => 'ulohy.index',
            ],
        ];

        $iconBg = [
            'blue' => 'bg-blue-100 text-blue-600',
            'green' => 'bg-green-100 text-green-600',
            'orange' => 'bg-orange-100 text-orange-600',
            'violet' => 'bg-violet-100 text-violet-600',
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <div class="flex items-center gap-4">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl {{ $iconBg[$card['color']] }}">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $card['icon'] }}" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                </div>
            </div>
            <a href="{{ route($card['route']) }}"
               class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700">
                {{ $card['link'] }}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    @endforeach
</div>
