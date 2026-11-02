@props([
    'rows' => 5,
    'cols' => 5,
    'hasActions' => true,
    'showHeader' => true
])

<div {{ $attributes->merge(['class' => 'w-full bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-white/[0.06] overflow-hidden select-none']) }}>
    <table class="w-full text-left">
        @if($showHeader)
        <thead>
            <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50">
                @for($c = 0; $c < $cols; $c++)
                <th class="px-5 py-3">
                    <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-md skeleton-shimmer w-{{ ($c == 0) ? '24' : (($c == $cols - 1 && $hasActions) ? '12 ml-auto' : '16') }}"></div>
                </th>
                @endfor
            </tr>
        </thead>
        @endif

        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
            @for($r = 0; $r < $rows; $r++)
            <tr class="animate-pulse">
                @for($c = 0; $c < $cols; $c++)
                <td class="px-5 py-3.5">
                    @if($c === 0)
                        {{-- Première colonne : Titre + Sous-titre --}}
                        <div class="space-y-1.5">
                            <div class="h-3.5 bg-slate-200 dark:bg-slate-700 rounded skeleton-shimmer w-36"></div>
                            <div class="h-2.5 bg-slate-150 dark:bg-slate-800 rounded skeleton-shimmer w-20"></div>
                        </div>
                    @elseif($c === $cols - 1 && $hasActions)
                        {{-- Dernière colonne : Bouton d'action --}}
                        <div class="flex justify-end">
                            <div class="h-7 bg-slate-200 dark:bg-slate-700 rounded-lg skeleton-shimmer w-16"></div>
                        </div>
                    @elseif($c === $cols - 2)
                        {{-- Avant-dernière colonne : Badge de statut --}}
                        <div class="flex justify-center">
                            <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded-full skeleton-shimmer w-16"></div>
                        </div>
                    @else
                        {{-- Colonnes classiques : Texte ou montant --}}
                        <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded skeleton-shimmer w-{{ ($c % 2 === 0) ? '24' : '20' }}"></div>
                    @endif
                </td>
                @endfor
            </tr>
            @endfor
        </tbody>
    </table>
</div>
