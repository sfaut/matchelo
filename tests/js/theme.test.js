import assert from 'node:assert/strict';
import { beforeEach, test } from 'node:test';
import theme from '../../resources/js/theme.js';

/**
 * Installe un document et un localStorage minimaux, puis enregistre le plugin
 * sur un faux Alpine qui ne fait que retenir les stores.
 *
 * @param {string} initialTheme thème posé par le script en ligne du <head>
 * @param {boolean} storageAvailable faux pour simuler un stockage qui lève une exception
 * @returns {{ store: object, root: object, storage: Map<string, string> }}
 */
function installTheme(initialTheme, storageAvailable = true) {
    const root = { dataset: { theme: initialTheme } };
    const storage = new Map();

    globalThis.document = { documentElement: root };
    globalThis.localStorage = {
        setItem(key, value) {
            if (!storageAvailable) {
                throw new Error('SecurityError');
            }
            storage.set(key, value);
        },
    };

    const stores = {};
    theme({ store: (name, value) => (stores[name] = value) });

    return { store: stores.theme, root, storage };
}

beforeEach(() => {
    delete globalThis.document;
    delete globalThis.localStorage;
});

test('the store starts from the theme applied in the head', () => {
    assert.equal(installTheme('dark').store.isDark, true);
    assert.equal(installTheme('light').store.isDark, false);
});

test('an unknown theme falls back to light', () => {
    assert.equal(installTheme('sepia').store.current, 'light');
});

test('toggling switches the html theme and remembers it', () => {
    const { store, root, storage } = installTheme('light');

    store.toggle();
    assert.equal(root.dataset.theme, 'dark');
    assert.equal(storage.get('matchelo-theme'), 'dark');
    assert.equal(store.isDark, true);

    store.toggle();
    assert.equal(root.dataset.theme, 'light');
    assert.equal(storage.get('matchelo-theme'), 'light');
});

test('toggling still works when storage is unavailable', () => {
    const { store, root } = installTheme('light', false);

    store.toggle();
    assert.equal(root.dataset.theme, 'dark');
});
