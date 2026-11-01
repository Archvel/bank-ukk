## Rangkuman Pertemuan Client

## 1. PENAMBAHAN (ADDITIONS)

- **Tabel Baru: members** Tabel ini dikhususkan untuk menampung data siswa yang belum menabung atau belum mengaktifkan rekening sama sekali. o **Struktur Field (Kolom):**
- user_id (Primary Key sekaligus Foreign Key yang berelasi ke tabel users)
- student_name (Nama siswa)
- graduation_batch / graduation_year (Angkatan keluar)
- account_status (Tipe ENUM: 'active', 'inactive')
o **Hak Akses:** Administrator memiliki wewenang penuh untuk melakukan CRUD (*Create, Read, Update, Delete*) pada tabel members.

- **Sistem Aktivasi Rekening Otomatis:** Terdapat penambahan *logic* sistem di mana jika seorang member datang ke loket Teller dan menyetor uang minimal **Rp10.000**, maka sistem akan secara otomatis memindahkan/mengkloning data pengguna tersebut masuk ke dalam tabel savers (Penabung).
## 2. PENGUBAHAN (MODIFICATIONS)

- **Perubahan Entitas Utama:** Tabel yang pada rancangan awal bernama customers (Nasabah) secara resmi diubah namanya menjadi **savers** (Penabung).
## 3. PENGHAPUSAN (DELETIONS)

- **Penghilangan Fitur Tambah Manual:** Fitur atau tombol Tambah (+) data secara manual pada modul tabel savers **dihapus**. Penambahan entitas *saver* baru kini sepenuhnya bergantung pada eksekusi sistem otomatis saat Teller memproses setoran awal dari tabel members.
## 4. ATURAN BISNIS & FITUR TAMBAHAN (ADDITIONAL RULES & FEATURES)

- **Integritas Riwayat Transaksi Laporan (PDF):** Apabila akun seorang saver dihapus, namun akun tersebut sudah memiliki riwayat transaksi pada hari itu (kas belum ditutup dan belum di-ACC oleh Supervisor), maka data transaksi dari akun tersebut **tidak boleh ikut terhapus**. Setelah akun dihapus dan laporan pada akhirnya di-ACC

oleh SPV, riwayat transaksi akun yang bersangkutan harus tetap muncul secara valid pada Laporan PDF. *(**Catatan Teknis Laravel:** Untuk mengimplementasikan ini,* *sangat disarankan menggunakan fitur SoftDeletes pada model Saver, sehingga saat* *dihapus, datanya hanya disembunyikan dan tidak benar-benar lenyap dari* *database, menjaga integritas relasi pada laporan PDF).*

- **Laporan Bulanan (*Monthly Report*):** Penambahan fitur untuk melakukan rekapitulasi dan pencetakan laporan perbankan secara bulanan, melengkapi modul laporan harian penutupan kas yang sudah ada.
- **Lupa PIN (*Forgot PIN*):** Penambahan alur sistem untuk melakukan pengaturan ulang (*reset*/ *recovery*) PIN keamanan 6-digit bagi siswa/penabung yang lupa.