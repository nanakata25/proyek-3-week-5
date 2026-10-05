"""Build the one-file Moodle report from source, actual test logs and screenshots.
Usage: python scripts/build_report.py --nim STUDENT_ID --name "Full Name"
Dependencies: reportlab, pillow, pypdf. Identity is supplied only at build time.
"""
from pathlib import Path
from xml.sax.saxutils import escape
import argparse,json,re
from reportlab.pdfgen import canvas
from reportlab.platypus import SimpleDocTemplate,Paragraph,Spacer,Table,TableStyle,Image,PageBreak,KeepTogether
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet,ParagraphStyle
from reportlab.lib.enums import TA_CENTER
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.pagesizes import A4
from PIL import Image as PILImage

root=Path(__file__).resolve().parents[1]
ap=argparse.ArgumentParser();ap.add_argument('--nim',required=True);ap.add_argument('--name',required=True);args=ap.parse_args()
out=root/'output/pdf';out.mkdir(parents=True,exist_ok=True)
filename=out/(args.nim+'_'+re.sub(r'[^\w-]+','_',args.name)+'_Laporan_Modul4.pdf')
shots=root/'evidence/screenshots'
db=json.loads((root/'evidence/database-snapshot.json').read_text())
shop=json.loads((root/'evidence/shop-browser.json').read_text())
order=shop['after']['queries']['orders']['rows'][-1]
repo='https://github.com/nanakata25/proyek-3-week-5'
for name,file in [('Body','arial.ttf'),('BodyBold','arialbd.ttf'),('BodyItalic','ariali.ttf')]:
    pdfmetrics.registerFont(TTFont(name,'C:/Windows/Fonts/'+file))
pdfmetrics.registerFontFamily('Body',normal='Body',bold='BodyBold',italic='BodyItalic',boldItalic='BodyBold')
ink=colors.HexColor('#163846');teal=colors.HexColor('#087e80');muted=colors.HexColor('#526974');pale=colors.HexColor('#edf4f0')
styles=getSampleStyleSheet()
styles.add(ParagraphStyle(name='BodyCustom',fontName='Body',fontSize=9.5,leading=14,textColor=ink,spaceAfter=8))
styles.add(ParagraphStyle(name='SmallCustom',fontName='Body',fontSize=8,leading=11,textColor=muted,spaceAfter=6))
styles.add(ParagraphStyle(name='TitleCustom',fontName='BodyBold',fontSize=25,leading=31,textColor=ink,spaceAfter=15))
styles.add(ParagraphStyle(name='HeadingCustom',fontName='BodyBold',fontSize=18,leading=23,textColor=ink,spaceAfter=13))
styles.add(ParagraphStyle(name='SubCustom',fontName='BodyBold',fontSize=11.5,leading=16,textColor=teal,spaceBefore=9,spaceAfter=7))
styles.add(ParagraphStyle(name='CaptionCustom',fontName='Body',fontSize=8,leading=11,textColor=muted,spaceAfter=10))
styles.add(ParagraphStyle(name='CodeCustom',fontName='Courier',fontSize=8,leading=11,textColor=ink,backColor=pale,borderPadding=8,spaceAfter=12))
story=[];fig=0
def p(s,style='BodyCustom'):story.append(Paragraph(s,styles[style]))
def h(s):p(s,'SubCustom')
def page(title,kicker='MODUL 04 / LAPORAN PRAKTIKUM'):
    if story:story.append(PageBreak())
    p(kicker,'SmallCustom');p(title,'HeadingCustom')
def table(headers,rows,widths=None,size=8.2):
    st=ParagraphStyle(name='cell',parent=styles['BodyCustom'],fontSize=size,leading=size+3,spaceAfter=0)
    data=[[Paragraph('<b>'+escape(str(c))+'</b>',st)for c in headers]]+[[Paragraph(escape(str(c)).replace('\n','<br/>'),st)for c in row]for row in rows]
    t=Table(data,colWidths=widths,repeatRows=1,hAlign='LEFT')
    t.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),pale),('TEXTCOLOR',(0,0),(-1,-1),ink),('VALIGN',(0,0),(-1,-1),'TOP'),('TOPPADDING',(0,0),(-1,-1),7),('BOTTOMPADDING',(0,0),(-1,-1),7),('LEFTPADDING',(0,0),(-1,-1),8),('RIGHTPADDING',(0,0),(-1,-1),8),('LINEBELOW',(0,0),(-1,0),1,teal),('LINEBELOW',(0,1),(-1,-1),.3,colors.HexColor('#dce5e6'))]))
    story.append(t);story.append(Spacer(1,10))
def pic(file,caption,maxh=235,width=505):
    global fig
    path=shots/file
    if not path.exists():raise FileNotFoundError(path)
    with PILImage.open(path)as im:w,h=im.size
    scale=min(width/w,maxh/h);fig+=1
    story.append(KeepTogether([Image(str(path),width=w*scale,height=h*scale,hAlign='CENTER'),Spacer(1,5),Paragraph(f'<b>Gambar {fig}.</b> '+caption,styles['CaptionCustom'])]))
def code(s):p(escape(s).replace('\n','<br/>').replace(' ','&nbsp;'),'CodeCustom')

