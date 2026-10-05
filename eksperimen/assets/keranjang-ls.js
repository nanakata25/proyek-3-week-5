'use strict';
const produk = [{id: 1, nama: 'Buku Tulis', harga: 5000}, {id: 2, nama: 'Pulpen', harga: 3000}, {id: 3, nama: 'Penggaris', harga: 4000}];
const KUNCI = 'keranjang';
const uang = angka => 'Rp ' + angka.toLocaleString('id-ID');
function bacaKeranjang() {
    try {
        const data = JSON.parse(localStorage.getItem(KUNCI) || '[]');
        if (!Array.isArray(data)) throw new TypeError('Keranjang harus berupa array.');
        return data.filter(item => item && Number.isInteger(item.id) && Number.isInteger(item.jumlah) && item.jumlah > 0 && item.jumlah <= 9999 && produk.some(p => p.id === item.id));
    } catch (error) { document.getElementById('error').textContent = 'Data keranjang tidak dapat dibaca (' + error.name + '). Kosongkan keranjang untuk memulai ulang.'; return []; }
}
function tampilkanKeranjang() {
    const ul = document.getElementById('isiKeranjang');
    ul.replaceChildren();
    let total = 0;
    const keranjang = bacaKeranjang();
    if (!keranjang.length) { const li = document.createElement('li'); li.textContent = 'Keranjang kosong.'; ul.appendChild(li); }
    for (const item of keranjang) {
        const p = produk.find(p => p.id === item.id);
        const subtotal = p.harga * item.jumlah;
        total += subtotal;
        const li = document.createElement('li'); li.textContent = p.nama + ' × ' + item.jumlah + ' = ' + uang(subtotal); ul.appendChild(li);
    }
    document.getElementById('total').textContent = 'Total: ' + uang(total);
    try { document.getElementById('dataKeranjang').textContent = localStorage.getItem(KUNCI) || '(kunci belum ada)'; }
    catch { document.getElementById('dataKeranjang').textContent = '(storage tidak tersedia)'; }
}
function tambah(id) {
    const keranjang = bacaKeranjang();
    const item = keranjang.find(item => item.id === id);
    if (item) item.jumlah = Math.min(item.jumlah + 1, 9999); else keranjang.push({id, jumlah: 1});
    try { localStorage.setItem(KUNCI, JSON.stringify(keranjang)); document.getElementById('error').textContent = ''; tampilkanKeranjang(); }
    catch (error) { document.getElementById('error').textContent = 'Keranjang gagal disimpan: ' + error.name; }
}
for (const p of produk) {
    const li = document.createElement('li'); const span = document.createElement('span'); const strong = document.createElement('strong');
    strong.textContent = p.nama; span.appendChild(strong); span.appendChild(document.createTextNode(uang(p.harga)));
    const button = document.createElement('button'); button.textContent = 'Tambah'; button.setAttribute('aria-label', 'Tambah ' + p.nama); button.addEventListener('click', () => tambah(p.id));
    li.append(span, button); document.getElementById('daftarProduk').appendChild(li);
}
document.getElementById('btnKosongkan').addEventListener('click', () => {
    try { localStorage.removeItem(KUNCI); document.getElementById('error').textContent = ''; tampilkanKeranjang(); }
    catch (error) { document.getElementById('error').textContent = 'Penghapusan gagal: ' + error.name; }
});
window.addEventListener('storage', event => { if (event.key === KUNCI || event.key === null) tampilkanKeranjang(); });
tampilkanKeranjang();
