import { STORAGE_KEY, normalizeCart, cartDetails, rupiah } from './cart-store.js';

const products = JSON.parse(document.querySelector('#catalog-data').textContent);
let cart = [];
let storageAvailable = true;
let toastTimer;

function showNotice(message) {
    const notice = document.querySelector('#storage-notice');
    if (message) {
        notice.textContent = message;
        notice.hidden = false;
    }
}

function toast(message) {
    const box = document.querySelector('#toast');
    clearTimeout(toastTimer);
    box.textContent = message;
    box.hidden = false;
    toastTimer = setTimeout(() => { box.hidden = true; }, 3500);
}

function persist() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
        storageAvailable = true;
    } catch {
        storageAvailable = false;
        showNotice('Penyimpanan browser tidak tersedia. Keranjang sementara ini akan hilang ketika halaman ditutup atau dimuat ulang.');
    }
}

function readCart() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const normalized = normalizeCart(raw, products);
        cart = normalized.cart;
        showNotice(normalized.warning);
        // Repair changed data without broadcasting redundant writes across tabs.
        if (raw !== null && raw !== JSON.stringify(cart)) persist();
    } catch {
        storageAvailable = false;
        showNotice('Penyimpanan browser tidak tersedia. Keranjang sementara ini akan hilang ketika halaman ditutup atau dimuat ulang.');
    }
}

function render() {
    const details = cartDetails(cart, products);
    document.querySelectorAll('[data-cart-count]').forEach(element => {
        element.textContent = details.count;
        element.setAttribute('aria-label', `${details.count} barang di keranjang`);
    });
    if (document.body.dataset.page !== 'keranjang') return;
    document.querySelector('#empty-cart').hidden = details.items.length > 0;
    document.querySelector('#filled-cart').hidden = details.items.length === 0;
    document.querySelector('#item-summary').textContent = `${details.items.length} jenis · ${details.count} item`;
    document.querySelector('#summary-count').textContent = `${details.count} item`;
    document.querySelector('#summary-subtotal').textContent = rupiah(details.total);
    document.querySelector('#summary-total').textContent = rupiah(details.total);
    const list = document.querySelector('#cart-items');
    const template = document.querySelector('#cart-item-template');
    list.replaceChildren();
    for (const item of details.items) {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.cart-item');
        row.dataset.id = item.id;
        row.querySelector('h3').textContent = item.nama;
        row.querySelector('.item-price').textContent = `${rupiah(item.harga)} × ${item.jumlah}`;
        row.querySelector('.item-stock').textContent = `Stok tersedia: ${item.stok}`;
        row.querySelector('.quantity').textContent = item.jumlah;
        row.querySelector('.item-subtotal').textContent = rupiah(item.subtotal);
        row.querySelector('[data-action="decrement"]').setAttribute('aria-label', `Kurangi ${item.nama}`);
        const increase = row.querySelector('[data-action="increment"]');
        increase.setAttribute('aria-label', `Tambah ${item.nama}`);
        increase.disabled = item.jumlah >= item.stok;
        const remove = row.querySelector('.remove-button');
        remove.dataset.action = 'remove';
        remove.setAttribute('aria-label', `Hapus ${item.nama}`);
        const art = document.querySelector(`[data-art="${item.id}"] svg`);
        if (art) row.querySelector('.cart-item-art').append(art.cloneNode(true));
        list.append(fragment);
    }
}

function change(id, action) {
    if (storageAvailable) readCart();
    const product = products.find(item => item.id === id);
    if (!product) return;
    const existing = cart.find(item => item.id === id);
    const quantity = existing?.jumlah || 0;
    if (action === 'remove') {
        cart = cart.filter(item => item.id !== id);
        toast(`${product.nama} dihapus dari keranjang.`);
    } else {
        const next = quantity + (action === 'decrement' ? -1 : 1);
        if (next > product.stok) {
            toast(`Stok ${product.nama} hanya ${product.stok}.`);
            render();
            return;
        }
        if (next <= 0) cart = cart.filter(item => item.id !== id);
        else if (existing) existing.jumlah = next;
        else cart.push({ id, jumlah: next });
        if (action === 'add') toast(`${product.nama} ditambahkan ke keranjang.`);
    }
    persist();
    render();
}

document.addEventListener('click', event => {
    const add = event.target.closest('[data-add]');
    if (add) change(Number(add.dataset.add), 'add');
    const action = event.target.closest('[data-action]');
    if (action) change(Number(action.closest('[data-id]').dataset.id), action.dataset.action);
});

document.querySelector('#clear-cart')?.addEventListener('click', () => {
    cart = [];
    persist();
    render();
    toast('Keranjang berhasil dikosongkan.');
});

window.addEventListener('storage', event => {
    if (event.key === STORAGE_KEY || event.key === null) {
        readCart();
        render();
    }
});

window.addEventListener('focus', () => {
    if (storageAvailable) {
        readCart();
        render();
    }
});

readCart();
render();
