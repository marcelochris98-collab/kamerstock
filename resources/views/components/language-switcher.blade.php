@php
    $currentLocale = app()->getLocale();
@endphp

{{-- AJOUT: switcher FR/EN memorise en session via la route lang.switch. --}}
<div class="inline-flex items-center overflow-hidden rounded-lg border border-slate-200 bg-white text-[11px] font-semibold shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <a href="{{ route('lang.switch', 'fr') }}"
        title="{{ __('app.french') }}"
        class="px-2.5 py-1 transition {{ $currentLocale === 'fr' ? 'bg-amber-500 text-slate-950' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
        FR
    </a>
    <a href="{{ route('lang.switch', 'en') }}"
        title="{{ __('app.english') }}"
        class="px-2.5 py-1 transition {{ $currentLocale === 'en' ? 'bg-amber-500 text-slate-950' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }}">
        EN
    </a>
</div>