p('PRAKTIKUM PEMROGRAMAN WEB / MODUL 04','SmallCustom');story.append(Spacer(1,36))
p('Penyimpanan Data<br/>pada Web','TitleCustom');p('Cookies, Session, dan Local Storage','HeadingCustom')
p('Eksperimen, login dengan password ter-hash, keranjang tanpa login, dan integrasi toko online.');story.append(Spacer(1,28))
table(['IDENTITAS','KETERANGAN'],[['Nama',args.name],['NIM',args.nim],['Tanggal pengerjaan / pengujian','5 Oktober 2026 (Asia/Jakarta)'],['Repository GitHub',repo],['Bentuk pengumpulan','Satu file laporan PDF melalui Moodle']],[110,395])
p('<b>URL source code:</b><br/><link href="'+repo+'" color="#087e80">'+repo+'</link>')
p('Repository berisi tiga proyek Laravel, eksperimen, skrip pengujian, dan bukti aplikasi. PDF ini menyatukan hasil pengerjaan agar source code tidak perlu diunggah terpisah ke Moodle.')
story.append(Spacer(1,20));p('Ikhtisar hasil','SubCustom')
p('<b>30 tes Laravel</b> (285 assertions), <b>6 tes unit JavaScript</b>, <b>20 langkah browser Tugas 1/2</b>, <b>9 kelompok eksperimen E01-E17</b>, dan <b>6 kelompok browser toko</b> lulus. Checkout contoh: <b>Rp19.000</b>, dua detail barang, stok berkurang, keranjang kosong.')
p('Bukti tangkapan layar berasal dari aplikasi yang benar-benar dijalankan. Keterangan membedakan screenshot halaman, ekspor query database, pemeriksaan otomatis browser, dan panel DevTools.','SmallCustom')

page('01. Ruang lingkup dan lingkungan')
table(['Komponen','Implementasi / hasil aktual'],[['Framework','Laravel 13.34.0; skeleton laravel/laravel 13.10.1; tanpa starter kit'],['Runtime','PHP 8.4.26; Composer 2; Blade dan aset CSS/SVG lokal'],['Database','MariaDB 10.4.32 bawaan XAMPP melalui driver Laravel mysql; localhost:3307. MariaDB disebut secara eksplisit, bukan diklaim Oracle MySQL.'],['Database aplikasi','db_tugas, db_keranjang, db_toko_online; eksperimen db_belajar'],['Database tes','db_tugas_test, db_keranjang_test, db_toko_online_test (terpisah dari data demo)'],['Browser pengujian','Google Chrome melalui Playwright 1.62.1; profil khusus, bukan profil pribadi'],['Alamat lokal','Tugas 1 :8001; Tugas 2 :8002; toko :8003; eksperimen :8004/eksperimen/']],[110,395])
h('Struktur satu repository')
code('tugas-1-login/       # autentikasi manual Laravel\ntugas-2-keranjang/   # katalog MySQL + Local Storage\ntugas-toko-online/  # toko, checkout, riwayat\neksperimen/         # PHP dan JavaScript materi\nevidence/           # screenshot dan log aktual\ndocs/               # prosedur dan jawaban materi\nscripts/            # otomasi tes dan pembuat laporan')
p('Setiap aplikasi memiliki composer.json, composer.lock, migration, seeder, route, controller, view, dan README. .env, APP_KEY, vendor, database runtime, profil browser, materi dosen, serta dokumen laporan tidak dimasukkan ke GitHub. Workspace Modul-4.code-workspace dibuka di VS Code.')
p('Laravel 13 memerlukan minimal PHP 8.3; lingkungan dan dependensi terkunci pada pengerjaan ini menggunakan PHP 8.4. Referensi: <link href="https://laravel.com/docs/13.x/releases" color="#087e80">Laravel 13 Release Notes</link>.','SmallCustom')

page('02. Dasar penyimpanan dan HTTP stateless')
table(['Aspek','Cookie','Session server','Local Storage'],[['Lokasi','Browser','Server; ID di browser','Browser per origin'],['Transport','Otomatis jika domain/path/flags cocok','Cookie ID ikut request','Tidak otomatis dikirim'],['Umur','Expires/Max-Age atau sesi','Timeout dan kebijakan server','Sampai dihapus/tereviksi'],['Batas umum','Sekitar 4 KiB/cookie','Bergantung backend','Sekitar 5 MiB; kebijakan browser'],['Kegunaan','Preferensi kecil untuk server','Status autentikasi','Preferensi atau cart tamu']],[71,144,145,145])
p('HTTP tidak otomatis membawa variabel dari request sebelumnya. E01-E02 mengirim nama Budi melalui POST ke Langkah 2, lalu membuka Langkah 3 lewat GET tanpa nama. Hasilnya berubah menjadi “tidak diketahui”. Agar nama tetap tersedia, simpan pada session dan baca kembali pada request berikutnya.')
pic('e01-nama-diterima.png','E01: server menerima nama Budi pada request POST.',190)
pic('e02-stateless.png','E02: request GET berikutnya tidak membawa nama; server menampilkan “tidak diketahui”.',190)

