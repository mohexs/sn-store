@props(['name', 'label', 'value' => null, 'type' => 'text', 'hint' => null, 'required' => false])
<x-admin.form-field :name="$name" :label="$label" :hint="$hint" :required="$required">
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @required($required) aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" @if ($hint || $errors->has($name)) aria-describedby="{{ trim(($hint ? $name.'-hint ' : '').($errors->has($name) ? $name.'-error' : '')) }}" @endif {{ $attributes->class(['w-full rounded-xl border bg-white px-3.5 py-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-brand-500 focus:ring-brand-500', 'border-rose-400' => $errors->has($name), 'border-slate-200' => ! $errors->has($name)]) }}>
</x-admin.form-field>
