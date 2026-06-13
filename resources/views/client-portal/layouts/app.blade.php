<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KamerStock | @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>

<body class="bg-slate-50 antialiased">

<div
    x-data="{
        mobileSidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
        }
    }"
    class="min-h-screen">

    {{-- Sidebar desktop --}}
    <div class="hidden lg:flex fixed inset-y-0 left-0 z-40">
        @include('layouts.sidebar')
    </div>

    {{-- Overlay mobile --}}
    <div x-show="mobileSidebarOpen"
         x-transition.opacity
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden">
    </div>

    {{-- Sidebar mobile --}}
    <div x-show="mobileSidebarOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 lg:hidden">
        @include('layouts.sidebar')
    </div>

    {{-- Contenu --}}
    <div
        :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-56'"
        class="min-h-screen flex flex-col transition-all duration-300">

        @include('layouts.header')

        <main class="flex-1 overflow-y-auto bg-slate-100 p-3 sm:p-4 lg:p-6">
            <div class="max-w-full overflow-x-auto">
                @yield('content')
            </div>
        </main>
    </div>

</div>

@if(session()->has('toast_notifications'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const notifications = @json(session('toast_notifications'));

    notifications.forEach(function (notification, index) {
        setTimeout(function () {
            const toast = document.createElement('div');

            let borderClass = 'border-emerald-100';
            let titleClass = 'text-emerald-700';

            if (notification.type === 'warning') {
                borderClass = 'border-amber-100';
                titleClass = 'text-amber-700';
            }

            if (notification.type === 'danger') {
                borderClass = 'border-red-100';
                titleClass = 'text-red-700';
            }

            if (notification.type === 'info') {
                borderClass = 'border-blue-100';
                titleClass = 'text-blue-700';
            }

            toast.className = 'fixed left-3 right-3 sm:left-auto sm:right-5 z-[9999] bg-white shadow-lg rounded-xl px-4 py-3 sm:max-w-sm border ' + borderClass;
            toast.style.top = (20 + (index * 90)) + 'px';

            toast.innerHTML = `
                <p class="text-xs font-bold ${titleClass}">${notification.title}</p>
                <p class="text-xs text-slate-500 mt-1">${notification.message}</p>
            `;

            document.body.appendChild(toast);

            if (notification.sound) {
                const audio = new Audio('/sounds/notification.wav');
                audio.volume = 0.2;
                audio.play().catch(function () {});
            }

            setTimeout(function () {
                toast.remove();
            }, 4500);
        }, index * 500);
    });
});
</script>
@endif

@stack('scripts')
</body>
</html>