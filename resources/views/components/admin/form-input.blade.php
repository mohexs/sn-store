@props(['name', 'label', 'value' => null, 'type' => 'text', 'hint' => null, 'required' => false])
@php
    $isDateInput = in_array($type, ['date', 'datetime-local'], true);
    $storedValue = old($name, $value);
    $displayValue = old($name.'_display');
    if ($displayValue === null && is_string($storedValue)) {
        $displayValue = match ($type) {
            'date' => preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $storedValue, $matches)
                ? $matches[3].'/'.$matches[2].'/'.$matches[1]
                : $storedValue,
            'datetime-local' => preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}:\d{2})$/', $storedValue, $matches)
                ? $matches[3].'/'.$matches[2].'/'.$matches[1].' '.$matches[4]
                : $storedValue,
            default => $storedValue,
        };
    }
    $fieldHint = $hint ?? ($isDateInput ? 'Format tanggal: dd/mm/yyyy'.($type === 'datetime-local' ? ' (waktu 24 jam)' : '') : null);
    $describedBy = trim(implode(' ', array_filter([
        ($hint || $isDateInput) ? $name.'-hint' : null,
        $errors->has($name) ? $name.'-error' : null,
    ])));
@endphp
<x-admin.form-field :name="$name" :label="$label" :hint="$fieldHint" :required="$required">
    @if ($isDateInput)
        <div class="relative flex gap-2" data-date-control data-date-type="{{ $type }}">
            <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ $displayValue }}" placeholder="{{ $type === 'date' ? 'dd/mm/yyyy' : 'dd/mm/yyyy --:--' }}" inputmode="{{ $type === 'date' ? 'numeric' : 'text' }}" pattern="{{ $type === 'date' ? '[0-9]{2}/[0-9]{2}/[0-9]{4}' : '[0-9]{2}/[0-9]{2}/[0-9]{4} [0-9]{2}:[0-9]{2}' }}" data-date-display @required($required) aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif {{ $attributes->class(['min-w-0 flex-1 rounded-xl border bg-white px-3.5 py-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-brand-500 focus:ring-brand-500', 'border-rose-400' => $errors->has($name), 'border-slate-200' => ! $errors->has($name)]) }}>
            <input type="hidden" value="{{ is_string($storedValue) ? $storedValue : '' }}" data-date-value>
            <input type="{{ $type }}" lang="id" value="{{ is_string($storedValue) ? $storedValue : '' }}" tabindex="-1" aria-hidden="true" data-date-picker class="pointer-events-none absolute right-0 top-0 h-12 w-12 opacity-0">
            <button type="button" data-date-picker-button aria-label="Pilih tanggal untuk {{ $label }}" class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-brand-700"><x-admin.icon name="calendar" size="h-5 w-5" /></button>
        </div>
    @else
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @required($required) aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif {{ $attributes->class(['w-full rounded-xl border bg-white px-3.5 py-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-brand-500 focus:ring-brand-500', 'border-rose-400' => $errors->has($name), 'border-slate-200' => ! $errors->has($name)]) }}>
    @endif
</x-admin.form-field>
