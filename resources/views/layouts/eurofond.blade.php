<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'EUROFOND OS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900">
<div class="flex min-h-screen">
    <aside class="flex w-60 shrink-0 flex-col border-r border-gray-200 bg-white">
        <div class="flex items-center gap-2 px-5 py-5">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-blue-600 text-sm font-bold text-white">E</span>
            <span class="text-lg font-bold tracking-tight">EUROFOND OS</span>
        </div>
        <nav class="flex-1 space-y-1 px-3">
            @php
                $icons = [
                    'dashboard' => 'M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 11-1.06 1.06l-.97-.97V19.5a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25V12.62l-.97.97a.75.75 0 01-1.06-1.06l8.69-8.69z',
                    'dnes' => 'M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M4.5 6h15a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75V6.75A.75.75 0 014.5 6z',
                    'inbox' => 'M3 8.25l1.5-4.5A1.5 1.5 0 016 2.75h12a1.5 1.5 0 011.5 1L21 8.25m-18 0v10.5a1.5 1.5 0 001.5 1.5h15a1.5 1.5 0 001.5-1.5V8.25m-18 0h4.5a3 3 0 006 0H21',
                    'projekty.index' => 'M3.75 8.25h16.5v9.75a1.5 1.5 0 01-1.5 1.5h-13.5a1.5 1.5 0 01-1.5-1.5V8.25zM8.25 8.25V6a1.5 1.5 0 011.5-1.5h4.5A1.5 1.5 0 0115.75 6v2.25',
                    'dokumenty.index' => 'M7.5 3.75h6l4.5 4.5v10.5a1.5 1.5 0 01-1.5 1.5h-9a1.5 1.5 0 01-1.5-1.5V5.25a1.5 1.5 0 011.5-1.5zM13.5 3.75V8.25h4.5',
                    'poziadavky.index' => 'M4.5 5.25h15a.75.75 0 01.75.75v9a.75.75 0 01-.75.75H9l-4.5 4.5V15h-.75a.75.75 0 01-.75-.75V6a.75.75 0 01.75-.75z',
                    'ulohy.index' => 'M9 12.75l2.25 2.25L15 10.5m6 1.5a9 9 0 11-18 0 9 9 0 0118 0z',
                    'rizika.index' => 'M12 2.75l7.5 3v6c0 4.5-3.15 8.25-7.5 9.5-4.35-1.25-7.5-5-7.5-9.5v-6l7.5-3z',
                    'rozhodnutia.index' => 'M12 3v18M5.25 6.75H3l3 6-3 6h6.75M18.75 6.75H21l-3 6 3 6h-6.75M5.25 6.75L12 3l6.75 3.75',
                    'nastavenia' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.774.773c.39.39.44 1.002.12 1.45l-.527.738c-.25.35-.272.806-.108 1.204.166.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.764.383-.93.78-.164.398-.142.854.108 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.108-.397.166-.71.505-.781.93l-.149.893c-.09.542-.56.94-1.11.94h-1.093c-.55 0-1.02-.398-1.11-.94l-.148-.893c-.071-.425-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527a1.125 1.125 0 01-1.449-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.164-.398.142-.854-.108-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.806.272 1.204.108.397-.166.71-.505.78-.93l.15-.894z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                ];
                $sections = [
                    ['Dashboard', 'dashboard'], ['Dnes', 'dnes'], ['Inbox', 'inbox'],
                    ['Projekty', 'projekty.index'], ['Dokumenty', 'dokumenty.index'],
                    ['Požiadavky', 'poziadavky.index'], ['Úlohy', 'ulohy.index'],
                    ['Riziká', 'rizika.index'], ['Rozhodnutia', 'rozhodnutia.index'],
                    ['Nastavenia', 'nastavenia'],
                ];
            @endphp
            @foreach ($sections as [$label, $routeName])
                @php
                    $activePattern = str_contains($routeName, '.') ? explode('.', $routeName)[0].'.*' : $routeName;
                @endphp
                <a href="{{ route($routeName) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium
                          {{ request()->routeIs($activePattern) ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$routeName] }}" />
                    </svg>
                    <span class="flex-1">{{ $label }}</span>
                    @if ($routeName === 'inbox')
                        @php $noveCount = \App\Models\InboxItem::where('status', \App\Enums\InboxItemStatus::Nove)->count(); @endphp
                        @if ($noveCount > 0)
                            <span class="rounded-full px-2 text-xs font-semibold {{ request()->routeIs($activePattern) ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700' }}">{{ $noveCount }}</span>
                        @endif
                    @endif
                </a>
            @endforeach
        </nav>
        <div class="border-t border-gray-200 p-4 text-xs text-gray-500">
            <p class="font-semibold text-gray-700">Lokálne / bezpečné</p>
            <p>Všetky dáta sú uložené lokálne.</p>
        </div>
    </aside>
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-4 border-b border-gray-200 bg-white px-6 py-3">
            <input type="search" placeholder="Vyhľadajte projekty, dokumenty, úlohy…"
                   class="w-full max-w-md rounded-lg border-gray-200 bg-gray-50 text-sm" disabled>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium">{{ auth()->user()?->name }}</span>
                <livewire:layout-logout />
            </div>
        </header>
        <main class="flex-1 p-6">{{ $slot }}</main>
    </div>
</div>
</body>
</html>
