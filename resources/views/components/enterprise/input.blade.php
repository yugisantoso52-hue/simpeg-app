@props([
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
])

<input 
    type="{{ $type }}" 
    name="{{ $name }}" 
    id="{{ $name }}" 
    value="{{ old($name, $value) }}" 
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge([
        'class' => 'w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:bg-gray-700 dark:border-gray-600 dark:text-white' . ($errors->has($name) ? ' border-red-500' : '')
    ]) }}
>

{{-- Baris aman jika ada konten slot tak sengaja terselip --}}
@if(isset($slot) && $slot->isNotEmpty())
    {{ $slot }}
@endif