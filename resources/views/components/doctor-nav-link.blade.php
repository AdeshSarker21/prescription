@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl bg-gradient-to-r from-indigo-500/15 to-indigo-500/5 text-indigo-700 shadow-sm border border-indigo-200/30'
            : 'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl text-gray-600 hover:bg-white/40 hover:text-gray-900 transition-all duration-200 border border-transparent hover:border-white/30';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
