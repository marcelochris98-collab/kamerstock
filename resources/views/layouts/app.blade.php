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

<body class="bg-slate-50 antialiased" x-data="{ mobileSidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">
        {{-- Backdrop mobile --}}
        <div x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false" x-cloak class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm md:hidden"></div>

        @include('layouts.sidebar')

        <div class="flex flex-col flex-1 overflow-hidden">
            @include('layouts.header')

            <main class="flex-1 overflow-y-auto p-3 sm:p-6 bg-slate-100">
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        @auth
        window.userSoundsEnabled = {{ auth()->user()->sounds_enabled ? 'true' : 'false' }};
        window.userSoundVolume = {{ auth()->user()->sound_volume ? auth()->user()->sound_volume / 100 : 0.3 }};
        @else
        window.userSoundsEnabled = false;
        window.userSoundVolume = 0.3;
        @endauth

        // 1. Session Toast notifications
        @if(session()->has('toast_notifications'))
        const sessionNotifications = @json(session('toast_notifications'));
        showToasts(sessionNotifications);
        @endif

        @if(session()->has('success') && !session()->has('toast_notifications'))
            if (window.userSoundsEnabled) {
                const audioSuccess = new Audio('/sounds/notification.wav');
                audioSuccess.volume = window.userSoundVolume;
                audioSuccess.play().catch(function () {});
            }
        @endif

        @if(session()->has('error') && !session()->has('toast_notifications'))
            if (window.userSoundsEnabled) {
                const audioError = new Audio('/sounds/notification.wav');
                audioError.volume = window.userSoundVolume;
                audioError.play().catch(function () {});
            }
        @endif

        // 2. Real-time AJAX Polling
        let lastCheckedTime = new Date().toISOString();

        function showToasts(toastsList) {
            toastsList.forEach(function (notification, index) {
                setTimeout(function () {
                    const toast = document.createElement('div');

                    let borderClass = 'border-emerald-100';
                    let titleClass = 'text-emerald-700';

                    if (notification.type === 'warning') {
                        borderClass = 'border-amber-100';
                        titleClass = 'text-amber-700';
                    } else if (notification.type === 'danger') {
                        borderClass = 'border-red-100';
                        titleClass = 'text-red-700';
                    } else if (notification.type === 'info') {
                        borderClass = 'border-blue-100';
                        titleClass = 'text-blue-700';
                    }

                    toast.className = 'fixed right-5 z-[9999] bg-white shadow-lg rounded-xl px-4 py-3 max-w-sm border ' + borderClass + ' flex items-start justify-between gap-3';
                    toast.style.top = (20 + (index * 90)) + 'px';

                    toast.innerHTML = `
                        <div>
                            <p class="text-xs font-bold ${titleClass}">${notification.title}</p>
                            <p class="text-xs text-slate-500 mt-1">${notification.message}</p>
                        </div>
                        <button class="text-slate-400 hover:text-slate-600 focus:outline-none" onclick="this.parentElement.remove()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    `;

                    document.body.appendChild(toast);

                    // Playing sound logic
                    if (notification.sound) {
                        const soundName = typeof notification.sound === 'string' ? notification.sound : 'notification';
                        const audio = new Audio('/sounds/' + soundName + '.wav');
                        audio.volume = parseFloat(notification.volume || 0.3);
                        audio.play().catch(function () {});
                    }

                    setTimeout(function () {
                        if (document.body.contains(toast)) {
                            toast.remove();
                        }
                    }, 10000);
                }, index * 500);
            });
        }

        function pollNotifications() {
            fetch(`/notifications/poll?last_checked=${encodeURIComponent(lastCheckedTime)}`)
                .then(response => response.json())
                .then(data => {
                    lastCheckedTime = data.timestamp;

                    // Display toasts
                    if (data.notifications && data.notifications.length > 0) {
                        const newToasts = data.notifications.map(n => {
                            let type = 'info';
                            if (['stock_low', 'stock_empty', 'credit_overdue'].includes(n.type)) {
                                type = 'danger';
                            }
                            return {
                                type: type,
                                title: n.title,
                                message: n.message,
                                sound: n.sound,
                                volume: n.volume
                            };
                        });
                        showToasts(newToasts);
                    }

                    // Refresh unread count in header bell badge dynamically
                    const badge = document.getElementById('notifBadge');
                    if (badge) {
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }

                    // Refresh CRM messages badge count dynamically
                    const crmBadge = document.getElementById('headerCrmChatBadge');
                    if (crmBadge && typeof data.unread_crm_count !== 'undefined') {
                        if (data.unread_crm_count > 0) {
                            crmBadge.textContent = data.unread_crm_count > 99 ? '99+' : data.unread_crm_count;
                            crmBadge.classList.remove('hidden');
                        } else {
                            crmBadge.classList.add('hidden');
                        }
                    }

                    if (window.location.pathname.includes('/admin/crm-messages')) {
                        if (typeof refreshAdminChat === 'function') {
                            refreshAdminChat();
                        }
                    }
                })
                .catch(err => console.error('Error polling notifications:', err));
        }

        // Start polling every 15s if user is authenticated
        if ({{ auth()->check() ? 'true' : 'false' }}) {
            setInterval(pollNotifications, 15000);
        }
    });
    </script>

    @stack('scripts')
</body>
</html>