page('03. Eksperimen cookie dan urutan request')
table(['Aksi','Counter setelah exit','Counter sebelum exit'],[['Buka pertama kali','1','1'],['Simpan Budi (POST → 302 → GET 200)','2 (+1)','3 (+2)'],['Refresh','3 (+1)','4 (+1)'],['Lupakan saya','4 (+1), nama dihapus','6 (+2), nama dihapus']],[285,110,110])
p('E03-E06: letak penghitung menentukan apakah request redirect ikut dihitung. Pada versi atas, POST Simpan dan GET hasil redirect sama-sama menjalankan penghitung. Versi normal baru menghitung ketika HTML dirender. Respons jaringan yang direkam benar-benar menunjukkan POST <b>302</b> diikuti GET <b>200</b>.')
pic('e03-cookie-normal.png','Versi normal sesudah Simpan: kunjungan 2; cookie baru kembali pada request berikutnya.',205)
pic('e06-cookie-atas.png','Versi penghitung di atas: aksi Simpan yang sama menghasilkan kunjungan 3.',205)
p('Cookie nama dan kunjungan memiliki masa berlaku 7 hari dan path /eksperimen/. Penghapusan mengirim masa berlaku lampau dengan cakupan path yang sama. Data pada $_COOKIE adalah data request masuk; setcookie() menulis header respons.','SmallCustom')

page('04. Eksperimen manipulasi cookie')
p('E07: Buku Tulis ×2 dan Pulpen ×1 menghasilkan <b>Rp13.000</b>. Data cookie hanya pasangan ID dan jumlah dalam JSON. E08: cookie keranjang diubah menjadi {"1":999,"2":1}, kemudian halaman di-refresh. Total berubah menjadi <b>Rp4.998.000</b> (999 × 5.000 + 3.000).')
pic('e07-cookie-cart.png','Keranjang cookie sebelum manipulasi: total Rp13.000.',220)
pic('e08-cookie-manipulasi.png','Sesudah perubahan cookie melalui API browser pengujian dan refresh: jumlah 999 dipercaya oleh demo.',220)
p('Perubahan tersebut dilakukan pada aplikasi lokal melalui API cookie browser; screenshot menunjukkan hasil aplikasi, bukan diklaim sebagai screenshot panel DevTools. Demo ini sengaja tidak memvalidasi stok untuk memperlihatkan masalah kepercayaan. Tidak ada checkout atau pembayaran pada demo.','SmallCustom')
h('Jawaban diskusi')
p('Session lebih sesuai untuk keranjang satu kunjungan jika server harus memegang data: pengguna tidak bisa mengedit isi session langsung dari DevTools dan payload keranjang tidak ikut setiap request. Namun endpoint perubahan jumlah tetap wajib memvalidasi input dan checkout wajib memeriksa harga serta stok.')

page('05. Eksperimen session PHP')
p('E09-E12 memakai PDO MySQL, password_hash()/password_verify(), prepared statement, session_regenerate_id(true), dan POST + CSRF saat logout. Password salah ditolak; password benar menampilkan Budi Santoso. Cookie PHPSESSID berubah setelah login dan hanya memuat ID, sedangkan user_id/nama berada di server.')
pic('e09-session-gagal.png','Login eksperimen gagal dengan pesan umum, tanpa menunjukkan mana kredensial yang salah.',220)
pic('e10-session-dashboard.png','Dashboard eksperimen sesudah login; tabel menjelaskan lokasi penyimpanan identitas.',250)
p('Konteks browser baru tanpa cookie dialihkan dari dashboard.php ke login.php. Setelah logout, PHPSESSID hilang dari path eksperimen; mengakses dashboard kembali ditolak. Hasil perbandingan ID dicatat sebagai boolean agar nilai sesi tidak disebarkan dalam log.','SmallCustom')

page('06. Logout dan preferensi tema')
pic('e12-session-logout.png','E12: logout menghapus data server dan cookie, lalu membuka halaman selesai yang tidak memulai session baru.',230)
p('Halaman login dapat membuat session anonim baru untuk CSRF. Karena itu adanya cookie baru setelah kembali ke login bukan berarti pengguna masih terautentikasi. Halaman logout_selesai.php memisahkan pengamatan penghapusan dari inisialisasi session berikutnya.')
pic('e13-tema-gelap.png','E13: tema gelap tetap dipakai setelah refresh karena localStorage["tema"] bernilai "gelap".',255)
p('Tema awal terang telah diuji sebelum perubahan. Local Storage dipilih untuk preferensi yang hanya dibutuhkan JavaScript. Tidak ada kedaluwarsa otomatis, tetapi data bisa dihapus pengguna/browser dan tidak berpindah ke profil lain.')

