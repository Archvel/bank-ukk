# UJI KOMPETENSI KEAHLIAN TAHUN AJARAN 2026/2027

## SOAL PRAKTIK KEJURUAN

- **Satuan Pendidikan :** Sekolah Menengah Kejuruan (SMK)
- **Kompetensi Keahlian :** Rekayasa Perangkat Lunak
- **Kode Kompetensi :** Paket 1 RPL-2026
- **Alokasi Waktu :** 16 - 24 Jam
- **Bentuk Penilaian :** Penugasan Perorangan
- **Judul Tugas : Sistem Informasi E-Teller Bank Mini Sekolah**
## I. PETUNJUK UMUM

1. Periksalah dengan teliti seluruh dokumen spesifikasi kebutuhan sistem sebelum mulai menulis kode program.
2. Gunakan perangkat lunak yang bersifat *open-source* atau berlisensi resmi sesuai dengan prosedur kerja standar di laboratorium.
3. Terapkan prinsip Keselamatan dan Kesehatan Kerja (K3) serta kerapian manajemen berkas proyek secara konsisten.
4. Lakukan penyimpanan repositori kode (*commit*) secara berkala dengan pesan yang deskriptif guna mempermudah pelacakan *audit trail*.
5. Pastikan logika backend, *constraint* basis data, dan validasi waktu diimplementasikan secara mutlak untuk mencegah konflik penjadwalan.
## II. DAFTAR PERALATAN DAN PERANGKAT KERJA

|No|Nama Alat/Perangkat|Spesifikasi Minimal|Keterangan|
|---|---|---|---|
|1|Komputer Workstation|CPU Core i3 / setara, RAM 4 GB (8 GB direkomendasikan), Storage Bebas|1 Unit / Siswa|
|2|Sistem Operasi|Windows / Linux Desktop|1 OS / PC|
|3|Perangkat Lunak Pendukung|VS Code / IDE Pilihan, XAMPP / Laragon / Docker environment|Secukupnya|
|4|Database Management System|MySQL / MariaDB / PostgreSQL|Secukupnya|
|5|Koneksi Internet|Minimal 2 Mbps (untuk sinkronisasi package & library)|Stabil|

## III. SOAL / TUGAS PROYEK

Anda ditugaskan untuk membangun aplikasi "E-Teller Bank Mini Sekolah" berbasis arsitektur client-server. Aplikasi wajib dirancang untuk mengawal integritas data operasional loket sekolah dan harus mengikuti logika-logika bisnis yang telah ditetapkan secara ketat.

### 1. Hak Akses Pengguna (Role-Based Access Control)

Sistem Informasi E-Teller Bank Mini Sekolah mengimplementasikan kontrol akses berbasis peran (Role-Based Access Control / RBAC) yang ketat untuk menjamin keamanan data keuangan dan pemisahan tugas (segregation of duties) yang jelas antara setiap aktor yang terlibat. Pembagian hak akses ini dirancang untuk mencegah terjadinya penyalahgunaan wewenang, mempermudah pelacakan jejak audit (audit trail), serta meminimalkan risiko kesalahan operasional loket sekolah harian. Sistem memiliki 4 *role* utama yang dikelola melalui satu pintu gerbang *login* terpusat dengan otorisasi akses sebagai berikut:

**a. Administrator (Sistem Utama & Konfigurasi):** Peran Administrator berada pada tingkatan tertinggi di dalam hierarki sistem yang berfokus pada manajemen infrastruktur data, konfigurasi aplikasi, serta pemeliharaan integritas database. Administrator mengelola operasional sistem di balik layar dan tidak terlibat langsung dalam transaksi keuangan harian loket.
1) Wewenang Utama (Privileges):
- Manajemen Akun Pengguna (User Management): Membuat, memperbarui, memblokir, atau menghapus akun internal sistem (users), termasuk menugaskan seseorang menjadi Teller atau Supervisor.
- Master Data Buku Rekening: Menginisiasi proses pembuatan nomor rekening baru nasabah secara otomatis berbasis algoritma urutan tahun ajaran dan nomor urut siswa.
- Konfigurasi Sistem global: Mengatur parameter inti perbankan mini, seperti batas saldo mengendap minimum (Rp10.000), pengaturan konektivitas perangkat pemindai QR Code, serta hak akses.
- Audit Jurnal Akuntansi (**Back-End**): Memiliki akses penuh untuk membaca dan mengawasi tabel jurnal_akuntansi guna memastikan sinkronisasi sistem akuntansi sekolah berjalan normal.
2) Batasan Keamanan (Restrictions):
- Administrator **tidak diizinkan** melakukan input transaksi setoran tunai maupun penarikan tunai atas nama nasabah pada antrean loket harian berjalan. Hal ini untuk menghindari celah manipulasi di mana pembuat akun juga bertindak sebagai eksekutor dana fisik.

