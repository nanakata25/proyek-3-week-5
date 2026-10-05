'use strict';
function bacaStorage() {
    try { document.getElementById('nilaiLocal').textContent = JSON.stringify(localStorage.getItem('dari_local')); document.getElementById('nilaiSession').textContent = JSON.stringify(sessionStorage.getItem('dari_session')); }
    catch (error) { document.getElementById('error').textContent = 'Storage tidak tersedia: ' + error.name; }
}
document.getElementById('inisialisasi').addEventListener('click', () => {
    try { localStorage.setItem('dari_local', 'Saya bertahan setelah browser ditutup'); sessionStorage.setItem('dari_session', 'Saya hilang saat sesi tab berakhir'); bacaStorage(); }
    catch (error) { document.getElementById('error').textContent = 'Penulisan gagal: ' + error.name; }
});
document.getElementById('hapus').addEventListener('click', () => {
    try { localStorage.removeItem('dari_local'); sessionStorage.removeItem('dari_session'); bacaStorage(); }
    catch (error) { document.getElementById('error').textContent = 'Penghapusan gagal: ' + error.name; }
});
document.getElementById('baca').addEventListener('click', bacaStorage);
window.addEventListener('storage', bacaStorage);
bacaStorage();