page('07. Local Storage dan umur sesi tab')
pic('e16-storage-sebelum.png','E16: setelah inisialisasi dan refresh, kedua storage masih berisi data.',235)
pic('e16-storage-sesudah.png','Tab ditutup lalu dibuat tab baru: dari_local tetap ada, dari_session menjadi null.',235)
h('Perbaikan rancangan eksperimen')
p('Contoh modul menulis kedua nilai setiap halaman dibuka. Jika langsung disalin, sessionStorage terisi kembali dan percobaan penutupan tab menjadi menyesatkan. Implementasi memisahkan tombol Inisialisasi dari pembacaan. Pengujian menggunakan tab baru biasa, bukan Duplicate Tab atau pemulihan sesi yang dapat menyalin/memulihkan sessionStorage.')
p('Local Storage memiliki cakupan origin (skema, host, port). Penggantian localhost menjadi 127.0.0.1 atau port lain membentuk origin berbeda; cookie tidak menggunakan port sebagai pembatas. Referensi: <link href="https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage" color="#087e80">MDN localStorage</link>.','SmallCustom')

page('08. Keranjang Local Storage dan keamanan')
pic('e14-ls-cart.png','E14: data [{id:1,jumlah:2},{id:2,jumlah:1}] memberikan total Rp13.000 tanpa request tambahan untuk tombol tambah.',235)
pic('e15-ls-manipulasi.png','E15: jumlah Local Storage diubah menjadi 999; setelah refresh demo menghasilkan Rp4.998.000.',235)
p('Local Storage tidak lebih tepercaya daripada cookie: keduanya dikendalikan pengguna. E17 juga membuktikan skrip dalam origin yang sama dapat membaca tema “gelap” dan isi storage. Ini demonstrasi pembacaan lokal, bukan klaim eksploitasi XSS atau pengiriman data ke pihak luar.')
p('Gunakan textContent/escaping untuk data pengguna, jangan simpan password/token akses di storage biasa, gunakan HttpOnly untuk cookie sesi, serta hitung harga final dan stok pada server. HttpOnly tidak melarang pemilik browser mengedit cookie lewat DevTools dan tidak meniadakan seluruh risiko XSS.')

page('09. Jawaban kuis materi (1-18)')
rows=[('1','B','HTTP memperlakukan request secara independen.'),('2','C','setcookie() menulis cookie melalui header respons.'),('3','B','Masa berlaku lampau menghapus cookie pada path/domain yang cocok.'),('4','B','session_start() memulai atau memulihkan sesi.'),('5','C','Browser menyimpan ID sesi; isi session berada di server.'),('6','B','Sekitar 5 MB sebagai pedoman; kuota bergantung browser.'),('7','C','getItem mengembalikan string (atau null jika tidak ada).'),('8','B','JSON.stringify(array) untuk menyimpan; JSON.parse untuk membaca.'),('9','C','sessionStorage terkait sesi tab dan berakhir saat tab ditutup normal.'),('10','B','HttpOnly mencegah pembacaan nilai cookie oleh JavaScript.'),('11','C','Regenerasi ID sesi membantu mencegah session fixation.'),('12','C','Password/token akses tidak boleh disimpan pada Local Storage biasa.'),('13','Benar*','Cookie otomatis terkirim jika cocok domain/path, umur, flags, kebijakan browser.'),('14','Salah','Local Storage tidak dikirim otomatis bersama HTTP.'),('15','Salah','Set cookie sebelum output dikirim; jangan bergantung output buffering.'),('16','Salah','Cookie baru tersedia di $_COOKIE pada request berikutnya.'),('17','Benar','Data session PHP tersimpan pada backend server.'),('18','Salah','Cookie dapat diubah pemilik browser melalui DevTools.')]
table(['No.','Jawaban','Alasan'],rows,[35,58,412],8.6)
p('*Pernyataan “setiap request” bukan berarti semua cookie dikirim ke semua URL. Cakupan dan kebijakan cookie tetap berlaku.','SmallCustom')

page('10. Jawaban kuis (19-26) dan diskusi')
table(['No.','Jawaban / alasan'],[['19',"localStorage.removeItem('tema'); - hanya menghapus satu kunci."],['20',"$_SESSION['user_id'] = $id; setelah session_start() dan autentikasi berhasil."],['21','session_destroy(); logout lengkap juga mengosongkan $_SESSION dan menghapus cookie sesi.'],['22','Session untuk status login portal kampus; identitas dipercaya dari server.'],['23','Cookie untuk bahasa yang perlu dibaca server saat menyusun halaman.'],['24','Local Storage untuk draft panjang lokal; pertimbangkan sensitivitas dan kuota.'],['25','sessionStorage untuk langkah formulir satu tab; gunakan validasi server jika memengaruhi proses.'],['26','Database terkait akun untuk keranjang yang harus tersedia di HP dan laptop.']],[35,470],9)
h('Mengapa JSON diperlukan?')
p('Cookie dan Web Storage menyimpan teks. JSON mempertahankan struktur array/objek ketika diubah menjadi string. Parsing harus menangani data rusak dan memvalidasi bentuk; JSON bukan enkripsi.')
h('Apa akibat cookie sesi dihapus?')
p('Browser tidak lagi memberikan identifikator sesi yang valid, sehingga dashboard menolak akses. Menghapus cookie saja tidak menjamin data server langsung terhapus; proses logout server tetap diperlukan.')
h('Apa arti persistensi?')
p('Persistensi adalah hasil dalam cakupan tertentu. Local Storage bertahan pada profil dan origin yang sama; browser lain tidak otomatis memiliki data yang sama. Mode privat, penghapusan data situs, dan kebijakan retensi dapat mengubah hasil. Session server juga memiliki timeout dan garbage collection, sehingga menutup browser bukan jaminan pencabutan sesi.')

