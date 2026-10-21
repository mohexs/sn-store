@props(['label', 'value', 'description', 'icon', 'highlight' => false])
<article {{ $attributes->class(['rounded-2xl border bg-white p-5 shadow-sm shadow-slate-200/20', 'border-brand-200' => $highlight, 'border-slate-200/80' => ! $highlight]) }}>
    <div class="flex items-center justify-between gap-3"><h2 class="text-sm font-medium text-slate-500">{{ $label }}</h2><span @class(['flex h-10 w-10 items-center justify-center rounded-xl', 'bg-brand-50 text-brand-700' => $highlight, 'bg-slate-50 text-slate-500' => ! $highlight])><x-admin.icon :name="$icon" /></span></div>
    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ $value }}</p>
    <p class="mt-3 flex items-center gap-2 text-xs leading-5 text-slate-500"><span @class(['h-1.5 w-1.5 shrink-0 rounded-full', 'bg-brand-500' => $highlight, 'bg-slate-300' => ! $highlight])></span>{{ $description }}</p>
</article>
