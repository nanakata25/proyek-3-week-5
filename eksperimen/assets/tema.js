'use strict';
function tampilTema() {
    try {
        const nilai = localStorage.getItem('tema');
        const gelap = nilai === 'gelap';
        document.body.classList.toggle('gelap', gelap);
        document.getElementById('statusTema').textContent = gelap ? 'Tema gelap' : 'Tema terang';
        document.getElementById('dataTema').textContent = 'localStorage.getItem("tema") = ' + JSON.stringify(nilai);
    } catch (error) { document.getElementById('error').textContent = 'Penyimpanan browser tidak tersedia: ' + error.name; }
}
document.getElementById('gantiTema').addEventListener('click', () => {
    try { localStorage.setItem('tema', document.body.classList.contains('gelap') ? 'terang' : 'gelap'); tampilTema(); }
    catch (error) { document.getElementById('error').textContent = 'Tema gagal disimpan: ' + error.name; }
});
document.getElementById('hapusTema').addEventListener('click', () => {
    try { localStorage.removeItem('tema'); tampilTema(); }
    catch (error) { document.getElementById('error').textContent = 'Preferensi gagal dihapus: ' + error.name; }
});
window.addEventListener('storage', tampilTema);
tampilTema();