page('11. Tugas 1 - implementasi dan jawaban')
p('Login ditulis manual dengan Auth::attempt menggunakan username. User memiliki kolom id, username unik, nama_lengkap, password, dan timestamps; dua akun dibuat oleh seeder. Model menggunakan cast password → hashed. Form dan dashboard dibuat dengan Blade. Tidak menggunakan Breeze, Jetstream, atau starter kit.')
table(['Route','Proteksi / perilaku'],[['GET /login','guest; pengguna aktif dialihkan ke dashboard'],['POST /login','Validasi; Auth::attempt; regenerasi session; gagal memberi pesan generik'],['GET /dashboard','auth; tanpa login redirect ke /login'],['POST /logout','auth + CSRF; logout, invalidate, regenerateToken']],[130,375])
h('a. Mengapa password di-hash dan apa isi tabel users?')
p('Hash adalah transformasi satu arah untuk verifikasi password, berbeda dari enkripsi yang dapat dibalik dengan kunci. Database menyimpan hash bcrypt berawalan $2y$, bukan rahasia123. Salt acak membuat dua akun dengan password sama memiliki hash berbeda. Password yang dimasukkan diverifikasi terhadap hash; plaintext tidak perlu disimpan.')
h('b. Mengapa logout menggunakan POST dan @csrf?')
p('Logout mengubah keadaan autentikasi sehingga harus melalui request perubahan, bukan tautan GET yang dapat dipanggil crawler/prefetch. @csrf menghasilkan token agar request yang tidak sah dapat ditolak. Tes request lintas situs tanpa token menghasilkan HTTP 419; GET /logout menghasilkan 405.')
h('c. Fungsi auth dan guest?')
p('auth mengizinkan halaman hanya untuk pengguna terautentikasi. guest menjaga halaman login untuk pengguna belum masuk. Membuka /dashboard tanpa login menghasilkan redirect ke /login; membuka /login saat sudah masuk diarahkan ke dashboard.')
h('d. Fungsi session()->regenerate()?')
p('Mengganti ID session setelah autentikasi berhasil agar ID sebelum login tidak menjadi identifikator sesi autentikasi. Ini mengurangi risiko session fixation. Pengujian framework membandingkan ID server sebelum/sesudah, bukan hanya ciphertext cookie.')
p('Jawaban a-d ditempatkan dalam satu halaman sesuai ketentuan Tugas 1.','SmallCustom')

page('12. Tugas 1 - hash dan login gagal')
pic('t1-tabel-users.png','Isi tabel users dari SELECT langsung melalui PDO; dua password demo tersimpan sebagai hash. Tampilan ini adalah ekspor hasil query, bukan phpMyAdmin.',135)
pic('t1-login-gagal.png','budi dengan password salah menghasilkan “Username atau password salah.” Password tidak dimasukkan kembali ke form.',335)
p('Hash yang berbeda untuk password demo sama diverifikasi oleh tes seeder. Model menyembunyikan password dari serialisasi normal. Screenshot tabel diambil khusus untuk membuktikan penyimpanan hash pada data demonstrasi.')

page('13. Tugas 1 - berhasil masuk dan dashboard')
pic('t1-login-berhasil.png','Sesudah login berhasil: nama Budi Santoso tampil pada dashboard dan pesan sukses terlihat.',270)
pic('t1-dashboard.png','Kunjungan ulang /login oleh pengguna terautentikasi dialihkan kembali ke dashboard.',270)
p('Cookie tugas1_session memiliki HttpOnly. Session disimpan di file server, sedangkan browser membawa identifikator terenkripsi oleh middleware cookie Laravel. Penggantian nilai cookie saja tidak cukup membuktikan regenerasi ID; pembuktian ID dilakukan terpisah pada tes framework.','SmallCustom')

page('14. Tugas 1 - incognito dan logout')
pic('t1-incognito-redirect.png','Browser context incognito baru tanpa cookie mencoba /dashboard dan berakhir di /login. Screenshot halaman ini tidak menampilkan dekorasi jendela incognito; isolasi dan rantai redirect tercatat dalam log.',270)
pic('t1-setelah-logout.png','Sesudah logout POST: form login tampil dengan pesan berhasil logout. Dashboard kemudian diuji ulang dan tetap ditolak.',270)
p('Hasil: 9 tes Laravel/53 assertions serta alur browser lulus. Log: evidence/tugas-1-login-phpunit.txt dan evidence/tugas12-browser.json.','SmallCustom')

