<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 focus:bg-slate-700 active:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-white transition ease-in-out duration-150 dark:bg-amber-500 dark:text-slate-950 dark:hover:bg-amber-400 dark:focus:ring-offset-slate-900']) }}>
    {{ $slot }}
</button>
