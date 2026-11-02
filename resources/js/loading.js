/**
 * KamerStock Global Loading Engine
 * Manages the top progress bar, button loading spinners, and network requests.
 */

class LoadingEngine {
    constructor() {
        this.progressBar = null;
        this.progress = 0;
        this.interval = null;
        this.activeRequests = 0;
        this.init();
    }

    init() {
        // Attendre que le DOM soit prêt pour créer la barre si nécessaire
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        // Créer l'élément de barre de progression s'il n'existe pas
        if (!document.getElementById('global-top-progress')) {
            this.progressBar = document.createElement('div');
            this.progressBar.id = 'global-top-progress';
            
            // Si on est dans l'espace landlord, appliquer le thème violet
            if (window.location.pathname.startsWith('/landlord')) {
                this.progressBar.classList.add('landlord');
            }
            
            document.body.prepend(this.progressBar);
        } else {
            this.progressBar = document.getElementById('global-top-progress');
        }

        this.bindEvents();
    }

    start() {
        if (!this.progressBar) return;

        clearInterval(this.interval);
        this.progress = 15;
        this.progressBar.style.opacity = '1';
        this.progressBar.style.width = this.progress + '%';

        // Progression progressive réaliste
        this.interval = setInterval(() => {
            if (this.progress < 70) {
                this.progress += Math.random() * 12;
            } else if (this.progress < 90) {
                this.progress += Math.random() * 4;
            }
            if (this.progressBar) {
                this.progressBar.style.width = this.progress + '%';
            }
        }, 200);
    }

    set(percent) {
        if (!this.progressBar) return;
        this.progress = Math.min(100, Math.max(0, percent));
        this.progressBar.style.opacity = '1';
        this.progressBar.style.width = this.progress + '%';
    }

    done() {
        if (!this.progressBar) return;

        clearInterval(this.interval);
        this.progress = 100;
        this.progressBar.style.width = '100%';

        setTimeout(() => {
            if (this.progressBar) {
                this.progressBar.style.opacity = '0';
                setTimeout(() => {
                    if (this.progressBar) {
                        this.progressBar.style.width = '0%';
                        this.progress = 0;
                    }
                }, 300);
            }
        }, 250);
    }

    bindEvents() {
        // 1. Navigation : Clics sur les liens internes
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href) return;

            // Ignorer les ancres, les liens externes, les onglets JavaScript, et les téléchargements
            if (
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                link.hasAttribute('download') ||
                link.getAttribute('target') === '_blank' ||
                e.ctrlKey || e.metaKey || e.shiftKey
            ) {
                return;
            }

            // Vérifier qu'il s'agit bien d'une URL de la même origine
            try {
                const targetUrl = new URL(link.href, window.location.origin);
                if (targetUrl.origin === window.location.origin && targetUrl.pathname !== window.location.pathname) {
                    this.start();
                }
            } catch (err) {
                // Ignore URL parsing errors
            }
        });

        // 2. Soumission de formulaires : Protection anti-double clic + spinner sur le bouton
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.hasAttribute('data-no-loading')) return;

            // Vérifier la validité HTML5
            if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                return;
            }

            this.start();

            // Trouver le bouton de soumission
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('btn-is-loading')) {
                // Marquer comme chargé
                submitBtn.classList.add('btn-is-loading');

                // Si c'est un bouton avec contenu
                if (submitBtn.tagName.toLowerCase() === 'button') {
                    // Créer le micro spinner
                    const spinner = document.createElement('span');
                    spinner.className = 'btn-spinner mr-1.5';
                    submitBtn.prepend(spinner);
                }
            }
        });

        // 3. Avant déchargement de la page
        window.addEventListener('beforeunload', () => {
            this.start();
        });

        // 4. Interception Axios pour les requêtes AJAX
        if (window.axios) {
            window.axios.interceptors.request.use((config) => {
                this.activeRequests++;
                if (this.activeRequests === 1) {
                    this.start();
                }
                return config;
            }, (error) => {
                this.activeRequests = Math.max(0, this.activeRequests - 1);
                if (this.activeRequests === 0) {
                    this.done();
                }
                return Promise.reject(error);
            });

            window.axios.interceptors.response.use((response) => {
                this.activeRequests = Math.max(0, this.activeRequests - 1);
                if (this.activeRequests === 0) {
                    this.done();
                }
                return response;
            }, (error) => {
                this.activeRequests = Math.max(0, this.activeRequests - 1);
                if (this.activeRequests === 0) {
                    this.done();
                }
                return Promise.reject(error);
            });
        }
    }
}

// Initialisation globale
window.KamerStockLoading = new LoadingEngine();
export default window.KamerStockLoading;