page('15. Tugas 2 - keputusan dan jawaban')
h('1. Mekanisme yang dipilih dan alasannya')
p('<b>Local Storage</b> dipilih untuk keranjang tanpa login agar isi bertahan setelah refresh, tab ditutup, dan browser dibuka kembali pada profil/origin yang sama. Data tersimpan hanya [{id,jumlah}]. Katalog lima barang (nama, harga, stok) diperoleh dari tabel barang di server MySQL, bukan dari harga tersimpan di browser.')
h('2. Kelebihan dan kekurangan dibanding alternatif')
p('Kelebihan: tidak mengirim seluruh cart pada setiap request seperti cookie, serta tidak memerlukan ruang session server untuk tiap tamu. Kekurangan: data dapat diubah pengguna/skrip dan tidak otomatis tersedia pada perangkat lain; session memudahkan server memegang cart, sedangkan cookie langsung tersedia bagi server. Akses Local Storage juga memerlukan JavaScript.')
h('3. Manipulasi jumlah menjadi 999')
p('Pada uji browser, kunci modul4.tugas2.keranjang diubah menjadi [{"id":1,"jumlah":999}], lalu halaman dimuat ulang. Aplikasi menampilkan peringatan, membatasi jumlah ke stok Buku Tulis <b>40</b>, dan menyimpan ulang hanya ID/jumlah. Total menjadi <b>Rp200.000</b>. Ini validasi kenyamanan UI; pengguna tetap dapat memodifikasi JavaScript, sehingga bukan batas keamanan checkout.')
h('4. Mengapa harga dari browser tidak dipercaya?')
p('Pengguna dapat mengubah Local Storage, request, dan kode sisi klien. Server checkout harus memuat harga resmi berdasarkan ID, memvalidasi integer positif dan stok terkini, menghitung total sendiri, lalu menyimpan pesanan dalam transaksi. Uji harga/nama palsu pada Tugas 2 diabaikan; implementasi toko menerapkan pemeriksaan checkout di server.')
h('5. Apa yang terjadi pada browser lain?')
p('Keranjang awal kosong karena browser/profil lain mempunyai Local Storage sendiri. Uji context terisolasi menunjukkan kosong. Dua tab pada profil dan origin sama membaca data yang sama; event storage menyinkronkan perubahan. Penutupan dan peluncuran ulang browser dengan profil uji yang sama mempertahankan Rp13.000.')
p('Jawaban 1-5 ditempatkan dalam satu halaman sesuai ketentuan Tugas 2.','SmallCustom')

page('16. Tugas 2 - katalog dan keranjang kosong')
pic('t2-daftar-barang.png','Lima barang dari tabel barang dengan harga, stok, tombol tambah, serta jumlah item di bagian atas.',320)
pic('t2-keranjang-kosong.png','Halaman keranjang kosong dapat dibuka tanpa login.',260)

page('17. Tugas 2 - isi dan ketahanan data')
pic('t2-keranjang-terisi.png','Buku Tulis ×2 dan Pulpen ×1: subtotal Rp10.000 + Rp3.000 = Rp13.000; badge menghitung tiga unit.',240)
pic('t2-setelah-refresh.png','Setelah refresh: isi, jumlah, dan total tetap sama.',240)
table(['Perlakuan yang dijalankan','Hasil aktual'],[['Refresh / buka tab lain','Tetap Rp13.000; perubahan antartab tersinkron'],['Tutup browser, luncurkan ulang profil uji','Tetap Rp13.000'],['Context/profil browser baru','Keranjang kosong'],['Kurangi sampai 0 / hapus / kosongkan','Item atau seluruh cart dihapus sesuai tindakan']],[300,205],8)

page('18. Tugas 2 - lokasi data dan manipulasi')
native=shots/'t2-devtools-local-storage.png'
if native.exists():
    pic(native.name,'Panel DevTools Application > Local Storage pada aplikasi Tugas 2; terlihat kunci dan nilai keranjang.',270)
else:
    table(['Pemeriksaan browser aktual','Nilai'],[['Origin','http://127.0.0.1:8002'],['Storage','Local Storage'],['Key','modul4.tugas2.keranjang'],['Nilai normal','[{"id":1,"jumlah":2},{"id":2,"jumlah":1}]'],['Nilai percobaan','[{"id":1,"jumlah":999}]'],['Setelah validasi halaman','[{"id":1,"jumlah":40}]']],[165,340],9)
    p('<b>Status bukti:</b> nilai di atas diperiksa langsung melalui API browser dan direkam pada log pengujian. Screenshot panel DevTools belum dapat diambil karena alat kontrol komputer menolak akses Google Chrome. Tabel ini tidak diklaim sebagai screenshot DevTools; bukti tersebut perlu dilengkapi setelah izin tersedia.','SmallCustom')
pic('t2-manipulasi-999.png','Hasil nyata setelah nilai 999 dan refresh: peringatan muncul, jumlah menjadi 40, tombol tambah nonaktif, total Rp200.000.',300)
p('JSON rusak juga diuji: aplikasi memulihkan cart kosong dengan pemberitahuan, tanpa uncaught JavaScript error. Nama dan harga palsu pada storage dibuang; data diserialisasi kembali menjadi ID dan jumlah saja.','SmallCustom')

