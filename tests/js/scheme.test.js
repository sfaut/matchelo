import assert from 'node:assert/strict';
import { beforeEach, test } from 'node:test';
import scheme from '../../resources/js/scheme.js';

/**
 * Installe un document et un localStorage minimaux, puis enregistre le plugin
 * sur un faux Alpine qui ne fait que retenir les stores.
 *
 * @param {string} initialScheme thème posé par le script en ligne du <head>
 * @param {boolean} storageAvailable faux pour simuler un stockage qui lève une exception
 * @returns {{ store: object, root: object, storage: Map<string, string> }}
 */
function installScheme(initialScheme, storageAvailable = true) {
    const root = { dataset: { scheme: initialScheme } };
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
    scheme({ store: (name, value) => (stores[name] = value) });

    return { store: stores.scheme, root, storage };
}

beforeEach(() => {
    delete globalThis.document;
    delete globalThis.localStorage;
});

test('the store starts from the scheme applied in the head', () => {
    assert.equal(installScheme('dark').store.isDark, true);
    assert.equal(installScheme('light').store.isDark, false);
});

test('an unknown scheme falls back to light', () => {
    assert.equal(installScheme('sepia').store.current, 'light');
});

test('toggling switches the html scheme and remembers it', () => {
    const { store, root, storage } = installScheme('light');

    store.toggle();
    assert.equal(root.dataset.scheme, 'dark');
    assert.equal(storage.get('matchelo-scheme'), 'dark');
    assert.equal(store.isDark, true);

    store.toggle();
    assert.equal(root.dataset.scheme, 'light');
    assert.equal(storage.get('matchelo-scheme'), 'light');
});

test('toggling still works when storage is unavailable', () => {
    const { store, root } = installScheme('light', false);

    store.toggle();
    assert.equal(root.dataset.scheme, 'dark');
});