**b. Piket Teller (Front-End Transaction Operator):** Peran Piket Teller bertindak sebagai operator garis depan (frontliner) yang berinteraksi langsung secara fisik dan digital dengan nasabah. Fokus utama peran ini adalah kecepatan, ketelitian entri data, dan kepatuhan terhadap prosedur operasional baku (Standard Operating Procedure / SOP).
1) Wewenang Utama (Privileges):
- Autentikasi Profil Nasabah: Mengakses modul pencarian data nasabah secara instan memanfaatkan integrasi perangkat pemindai (Scanner / Webcam) untuk membaca QR Code kartu nasabah atau melalui input manual nomor rekening.
- Eksekusi Setoran Tunai: Memasukkan data nominal uang simpanan ke dalam aplikasi setelah lolos validasi penghitungan fisik uang tunai.
- Eksekusi Penarikan Tunai: Menginput permohonan pengambilan dana nasabah ke sistem yang selanjutnya akan memicu validasi ketersediaan saldo dan form verifikasi PIN keamanan nasabah.
- Generasi Laporan Penutupan Kas: Melakukan kalkulasi otomatis akhir hari kerja dan mencetak draf Laporan Harian Teller untuk diserahkan kepada Supervisor.
2) Batasan Keamanan (Restrictions):
- Teller tidak dapat memodifikasi, mengedit, atau menghapus transaksi yang telah tersimpan di dalam database.
- Teller tidak berwenang melakukan persetujuan (approval) atas laporan hariannya sendiri. Seluruh data transaksi terkunci secara otomatis setelah tombol simpan ditekan, menunggu proses verifikasi berjenjang.
**c. Supervisor / Kepala Bank Mini (Data Validator & Controller)** Peran Supervisor berfungsi sebagai jembatan kendali mutu operasional antara loket teller dan pihak manajemen sekolah (Bendahara Sekolah/Kepala Sekolah). Peran ini memiliki otoritas penuh untuk memvalidasi dan mengunci keabsahan seluruh pergerakan uang yang terjadi di unit Bank Mini.
1) Wewenang Utama (Privileges):
- Verifikasi & Validasi Akhir (Approval Module): Memeriksa kecocokan data transaksi digital yang ada pada sistem dengan bukti fisik bundel slip transaksi dan uang tunai harian loket yang diserahkan oleh Teller.
- Otorisasi Pemindahan Kas (Brankas Utama): Menandatangani dan menyetujui Laporan Harian Teller secara digital di dalam sistem, yang secara otomatis memicu perintah pembukuan pemindahan tanggung jawab kas dari laci Teller ke Brankas Utama Bank Mini.
- Penyelesaian Selisih Kas (Dispute Resolution): Membuka modul pelacakan riwayat waktu nyata (real-time log) untuk mencari kesalahan input logis apabila terjadi perbedaan nilai saldo antara uang fisik dan sistem komputer.

2) Batasan Keamanan (Restrictions):
- Supervisor **tidak diizinkan** untuk melayani transaksi nasabah secara mandiri di loket penarikan/setoran guna mencegah konflik kepentingan dalam fungsi pengawasan.
**d. Nasabah / Siswa (End-User Access):** Peran Nasabah adalah pengguna luar (external user) yang tidak memiliki hak operasional terhadap sistem keuangan. Hak akses nasabah dibatasi hanya pada area informasi personal mereka sendiri yang bersifat read-only, kecuali pada bagian otorisasi keamanan.
1) Wewenang Utama (Privileges):
- Cek Saldo Mandiri: Mengakses dasbor aplikasi nasabah untuk melihat total saldo berjalan terupdate secara waktu nyata.
- Riwayat Buku Tabungan Digital: Membaca daftar riwayat mutasi rekening (kapan saja mereka menyetor dan menarik uang) tanpa batas waktu secara mandiri.
- Otorisasi Keamanan Penarikan (PIN): Menginput 6-digit PIN rahasia pada layar konfirmasi saat didepan loket Teller sebagai bentuk persetujuan penarikan dana secara sah dari rekening mereka.
2) Batasan Keamanan (Restrictions):
- Nasabah **sama sekali tidak memiliki akses** ke tabel data pengguna internal, jurnal akuntansi, profil nasabah lain, maupun modul input transaksi bank.
Matriks Ringkasan Hak Akses (*Role-Based Access Control* / RBAC) yang memetakan seluruh kontrol operasional, izin eksekusi data (*Create, Read, Update, Delete*), serta batasan fitur secara terperinci untuk mempermudah implementasi pemrograman