page('19. Toko online - rancangan integrasi')
p('Pengunjung dapat melihat katalog tanpa login. Menambah barang, mengubah keranjang, checkout, dan riwayat memerlukan auth. Keranjang menggunakan <b>database terkait akun</b>, sehingga cart Budi dan Siti terpisah dan dapat dimuat kembali setelah login di perangkat lain.')
table(['Tabel','Kunci dan data penting'],[['users','id_user VARCHAR(15), nama_lengkap, email unik, username unik, password hash, no_hp, alamat'],['products','id_barang VARCHAR(10), nama_barang, deskripsi, harga DECIMAL(12,2), stok, gambar'],['cart_items (tambahan)','PK (id_user,id_barang), jumlah; foreign key ke users/products'],['orders','id_order VARCHAR(15), id_user FK, tanggal_order, total_harga, alamat_pengiriman; checkout_token unik'],['order_details','PK (id_order,id_barang), harga_satuan DECIMAL(12,2), jumlah_beli; FK ke orders/products']],[105,400],8.7)
h('Relasi dan keputusan teknis')
p('Satu user memiliki banyak order; satu order memiliki banyak detail; setiap detail menunjuk satu produk. Keranjang memakai kunci gabungan agar produk yang sama tidak membentuk baris ganda. Kolom jumlah_beli ditulis konsisten huruf kecil mengikuti konvensi kode. Harga_satuan adalah arsip harga saat membeli; perubahan katalog setelahnya tidak mengubah riwayat.')
code('Login → pilih barang → keranjang milik akun\n  → POST /checkout + CSRF + checkout_token\n  → transaksi: lock user → cart → produk berurutan\n  → cek stok + hitung harga database\n  → orders + order_details + pengurangan stok\n  → hapus cart → COMMIT → detail pesanan')
p('Jika satu langkah gagal, seluruh perubahan dibatalkan dan keranjang dipertahankan. Lock user menyerialkan mutasi keranjang akun yang sama; lock produk menangani perebutan stok. Token unik menangani pengiriman ulang tanpa membuat order kedua. Referensi transaksi: <link href="https://laravel.com/docs/13.x/database" color="#087e80">Laravel Database Transactions</link>.','SmallCustom')

page('20. Toko online - katalog sepuluh barang')
pic('toko-katalog.png','Sepuluh barang beserta gambar SVG lokal, harga, dan stok. B010 memiliki stok 0 dan tidak dapat dibeli.',560)
p('Pembatasan stok diterapkan juga pada endpoint server. Menyembunyikan atau menonaktifkan tombol di UI saja tidak cukup: request langsung tetap diperiksa. Gambar adalah ilustrasi SVG dalam source code, tanpa dependensi gambar eksternal.','SmallCustom')

page('21. Toko online - login dan keranjang')
pic('toko-login.png','Pengguna tamu yang membuka keranjang dialihkan ke halaman login.',265)
pic('toko-keranjang.png','Budi memilih Buku Tulis ×2 dan Pulpen ×3. Subtotal Rp10.000 + Rp9.000, ongkir Rp0, total Rp19.000.',285)
p('Input jumlah dapat diubah dan item dapat dihapus. Angka negatif, pecahan, produk tidak dikenal, stok 0, dan jumlah di atas stok ditolak oleh server. Stok tidak berkurang ketika sekadar menambah atau mengubah cart; pengurangan baru terjadi saat checkout.','SmallCustom')

page('22. Toko online - bukti checkout')
pic('toko-checkout-berhasil.png','Checkout berhasil membuat pesanan '+escape(order['id_order'])+' dan menampilkan dua detail barang.',300)
table(['Pemeriksaan','Sebelum','Sesudah'],[['Pesanan Budi','0','1 pesanan'],['Detail pesanan','0','2 baris'],['B001 Buku Tulis','40 unit','38 unit'],['B002 Pulpen','60 unit','57 unit'],['Keranjang Budi','2 jenis / 5 unit','0 baris'],['Total bayar','10.000 + 9.000','Rp19.000; ongkir diabaikan']],[245,130,130],9)
p('Data diverifikasi melalui query langsung terhadap orders, order_details, products, dan cart_items, bukan hanya pesan sukses pada UI. Bukti sebelum/sesudah tersimpan dalam evidence/shop-browser.json; hasil query lengkap pada evidence/database-snapshot.json.')
p('Tes tambahan mengirim harga=1, total_harga=1, jumlah=1000 dan id_user akun lain; checkout tetap menggunakan cart pengguna terautentikasi dan harga database. Simulasi kegagalan pada penyimpanan detail kedua membuktikan order, detail, stok, dan cart di-rollback.','SmallCustom')

page('23. Toko online - riwayat dan cart akhir')
pic('toko-riwayat.png','Riwayat Budi menampilkan pesanan miliknya dengan total Rp19.000 dan tautan detail.',260)
pic('toko-keranjang-setelah-checkout.png','Keranjang Budi kosong setelah transaksi berhasil.',260)
p('Ketika login sebagai Siti dan membuka URL pesanan Budi, server mengembalikan <b>404</b>. Filter id_user diterapkan pada daftar maupun detail. Tes juga memastikan pengiriman ulang checkout tidak menambah order atau mengurangi stok lagi, dan perubahan harga katalog tidak mengubah harga arsip pesanan.','SmallCustom')

