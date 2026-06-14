<section>
    <header>
        <h2 class="text-sm font-semibold text-slate-800">
            {{ __('Paramètres de Notification') }}
        </h2>

        <p class="mt-1 text-xs text-slate-400">
            {{ __('Gérez vos préférences de notifications et d\'alertes sonores pour les différents modules.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.notifications.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <!-- Enable/Disable notifications -->
        <div class="flex items-center gap-2.5">
            <input type="checkbox" name="notifications_enabled" id="notifications_enabled" value="1" 
                {{ $user->notifications_enabled ? 'checked' : '' }}
                class="w-4 h-4 accent-slate-900 border-slate-300 rounded text-slate-900 focus:ring-slate-900">
            <label for="notifications_enabled" class="text-xs font-semibold text-slate-700">
                {{ __('Activer les notifications du système') }}
            </label>
        </div>

        <!-- Enable/Disable sounds -->
        <div class="flex items-center gap-2.5">
            <input type="checkbox" name="sounds_enabled" id="sounds_enabled" value="1" 
                {{ $user->sounds_enabled ? 'checked' : '' }}
                class="w-4 h-4 accent-slate-900 border-slate-300 rounded text-slate-900 focus:ring-slate-900">
            <label for="sounds_enabled" class="text-xs font-semibold text-slate-700">
                {{ __('Activer les alertes sonores') }}
            </label>
        </div>

        <!-- Volume slider -->
        <div class="space-y-1 max-w-sm">
            <label for="sound_volume" class="block text-[11px] font-semibold text-slate-500">
                {{ __('Volume du son (%)') }}
            </label>
            <div class="flex items-center gap-3">
                <input type="range" name="sound_volume" id="sound_volume" min="0" max="100" value="{{ $user->sound_volume ?? 50 }}" 
                    class="w-full h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-slate-900">
                <span class="text-xs font-bold text-slate-700 w-8 text-right" id="volume_display">{{ $user->sound_volume ?? 50 }}%</span>
            </div>
        </div>

        <!-- Categories of notifications to receive -->
        <div class="space-y-2">
            <p class="text-[11px] font-semibold text-slate-500">{{ __('Catégories d\'alertes autorisées') }}</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @php
                    $categories = $user->notification_categories ?? ['messaging', 'sales', 'stock', 'finance', 'admin'];
                @endphp
                @foreach([
                    'messaging' => 'Messagerie / Chat',
                    'sales' => 'Ventes & POS',
                    'stock' => 'Mouvements Stock',
                    'finance' => 'Crédits & Rapports',
                    'admin' => 'Activité Admin'
                ] as $slug => $label)
                <label class="flex items-center gap-2 px-3 py-2 border border-slate-100 rounded-lg bg-slate-50/50 cursor-pointer hover:bg-slate-100 transition">
                    <input type="checkbox" name="categories[]" value="{{ $slug }}" {{ in_array($slug, $categories) ? 'checked' : '' }}
                        class="w-3.5 h-3.5 accent-slate-900 border-slate-200 text-slate-900 rounded">
                    <span class="text-xs text-slate-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 transition">
                {{ __('Sauvegarder') }}
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('sound_volume');
            const display = document.getElementById('volume_display');
            if(slider && display) {
                slider.addEventListener('input', function() {
                    display.textContent = this.value + '%';
                });
            }
        });
    </script>
</section>
