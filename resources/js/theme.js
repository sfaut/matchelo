/**
 * Plugin Alpine du thème clair (1a) / sombre (1b), mémorisé dans localStorage('matchelo-theme').
 *
 * Le script en ligne du <head> du layout applique le thème avant le premier rendu ;
 * ce plugin expose $store.theme pour la bascule (<x-theme-toggle>).
 *
 * @param {object} Alpine instance Alpine fournie par Livewire
 */
export default function theme(Alpine) {
    const KEY = 'matchelo-theme';
    const root = document.documentElement;

    Alpine.store('theme', {
        current: root.dataset.theme === 'dark' ? 'dark' : 'light',

        get isDark() {
            return this.current === 'dark';
        },

        toggle() {
            this.current = this.isDark ? 'light' : 'dark';
            root.dataset.theme = this.current;

            try {
                localStorage.setItem(KEY, this.current);
            } catch {
                // Stockage indisponible (navigation privée…) : le thème vaut pour la page en cours
            }
        },
    });
}
