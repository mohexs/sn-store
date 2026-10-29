@props(['name', 'size' => 'h-5 w-5'])
<svg {{ $attributes->merge(['class' => $size]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('theme') <path d="M12 3a9 9 0 1 0 0 18h1a2 2 0 0 0 1-3.7 1.5 1.5 0 0 1 .8-2.8H17a4 4 0 0 0 4-4C21 6.4 17 3 12 3Z"/><circle cx="7.5" cy="10" r=".7"/><circle cx="11" cy="6.5" r=".7"/><circle cx="16" cy="8" r=".7"/><circle cx="6.5" cy="14.5" r=".7"/> @break
        @case('sun') <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/> @break
        @case('moon') <path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/> @break
        @case('dashboard') <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/> @break
        @case('category') <rect x="3" y="4" width="7" height="7" rx="2"/><path d="m17.5 3 4.5 4.5-4.5 4.5L13 7.5z"/><rect x="3" y="15" width="7" height="7" rx="2"/><rect x="14" y="15" width="7" height="7" rx="2"/> @break
        @case('product') <path d="m12 3 9 5v9l-9 5-9-5V8zM3 8l9 5 9-5M12 13v9M7.5 5.5l9 5"/> @break
        @case('promotion') <path d="m3 11 10-8h7l1 7-10 10a2 2 0 0 1-3 0l-5-5a3 3 0 0 1 0-4Z"/><circle cx="17" cy="7" r="1"/> @break
        @case('ads') <rect x="3" y="4" width="18" height="16" rx="2"/><path d="m10 9 5 3-5 3z"/> @break
        @case('transaction') <path d="M6 3h12v19l-3-2-3 2-3-2-3 2zM9 7h6M9 11h6M9 15h3"/> @break
        @case('income') <rect x="3" y="5" width="18" height="15" rx="3"/><path d="M3 9h18M16 13h5v4h-5zM7 5V3h10"/> @break
        @case('report') <path d="M4 3v18h17M8 16v-5M13 16V7M18 16v-8"/> @break
        @case('users') <circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/> @break
        @case('arrow') <path d="M5 12h14m-6-6 6 6-6 6"/> @break
        @case('download') <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5 5 5 5-5m-5 5V3"/> @break
        @case('external') <path d="M14 3h7v7m0-7L10 14M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('logout') <path d="M9 4H5v16h4m5-12 4 4-4 4m-6-4h13"/> @break
        @case('chevron') <path d="m8 10 4 4 4-4"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v5m10-5v5M3 11h18M7 15h2m4 0h2"/> @break
        @case('shield') <path d="m12 3 8 3v6c0 4-5 8-8 9-3-1-8-5-8-9V6zM8 12l3 3 5-6"/> @break
        @default <circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/>
    @endswitch
</svg>
