export const STORAGE_KEY = 'modul4.tugas2.keranjang';

/** Only product IDs and positive integer quantities may survive normalization. */
export function normalizeCart(raw, products) {
    const catalog = new Map(products.map(product => [product.id, product]));
    let input;
    try {
        input = typeof raw === 'string' ? JSON.parse(raw) : raw;
    } catch {
        return { cart: [], warning: 'Data keranjang tidak valid. Keranjang telah diatur ulang.' };
    }
    if (input === null || input === undefined) return { cart: [], warning: '' };
    if (!Array.isArray(input)) {
        return { cart: [], warning: 'Format keranjang tidak valid. Keranjang telah diatur ulang.' };
    }
    const quantities = new Map();
    let changed = false;
    let capped = false;
    for (const item of input) {
        if (!item || !Number.isSafeInteger(item.id) || !Number.isSafeInteger(item.jumlah) || item.jumlah <= 0) {
            changed = true;
            continue;
        }
        const product = catalog.get(item.id);
        if (!product || product.stok <= 0) {
            changed = true;
            continue;
        }
        const existing = quantities.get(item.id) || 0;
        if (item.jumlah > product.stok - existing) capped = true;
        if (existing > 0 || Object.keys(item).some(key => !['id', 'jumlah'].includes(key))) changed = true;
        quantities.set(item.id, Math.min(product.stok, existing + item.jumlah));
    }
    return {
        cart: Array.from(quantities, ([id, jumlah]) => ({ id, jumlah })),
        warning: capped
            ? 'Jumlah keranjang melebihi stok. Jumlah telah disesuaikan dengan stok produk terbaru.'
            : changed ? 'Data keranjang telah diperbaiki menggunakan daftar produk terbaru.' : '',
    };
}

export function cartDetails(cart, products) {
    const catalog = new Map(products.map(product => [product.id, product]));
    const items = cart.map(item => {
        const product = catalog.get(item.id);
        return { ...product, jumlah: item.jumlah, subtotal: product.harga * item.jumlah };
    });
    return {
        items,
        count: items.reduce((sum, item) => sum + item.jumlah, 0),
        total: items.reduce((sum, item) => sum + item.subtotal, 0),
    };
}

export function rupiah(value) {
    return `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value)}`;
}
