// Thème clair (1a) / sombre (1b). Charger le plus tôt possible (inline dans <head>) pour éviter un flash.
const KEY = 'matchelo-theme';
const root = document.documentElement;
root.dataset.theme = localStorage.getItem(KEY) || 'light';

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-toggle]')) {
        return;
    }
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    localStorage.setItem(KEY, next);
});
