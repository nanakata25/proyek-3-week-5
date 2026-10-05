import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const source = await readFile(new URL('../public/js/cart-store.js', import.meta.url), 'utf8');
const { normalizeCart, cartDetails, rupiah } = await import(`data:text/javascript;base64,${Buffer.from(source).toString('base64')}`);
const products = [
    { id: 1, nama: 'Buku Tulis', harga: 5000, stok: 40 },
    { id: 2, nama: 'Pulpen', harga: 3000, stok: 60 },
    { id: 3, nama: 'Barang habis', harga: 100, stok: 0 },
];

test('cart stores only IDs and quantities; server catalog determines totals', () => {
    const { cart, warning } = normalizeCart('[{"id":1,"jumlah":2,"harga":1,"nama":"Palsu"},{"id":2,"jumlah":1}]', products);
    assert.deepEqual(cart, [{ id: 1, jumlah: 2 }, { id: 2, jumlah: 1 }]);
    assert.ok(warning);
    const result = cartDetails(cart, products);
    assert.equal(result.count, 3);
    assert.equal(result.total, 13000);
    assert.equal(result.items[0].nama, 'Buku Tulis');
    assert.equal(result.items[0].harga, 5000);
    assert.equal(rupiah(result.total), 'Rp 13.000');
});

test('manual 999 quantity is capped to stock with a visible warning', () => {
    const { cart, warning } = normalizeCart('[{"id":1,"jumlah":999}]', products);
    assert.deepEqual(cart, [{ id: 1, jumlah: 40 }]);
    assert.match(warning, /melebihi stok/);
    assert.equal(cartDetails(cart, products).total, 200000);
});

test('malformed JSON and wrong root structures reset safely', () => {
    for (const raw of ['{broken', '{"id":1,"jumlah":2}', '"unexpected"']) {
        const result = normalizeCart(raw, products);
        assert.deepEqual(result.cart, []);
        assert.ok(result.warning);
    }
    assert.deepEqual(normalizeCart(null, products), { cart: [], warning: '' });
});

test('unknown, sold-out, noninteger and nonpositive data are removed', () => {
    const { cart, warning } = normalizeCart([
        null, { id: 99, jumlah: 1 }, { id: 3, jumlah: 1 },
        { id: 1, jumlah: -1 }, { id: 1, jumlah: 0 }, { id: 1, jumlah: 1.5 },
        { id: '1', jumlah: 2 }, { id: 1, jumlah: '2' },
        { id: 2, jumlah: 2 },
    ], products);
    assert.deepEqual(cart, [{ id: 2, jumlah: 2 }]);
    assert.ok(warning);
});

test('duplicate IDs merge and cannot exceed available stock', () => {
    const { cart, warning } = normalizeCart([{ id: 1, jumlah: 30 }, { id: 1, jumlah: 30 }], products);
    assert.deepEqual(cart, [{ id: 1, jumlah: 40 }]);
    assert.match(warning, /melebihi stok/);
});

test('valid persistent data is stable after serializing and loading again', () => {
    const expected = [{ id: 1, jumlah: 2 }, { id: 2, jumlah: 1 }];
    const first = normalizeCart(JSON.stringify(expected), products);
    const second = normalizeCart(JSON.stringify(first.cart), products);
    assert.deepEqual(second, { cart: expected, warning: '' });
});
