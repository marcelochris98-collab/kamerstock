@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-amber-400 text-start text-base font-medium text-amber-700 bg-amber-50 focus:outline-none focus:text-amber-800 focus:bg-amber-100 focus:border-amber-700 transition duration-150 ease-in-out dark:bg-amber-500/10 dark:text-amber-300'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800 dark:hover:border-slate-700';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
