import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { runInNewContext } from 'node:vm';

const storeSource = await readFile(new URL('../public/js/cart-store.js', import.meta.url), 'utf8');
const store = await import(`data:text/javascript;base64,${Buffer.from(storeSource).toString('base64')}`);
const browserSource = await readFile(new URL('../public/js/keranjang.js', import.meta.url), 'utf8');
// Run the real page controller with its imports supplied and only browser APIs stubbed.
const controllerSource = browserSource.replace(/^import[^\n]*\n/, '');

function createPage() {
    let stored = JSON.stringify([{ id: 1, jumlah: 1 }]);
    let failWrites = false;
    const documentListeners = new Map();
    const windowListeners = new Map();
    const badge = { textContent: '', setAttribute() {} };
    const elements = {
        '#catalog-data': { textContent: JSON.stringify([{ id: 1, nama: 'Buku Tulis', harga: 5000, stok: 40 }]) },
        '#storage-notice': { hidden: true, textContent: '' },
        '#toast': { hidden: true, textContent: '' },
    };
    runInNewContext(controllerSource, {
        ...store,
        document: {
            body: { dataset: { page: 'katalog' } },
            querySelector: selector => elements[selector],
            querySelectorAll: selector => selector === '[data-cart-count]' ? [badge] : [],
            addEventListener: (name, listener) => documentListeners.set(name, listener),
        },
        window: { addEventListener: (name, listener) => windowListeners.set(name, listener) },
        localStorage: {
            getItem: () => stored,
            setItem: (key, value) => {
                if (failWrites) throw new Error('QuotaExceededError');
                stored = value;
            },
        },
        setTimeout: () => 1,
        clearTimeout() {},
    });
    return {
        count: () => Number(badge.textContent),
        storedCart: () => JSON.parse(stored),
        failWrites: value => { failWrites = value; },
        add() {
            documentListeners.get('click')({
                target: { closest: selector => selector === '[data-add]' ? { dataset: { add: '1' } } : null },
            });
        },
        storageEvent: key => windowListeners.get('storage')({ key }),
        externalCart: quantity => { stored = JSON.stringify([{ id: 1, jumlah: quantity }]); },
    };
}

test('storage events preserve the temporary cart after a failed write', () => {
    const page = createPage();
    page.failWrites(true);
    page.add();
    assert.equal(page.count(), 2);
    assert.deepEqual(page.storedCart(), [{ id: 1, jumlah: 1 }]);

    // A queued cart event must not replace unsaved local changes with stale storage.
    page.storageEvent(store.STORAGE_KEY);
    assert.equal(page.count(), 2);
    page.storageEvent(null);
    assert.equal(page.count(), 2);
});

test('successful writes restore synchronization with changes from other tabs', () => {
    const page = createPage();
    page.failWrites(true);
    page.add();
    page.failWrites(false);
    page.add();
    assert.equal(page.count(), 3);
    assert.deepEqual(page.storedCart(), [{ id: 1, jumlah: 3 }]);

    page.externalCart(4);
    page.storageEvent(store.STORAGE_KEY);
    assert.equal(page.count(), 4);
});