page('24. Rekap pengujian dan refleksi')
table(['Pengujian','Hasil aktual'],[['Tugas 1 / PHPUnit','9 lulus, 53 assertions'],['Tugas 2 / PHPUnit','3 lulus, 18 assertions'],['Toko / PHPUnit','18 lulus, 214 assertions'],['Logika cart / Node','6 lulus'],['Browser Tugas 1 dan 2','20 langkah lulus, 0 gagal, 0 uncaught JS error'],['Browser eksperimen','9 kelompok meliputi E01-E17 lulus'],['Browser toko','6 kelompok lulus'],['Responsif Tugas 2','Viewport 390 × 844 tanpa overflow horizontal']],[245,260])
h('Refleksi hasil pengerjaan')
p('Hasil eksperimen memperlihatkan bahwa lokasi penyimpanan, umur data, dan tingkat kepercayaan merupakan hal berbeda. Cookie dan Local Storage sama-sama dapat diubah, walaupun hanya cookie yang dikirim otomatis. Kebutuhan keranjang tamu cocok dengan persistensi lokal, sedangkan autentikasi dan keputusan checkout harus ditangani server.')
p('Percobaan counter +1/+2 menegaskan pentingnya membaca rangkaian request, bukan jumlah klik. Uji sessionStorage juga perlu memisahkan penulisan dari pembacaan agar kesimpulan penutupan tab benar. Pada integrasi toko, pembuktian transaksi tidak cukup melalui tampilan sukses: tabel pesanan, detail, stok, dan cart diperiksa bersama.')
h('Batas hasil dan kelengkapan')
p('Pengujian memakai MariaDB XAMPP dengan driver MySQL. Uji beban atau konkurensi multi-proses belum dilakukan; transaksi, stale stock, pengiriman ulang, dan kegagalan di tengah proses diuji secara fungsional. Pembayaran eksternal, ongkir, admin, dan deployment berada di luar lingkup. Password demo hanya untuk praktikum lokal.')
if not native.exists():p('<b>Belum lengkap:</b> screenshot panel DevTools Application > Local Storage masih menunggu izin akses Chrome. Seluruh source, hasil eksperimen, tangkapan aplikasi, serta uji fungsi sudah tersedia. Jangan menganggap dokumen ini telah memenuhi bukti DevTools sampai gambar tersebut dilengkapi.')
else:p('Bukti panel DevTools, screenshot halaman, hasil query database, dan log pengujian telah disertakan sesuai penjelasan sumbernya.')

page('25. Cara menjalankan dan referensi')
h('Reproduksi singkat')
p('Clone repository, gunakan PHP 8.4+ dan Composer. Siapkan database kosong sesuai tiap .env.example. Jalankan langkah berikut pada masing-masing folder aplikasi; sesuaikan koneksi database sebelum migration. Tidak perlu npm untuk menjalankan aplikasi.')
code('composer install\n# salin .env.example menjadi .env lalu atur DB_*\nphp artisan key:generate\nphp artisan migrate --seed\nphp artisan serve --host=127.0.0.1 --port=8001')
p('Gunakan port 8002 untuk keranjang dan 8003 untuk toko. Login demo: budi atau siti, password rahasia123. Eksperimen memakai php eksperimen/seed_user.php, lalu server lokal sesuai README. Untuk tes, gunakan database terpisah atau SQLite in-memory bawaan phpunit.xml; jangan jalankan RefreshDatabase pada data penting.')
h('Materi utama yang diberikan')
for s in ['Modul Penyimpanan Data pada Web, versi update 5, 28 halaman: konsep, kode eksperimen, aktivitas, kuis 1-26.','Penyimpanan Data pada Web versi 5, 23 slide: ringkasan Cookies, Session, dan Local Storage.','Tugas 1: Login dengan Password Terenkripsi (Laravel 13), 3 halaman.','Tugas 2: Keranjang Belanja Tanpa Login - Memakai Laravel, 2 halaman.','Soal Tugas: Program Toko Online (PR), 2 halaman.']:p(s,'SmallCustom')
h('Dokumentasi pendukung resmi (diakses 5 Oktober 2026)')
for label,url in [('Laravel 13: release notes','https://laravel.com/docs/13.x/releases'),('Laravel 13: authentication','https://laravel.com/docs/13.x/authentication'),('Laravel 13: database transactions','https://laravel.com/docs/13.x/database'),('MDN: Window.localStorage','https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage')]:p('<link href="'+url+'" color="#087e80">'+label+'</link><br/>'+url,'SmallCustom')
h('Tautan hasil')
p('<link href="'+repo+'" color="#087e80">'+repo+'</link>')
p('Nama file pengumpulan: <b>'+escape(filename.name)+'</b>. Hanya file PDF ini yang dikumpulkan di Moodle; source code diakses melalui repository.','SmallCustom')

def decorate(c,doc):
    c.saveState();w,h=A4;c.setStrokeColor(teal);c.setLineWidth(1.2);c.line(43,h-29,w-43,h-29)
    c.setFont('Body',8);c.setFillColor(muted);c.drawString(43,25,'MODUL 04  |  '+args.nim+'  |  '+args.name);c.drawRightString(w-43,25,str(doc.page));c.restoreState()
doc=SimpleDocTemplate(str(filename),pagesize=A4,rightMargin=43,leftMargin=43,topMargin=43,bottomMargin=45,title='Laporan Modul 4 - Penyimpanan Data pada Web',author=args.name)
doc.build(story,onFirstPage=decorate,onLaterPages=decorate)
print(filename)
