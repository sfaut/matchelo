/**
 * Plugin Alpine du mode clair (1a) / sombre (1b), mémorisé dans localStorage('matchelo-scheme').
 *
 * Le script en ligne du <head> du layout applique le mode avant le premier rendu ;
 * ce plugin expose $store.scheme pour la bascule (<x-scheme-toggle>).
 *
 * @param {object} Alpine instance Alpine fournie par Livewire
 */
export default function scheme(Alpine) {
    const KEY = 'matchelo-scheme';
    const root = document.documentElement;

    Alpine.store('scheme', {
        current: root.dataset.scheme === 'dark' ? 'dark' : 'light',

        get isDark() {
            return this.current === 'dark';
        },

        toggle() {
            this.current = this.isDark ? 'light' : 'dark';
            root.dataset.scheme = this.current;

            try {
                localStorage.setItem(KEY, this.current);
            } catch {
                // Stockage indisponible (navigation privée…) : le thème vaut pour la page en cours
            }
        },
    });
}
