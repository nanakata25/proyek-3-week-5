# Modul 5 Role Management

Lanjutan toko online W05–W06 pada `tugas-toko-online`, Laravel 13. Nama: Dyas Nakata Hilwan; NIM: 251511009; kelas: D3 A. Baseline sebelum implementasi: `a0fd091`.

## Rencana dan prediksi sebelum pengujian

| Aksi | Guest | Customer | Staff | Admin |
|---|---|---|---|---|
| Katalog | Ya | Ya | Ya | Ya |
| Keranjang | Tidak | Ya | Tidak | Tidak |
| Angka stok | Tidak | Tidak | Ya | Ya |
| Kelola produk | Tidak | Tidak | Tidak | Ya |
| Ubah role | Tidak | Tidak | Tidak | Ya |

BR-01: role default customer. BR-02: endpoint perubahan role hanya admin. BR-03: perubahan hanya customer/staff. BR-04: akun sendiri dan target admin ditolak. BR-05: role sama tidak memanggil save. BR-06: Observer hanya log perubahan role dengan ID target, role lama/baru, dan ID aktor. BR-07: cart dari payload hanya untuk customer yang login.

Prediksi HTTP JSON: admin customer→staff 302 dengan satu perubahan DB dan satu log; customer PATCH 403 tanpa perubahan; self-edit 422; input admin 422; no-op 302 tanpa save/log; staff POST cart/add 403. Guest HTML ke route terlindungi 302 ke login, guest JSON 401. Validasi HTML diprediksi 422 sebagai hipotesis awal yang akan dibandingkan dengan perilaku redirect form Laravel.

## Pembagian tanggung jawab

Route dan Gate menjaga akses; ChangeRoleRequest memvalidasi role; controller menghubungkan request dengan service; RoleManagementService menjaga business rule sebelum save; UserObserver merespons perubahan sukses. Custom primary key pengguna adalah id_user sehingga log memakai getKey(). Role tidak boleh masuk fillable atau formulir publik.

Pengujian lokal memakai database dummy terpisah dari MySQL tugas sebelumnya. Jangan commit database, .env, cookie, CSRF token, password, atau hash. Screenshot dan ringkasan HTTP/DB/log yang sudah disaring disimpan dalam evidence/modul-5.
