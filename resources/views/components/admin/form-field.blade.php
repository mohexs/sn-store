@props(['name', 'label', 'hint' => null, 'required' => false])
<div {{ $attributes }}>
    <label for="{{ $name }}" class="mb-2 block text-xs font-semibold text-slate-700">{{ $label }} @if ($required)<span class="text-brand-600" aria-hidden="true">*</span>@endif</label>
    {{ $slot }}
    @if ($hint)<p id="{{ $name }}-hint" class="mt-2 text-[11px] leading-5 text-slate-400">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $name }}-error" role="alert" class="mt-2 text-xs text-rose-700">{{ $message }}</p>@enderror
</div>
