@props([
    'count' => 4,
    'cols' => 4
])

@php
    $gridCols = match((int)$cols) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp

<div {{ $attributes->merge(['class' => "grid {$gridCols} gap-4 sm:gap-5 w-full select-none"]) }}>
    @for($i = 0; $i < $count; $i++)
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-white/[0.06] rounded-2xl p-4 sm:p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            {{-- Libellé du KPI --}}
            <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-md skeleton-shimmer w-24"></div>
            {{-- Cercle d'icône --}}
            <div class="w-8 h-8 rounded-xl bg-slate-200 dark:bg-slate-700 skeleton-shimmer shrink-0"></div>
        </div>

        {{-- Grande valeur principale --}}
        <div class="space-y-1.5 pt-1">
            <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded-lg skeleton-shimmer w-32"></div>
            {{-- Sous-titre ou badge d'évolution --}}
            <div class="h-2.5 bg-slate-150 dark:bg-slate-800 rounded skeleton-shimmer w-20"></div>
        </div>
    </div>
    @endfor
</div>
