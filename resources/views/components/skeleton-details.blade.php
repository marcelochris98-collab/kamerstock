@props([
    'items' => 6
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border border-slate-100 dark:border-white/[0.06] rounded-2xl p-5 sm:p-6 shadow-sm space-y-4 select-none']) }}>
    {{-- En-tête de la fiche --}}
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded skeleton-shimmer w-36"></div>
        <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded-full skeleton-shimmer w-20"></div>
    </div>

    {{-- Lignes clés/valeurs --}}
    <div class="space-y-3 pt-1">
        @for($i = 0; $i < $items; $i++)
        <div class="flex items-center justify-between">
            <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded skeleton-shimmer w-24"></div>
            <div class="h-3.5 bg-slate-200 dark:bg-slate-700 rounded skeleton-shimmer w-{{ ($i % 2 === 0) ? '32' : '28' }}"></div>
        </div>
        @endfor
    </div>
</div>
