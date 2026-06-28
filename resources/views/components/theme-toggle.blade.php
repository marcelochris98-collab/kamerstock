{{-- AJOUT: bouton dark/light mode, pilote par le script global du layout. --}}
<button type="button" id="theme-toggle"
    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
    title="{{ __('app.theme_dark') }}"
    aria-label="{{ __('app.theme_dark') }}">
    <svg class="h-4 w-4 dark:hidden" data-theme-icon="moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
    </svg>
    <svg class="hidden h-4 w-4 dark:block" data-theme-icon="sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36-6.36-1.42 1.42M7.06 16.94l-1.42 1.42m12.72 0-1.42-1.42M7.06 7.06 5.64 5.64M12 8a4 4 0 100 8 4 4 0 000-8z"/>
    </svg>
</button>
