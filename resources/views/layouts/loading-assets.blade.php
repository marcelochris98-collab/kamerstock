<style>
/* ========================================================================= */
/* SKELETON SHIMMER ANIMATION (KAMERSTOCK DESIGN SYSTEM)                     */
/* ========================================================================= */
@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.skeleton-shimmer {
    background: linear-gradient(90deg, #e2e8f0 25%, #f8fafc 50%, #e2e8f0 75%) !important;
    background-size: 200% 100% !important;
    animation: shimmer 1.5s infinite linear !important;
}

.dark .skeleton-shimmer {
    background: linear-gradient(90deg, #1e293b 25%, #334155 50%, #1e293b 75%) !important;
    background-size: 200% 100% !important;
    animation: shimmer 1.5s infinite linear !important;
}

/* ========================================================================= */
/* BARRE DE PROGRESSION EN HAUT D'ÉCRAN (TOP LOADING BAR)                    */
/* ========================================================================= */
#global-top-progress {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    height: 3px !important;
    width: 0% !important;
    z-index: 9999999 !important;
    background: linear-gradient(90deg, #f59e0b, #fbbf24, #d97706) !important;
    box-shadow: 0 0 10px rgba(245, 158, 11, 0.8), 0 0 5px rgba(251, 191, 36, 1) !important;
    transition: width 0.2s ease-out, opacity 0.3s ease !important;
    pointer-events: none !important;
    opacity: 0;
}

#global-top-progress.landlord {
    background: linear-gradient(90deg, #6366f1, #818cf8, #4f46e5) !important;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.8), 0 0 5px rgba(129, 140, 248, 1) !important;
}

/* ========================================================================= */
/* ÉTATS DE CHARGEMENT SUR LES BOUTONS (SPINNER + ANTI-DOUBLE CLIC)          */
/* ========================================================================= */
.btn-is-loading {
    position: relative !important;
    pointer-events: none !important;
    opacity: 0.75 !important;
    cursor: not-allowed !important;
}

.btn-spinner {
    display: inline-block;
    width: 0.9rem;
    height: 0.9rem;
    margin-right: 0.4rem;
    vertical-align: -0.125em;
    border: 2px solid currentColor;
    border-right-color: transparent !important;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

[x-cloak] { display: none !important; }
</style>

<script>
(function() {
    class LoadingEngine {
        constructor() {
            this.bar = null;
            this.progress = 0;
            this.timer = null;
            this.init();
        }

        init() {
            const createBar = () => {
                if (!document.getElementById('global-top-progress')) {
                    this.bar = document.createElement('div');
                    this.bar.id = 'global-top-progress';
                    if (window.location.pathname.startsWith('/landlord')) {
                        this.bar.classList.add('landlord');
                    }
                    document.body.prepend(this.bar);
                } else {
                    this.bar = document.getElementById('global-top-progress');
                }
                this.bindEvents();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', createBar);
            } else {
                createBar();
            }
        }

        start() {
            if (!this.bar) return;
            clearInterval(this.timer);
            this.progress = 20;
            this.bar.style.opacity = '1';
            this.bar.style.width = this.progress + '%';

            this.timer = setInterval(() => {
                if (this.progress < 75) {
                    this.progress += Math.floor(Math.random() * 15) + 5;
                } else if (this.progress < 90) {
                    this.progress += Math.floor(Math.random() * 3) + 1;
                }
                if (this.bar) {
                    this.bar.style.width = this.progress + '%';
                }
            }, 180);
        }

        done() {
            if (!this.bar) return;
            clearInterval(this.timer);
            this.progress = 100;
            this.bar.style.width = '100%';

            setTimeout(() => {
                if (this.bar) {
                    this.bar.style.opacity = '0';
                    setTimeout(() => {
                        if (this.bar) {
                            this.bar.style.width = '0%';
                            this.progress = 0;
                        }
                    }, 250);
                }
            }, 200);
        }

        bindEvents() {
            // Clic sur les liens de navigation
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.hasAttribute('download') || link.target === '_blank') {
                    return;
                }

                if (e.ctrlKey || e.metaKey || e.shiftKey) return;

                try {
                    const dest = new URL(link.href, window.location.origin);
                    if (dest.origin === window.location.origin && dest.pathname !== window.location.pathname) {
                        this.start();
                    }
                } catch(err) {}
            });

            // Soumission de formulaires : activation de la barre + spinner sur le bouton
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.hasAttribute('data-no-loading')) return;

                this.start();

                const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (btn && !btn.classList.contains('btn-is-loading')) {
                    btn.classList.add('btn-is-loading');
                    if (btn.tagName.toLowerCase() === 'button') {
                        const sp = document.createElement('span');
                        sp.className = 'btn-spinner';
                        btn.prepend(sp);
                    }
                }
            });

            // Rechargement / départ de la page
            window.addEventListener('beforeunload', () => {
                this.start();
            });
        }
    }

    window.KamerStockLoading = new LoadingEngine();

    // =========================================================================
    // CONTRÔLEUR UNIVERSEL DU SKELETON (TOUTES LES PAGES)
    // =========================================================================
    window.initKamerStockSkeleton = function(durationMs) {
        durationMs = durationMs || 1000; // ~1 seconde visible et élégante
        
        if (window.KamerStockLoading) {
            window.KamerStockLoading.start();
        }

        setTimeout(function() {
            var skeleton = document.getElementById('kamerstock-page-skeleton');
            var content = document.getElementById('kamerstock-page-content');

            if (skeleton) {
                skeleton.style.transition = 'opacity 0.25s ease-out';
                skeleton.style.opacity = '0';

                setTimeout(function() {
                    skeleton.style.display = 'none';
                    if (content) {
                        content.style.display = 'block';
                        void content.offsetWidth; // Force le reflow du navigateur
                        content.style.opacity = '1';
                    }
                }, 250);
            } else if (content) {
                content.style.display = 'block';
                content.style.opacity = '1';
            }

            if (window.KamerStockLoading) {
                window.KamerStockLoading.done();
            }
        }, durationMs);

        // Sécurité absolue : affichage garanti quoi qu'il arrive après 2.5s
        setTimeout(function() {
            var skeleton = document.getElementById('kamerstock-page-skeleton');
            var content = document.getElementById('kamerstock-page-content');
            if (skeleton && skeleton.style.display !== 'none') {
                skeleton.style.display = 'none';
            }
            if (content && (content.style.display === 'none' || content.style.opacity === '0')) {
                content.style.display = 'block';
                content.style.opacity = '1';
            }
            if (window.KamerStockLoading) {
                window.KamerStockLoading.done();
            }
        }, 2500);
    };

    // Déclenchement automatique au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.initKamerStockSkeleton(1000);
        });
    } else {
        window.initKamerStockSkeleton(1000);
    }
})();
</script>

<noscript>
    <style>
        #kamerstock-page-skeleton { display: none !important; }
        #kamerstock-page-content { display: block !important; opacity: 1 !important; }
    </style>
</noscript>