|Fitur Utama Sistem|Administrator|Teller|Supervisor|Nasabah|
|---|---|---|---|---|
|Manajemen User (users)||Tidak Ada Akses||Tidak Ada Akses|
||C, R, U, D||R||
|Pembuatan & Manajemen Rekening||||Tidak Ada Akses|
||C, R, U|R|R||
|Autentikasi Profil via Scan QR Code|Konfigurasi|R (Eksekusi)|Tidak Ada Akses|Tidak Ada Akses|
|Input Transaksi Setoran Tunai|Tidak Ada Akses|C|R|Tidak Ada Akses|
|Input Transaksi Penarikan Tunai|Tidak Ada Akses|C|R|Tidak Ada Akses|

#### Fitur Utama

#### Administrator Teller Supervisor Nasabah

#### Sistem

|Otorisasi PIN Keamanan|Tidak Ada Akses|Tidak Ada Akses|Tidak Ada Akses|R (Eksekusi PIN)|
|---|---|---|---|---|
|Generasi & Cetak Draf Laporan Harian|Tidak Ada Akses|C, R|R|Tidak Ada Akses|
|Verifikasi & Approval Laporan Harian|Tidak Ada Akses|Tidak Ada Akses|U (Approve)|Tidak Ada Akses|
|Otorisasi Pemindahan Kas ke Brankas|Tidak Ada Akses|Tidak Ada Akses|U (Approve)|Tidak Ada Akses|
|Cek Saldo & Mutasi Mandiri|Tidak Ada Akses|Tidak Ada Akses|Tidak Ada Akses|R (Data Sendiri)|
|Audit Jurnal Akuntansi ( Back-End )|R|Tidak Ada Akses|R|Tidak Ada Akses|

### Catatan Implementasi Teknis bagi Siswa (Developer):

a. **C (*Create*):** Hak akses untuk membuat atau menambahkan data baru ke dalam database.
b. **R (*Read*):** Hak akses untuk membaca, melihat, atau menampilkan data (bersifat *Read-Only*).
c. **U (*Update*):** Hak akses untuk mengubah, memperbarui status, atau memodifikasi data yang sudah ada.
d. **D (*Delete*):** Hak akses untuk menghapus data (sangat dibatasi untuk menjaga keamanan *ledger* perbankan).
e. **Sistem Proteksi:** Setiap kali program melakukan eksekusi fungsi di atas, sistem wajib melakukan pengecekan SESSION atau TOKEN pengguna untuk memastikan bahwa parameter role di database cocok dengan matriks ini sebelum query SQL dijalankan.

### 2. Spesifikasi Aturan Bisnis & Algoritma

a. **Alur Transaksi Setoran Tunai** Teller memeriksa slip setoran. Jika tidak lengkap/salah, minta nasabah isi ulang. Teller menghitung uang fisik. Jika uang fisik tidak sesuai nominal slip, kembalikan ke nasabah untuk konfirmasi. Jika sesuai, simpan transaksi ke sistem komputer. Sistem otomatis menambah saldo, mencetak struk bukti setoran tunai, dan menyerahkan buku tabungan beserta struk ke nasabah.
b. **Alur Transaksi Penarikan Tunai** Teller memeriksa kelengkapan slip penarikan. Sistem melakukan pengecekan saldo rekening. Jika saldo tidak mencukupi (atau melanggar aturan saldo mengendap minimal Rp 10.000), sistem menolak dan menampilkan sisa saldo. Jika cukup, proses pengurangan saldo berjalan, sistem mencetak struk penarikan, teller menyiapkan uang tunai dan menyerahkan uang, buku tabungan, serta struk kepada nasabah.
c. **Alur Penutupan Kas & Laporan Harian** Teller mengumpulkan slip, menghitung total setoran, total penarikan, dan mencocokkan Saldo Akhir Kas pada komputer dengan Uang Fisik di laci (Rumus: Saldo Awal + Total Setoran-Total Penarikan). Jika ada selisih, lakukan periksa ulang. Jika sesuai, cetak Laporan Harian Teller dan serahkan ke Supervisor. Supervisor memverifikasi data. Jika valid, Supervisor menyetujui laporan, menerima kas fisik untuk disimpan di brankas utama, dan laporan diarsip di sistem secara resmi.

## IV. GAMBAR KERJA & SKEMA BASIS DATA (DATABASE SCHEMA)

Untuk mencegah redundansi data dan memisahkan alur kerja birokrasi, Anda wajib menggunakan penamaan bahasa Inggris terstruktur untuk tabel-tabel di bawah ini:

## V. INSTRUMEN & INDIKATOR PENILAIAN (RUBRIK EVALUASI)

Evaluasi kelulusan didasarkan pada total akumulasi skor dari empat komponen penilaian berikut: Berikut adalah **Instrumen dan Indikator Penilaian (Rubrik Evaluasi)** untuk Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak dengan judul proyek *Sistem Informasi E-Teller Bank* *Mini Sekolah (Advanced Flowchart Edition)*: **RUBRIK PENILAIAN UJI KOMPETENSI KEAHLIAN (UKK)**

No Komponen Penilaian Indikator Keberhasilan / Kriteria Penilaian Skor Maks
I ARSITEKTUR DATA & DATABASE SCHEMA (Bobot 25%)
1.1 Normalisasi & Struktur Tabel Database dirancang memenuhi kaidah 3NF (tidak ada duplikasi data), menggunakan engine InnoDB, dan tipe data nominal rupiah dikunci dengan tipe data yang presisi (INT / BIGINT).	10
1.2 Integritas Relasi ( Constraints ) Penerapan Foreign Key Constraints secara tepat menggunakan aturan ON DELETE RESTRICT pada tabel master (users dan nasabah) untuk mencegah penghapusan jejak audit transaksi.	10
1.3 Validasi Level Database Penerapan Check Constraint (nominal > 0) langsung pada tabel untuk menghalau data siluman atau manipulasi nilai transaksi minus.	5
II IMPLEMENTASI ATURAN BISNIS & ALGORITMA (Bobot 40%)
2.1 Algoritma Setoran Tunai & Kesesuaian Fisik Sistem mampu mencocokkan input dengan uang fisik secara presisi harian. Mengotomatisasi penambahan saldo dan mencetak struk bukti setoran.	10
2.2 Validasi Batas Saldo Mengendap Algoritma penarikan tunai secara real-time menolak transaksi jika sisa saldo nasabah setelah penarikan berada di bawah batas minimum Rp10.000.	10
2.3 Mekanisme Double-Entry Jurnal Setiap 1 transaksi di loket berhasil memicu penulisan otomatis 2 baris jurnal akuntansi di back-end (Debet Kas 101 dan Kredit Tabungan 201) secara seimbang ( balance ).	10
2.4 Alur Fitur penutupan kas menghitung rumus saldo	10

|No|Komponen Penilaian|Indikator Keberhasilan / Kriteria Penilaian|Skor Maks|
|---|---|---|---|
||Rekonsiliasi Akhir Hari & Otorisasi|akhir secara akurat. Sistem mengunci modul cetak laporan jika terdeteksi selisih antara uang fisik laci teller dengan hitungan digital sistem.||
|III|MANAJEMEN KEAMANAN & HAK AKSES / RBAC (Bobot 35%)|||
|3.1|Kredensial & Keamanan Autentikasi|Proses login dilengkapi dengan enkripsi kata sandi yang aman ( password hashing ) serta verifikasi PIN 6-digit nasabah untuk validasi penarikan dana.|10|
|3.2|Implementasi Middleware / Session Guard|Sistem membatasi hak akses secara mutlak. Jika pengguna tingkat rendah ( Teller atau Nasabah ) mencoba menembak URL halaman Supervisor, sistem langsung memblokir dan melempar balik ke halaman login.|15|
|3.3|Integrasi Perangkat & Fitur QR Code|Fitur pencarian profil nasabah dapat dieksekusi secara instan dan akurat menggunakan pemindaian QR Code melalui kamera/webcam loket.|10|
|TOTAL SKOR MAKSIMAL|||100|

#### STANDAR KONVERSI NILAI KELULUSAN LOGIKA

- **Skor 91 - 100 (Sangat Kompeten):** Seluruh indikator terpenuhi tanpa ada kesalahan logika (*bug-free*). Program menggunakan kaidah penulisan kode berbasis OOP yang bersih, aman dari percobaan manipulasi data keuangan, dan memiliki penanganan *error* *handling* (Try-Catch & Rollback) yang matap.
- **Skor 75-90 (Kompeten):** Seluruh fitur utama berjalan normal. Namun, terdapat beberapa validasi aturan bisnis yang hanya berjalan di sisi tampilan depan (*front-end*) dan belum dikunci secara ketat di sisi server/database.
- **Skor < 75 (Belum Kompeten):** Terjadi celah fatal seperti *overdraft* (saldo nasabah bisa menjadi minus), data transaksi tidak sinkron dengan jurnal akuntansi, atau hak akses pengguna bocor sehingga *Nasabah* bisa memanipulasi data loket.
#### "SELAMAT & SUKSES"