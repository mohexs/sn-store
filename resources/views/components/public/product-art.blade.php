@props(['type' => 'bottle', 'color' => '#c84451', 'label' => 'HEALTH'])
<svg {{ $attributes->merge(['class' => 'h-36 w-36 drop-shadow-lg']) }} viewBox="0 0 180 180" fill="none" aria-hidden="true">
    <ellipse cx="90" cy="160" rx="49" ry="8" fill="#202b3a" opacity=".06"/>
    @if ($type === 'bottle')
        <rect x="62" y="21" width="56" height="25" rx="5" fill="#f1eee8"/><path d="M67 25v17m7-17v17m8-17v17m8-17v17m8-17v17m8-17v17" stroke="#dedbd4"/>
        <rect x="55" y="44" width="70" height="112" rx="17" fill="#faf9f5" stroke="#e5e1da"/><rect x="55" y="75" width="70" height="59" fill="{{ $color }}"/><path d="M61 56v88" stroke="white" stroke-width="5" opacity=".7"/>
        <path d="M86 85h8v7h7v8h-7v7h-8v-7h-7v-8h7z" fill="white"/><text x="90" y="120" text-anchor="middle" fill="white" font-size="7" font-family="sans-serif" font-weight="700">{{ $label }}</text>
    @elseif ($type === 'device')
        <rect x="40" y="42" width="100" height="112" rx="24" fill="#f8fafc" stroke="#d8e0e5" stroke-width="2"/><rect x="52" y="56" width="76" height="55" rx="8" fill="#c9d9cc"/><text x="90" y="90" text-anchor="middle" fill="#48645c" font-family="monospace" font-size="26">120</text><rect x="65" y="122" width="50" height="15" rx="7" fill="{{ $color }}"/>
    @else
        <path d="m34 52 86-15 29 20v88l-86 13-29-19z" fill="#faf9f5" stroke="#e1ded8"/><path d="m120 37 29 20v88l-29-16z" fill="{{ $color }}" opacity=".5"/><path d="m34 86 86-14v43l-86 14z" fill="{{ $color }}"/><path d="M70 89h9v8h8v9h-8v8h-9v-8h-8v-9h8z" fill="white"/><text x="75" y="143" text-anchor="middle" fill="#667085" font-size="7" font-family="sans-serif">SN HEALTH</text>
    @endif
</svg>
