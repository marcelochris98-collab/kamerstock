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
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>

<body class="bg-slate-50 antialiased">

    <div class="flex h-screen overflow-hidden">
        @include('layouts.sidebar')

        <div class="flex flex-col flex-1 overflow-hidden">
            @include('layouts.header')

            <main class="flex-1 overflow-y-auto p-6 bg-slate-100">
                @yield('content')
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

                toast.className = 'fixed right-5 z-[9999] bg-white shadow-lg rounded-xl px-4 py-3 max-w-sm border ' + borderClass;
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
