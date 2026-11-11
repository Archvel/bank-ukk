# Sistem Informasi E-Teller Bank Mini Sekolah (`bank-ukk`)

Aplikasi web dan REST API **E-Teller Bank Mini Sekolah** (implementasi studi kasus **Bank Mini SMKN 1 / SMK Negeri 1 Bantul** — Proyek UKK Rekayasa Perangkat Lunak) yang dirancang untuk mengelola operasional tabungan siswa, loket kasir Teller dengan pemindaian QR Code, verifikasi PIN 6-digit, akuntansi *double-entry* otomatis, rekonsiliasi tutup kas harian, serta portal mandiri dan API aplikasi mobile untuk nasabah siswa (*saver*).

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Peran & Hak Akses (RBAC)](#peran--hak-akses-rbac)
- [Aturan Bisnis & Alur Akuntansi](#aturan-bisnis--alur-akuntansi)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Struktur Direktori Utama](#struktur-direktori-utama)
- [Skema Basis Data](#skema-basis-data)
- [Instalasi & Konfigurasi Lokal](#instalasi--konfigurasi-lokal)
- [Akun Bawaan (Seeder)](#akun-bawaan-seeder)
- [Daftar Endpoint API](#daftar-endpoint-api)
- [Pengujian & Pemformatan Kode](#pengujian--pemformatan-kode)
- [Deployment](#deployment)

---

## Fitur Utama

### 1. Portal Administrator & Supervisor (`/admin/*`)
- **Dashboard Analitik**: Ringkasan total simpanan nasabah aktif, saldo Kas Brankas Utama (Akun `102`), rekap setoran/penarikan serta perputaran kas hari ini, grafik arus kas masuk vs keluar 7 hari terakhir (*Chart.js*), daftar penutupan kas yang menunggu verifikasi, dan aktivitas pembukaan rekening/transaksi terbaru.
- **Manajemen Pengguna (`/admin/users`)**: Pengelolaan akun internal (`admin`, `teller`, `nasabah`) lengkap dengan filter peran, pencarian, serta fitur aktivasi/nonaktifkan akun (*toggle active*).
- **Data Calon Penabung / Siswa (`/admin/members`)**:
  - CRUD data calon penabung (`members`) dengan filter status akun, tahun kelulusan/angkatan, kelas, dan jurusan.
  - **Impor Massal Excel/CSV** (`MemberExcelImportService`) mendukung format `.xlsx` (XML parsing native via `ZipArchive`) dan `.csv`, serta unduh template CSV siap pakai.
  - **Kenaikan Kelas & Kelulusan Massal** (`PromoteStudentsAction`) secara menyeluruh (`X -> XI -> XII -> Alumni`), per tingkat, per kelas, maupun per siswa.
- **Master Rekening Penabung (`/admin/savers`)**: Pemantauan rekening tabungan aktif/diblokir/ditutup, detail mutasi per rekening, reset PIN keamanan 6-digit oleh admin, serta mekanisme **SoftDelete & Restore** agar data transaksi terdahulu tetap utuh untuk kebutuhan laporan audit.
- **Verifikasi Kas Harian (`/admin/cash-closures`)**: Peninjauan laporan penutupan kas harian Teller (`submitted`), deteksi selisih kas (*disputes*), serta persetujuan (*Approve*) atau penolakan (*Reject*) yang secara otomatis memicu jurnal pemindahan kas ke Brankas Utama.
- **Audit Jurnal Akuntansi (`/admin/audit-journals`)**: Pencatatan jurnal berpasangan (*double-entry*) otomatis beserta filter kode akun, tipe (`debit`/`credit`), tanggal, dan verifikasi keseimbangan (*equilibrium check*: Total Debit = Total Kredit).
- **Laporan Transaksi & Cetak PDF (`/admin/reports`)**: Laporan Harian Penutupan Kas dan Laporan Bulanan (*Monthly Report*) dengan format dokumen siap cetak (`window.print()`) yang tetap menyertakan transaksi milik nasabah yang di-*SoftDelete*.
- **Konfigurasi Sistem (`/admin/settings`)**: Tampilan parameter global perbankan (saldo mengendap, setoran awal minimal, format nomor rekening, batas penarikan harian) dan utilitas uji kamera/scanner QR loket.

### 2. Meja Layanan Loket / Piket Teller (`/teller/*`)
- **Manajemen Kas Awal Sesi (`/teller/opening-cash`)**: Inisialisasi kas awal laci Teller dengan jam operasional sesi **`07:00 – 15:30 WIB`** (`Asia/Jakarta`) serta rekomendasi saldo awal dari penutupan kas tervalidasi sebelumnya.
- **POS Counter & Pencarian Instan (`/teller/counter`)**:
  - Lookup cepat berdasarkan Nomor Rekening (`BM...`), Token QR (`QR-BM...`), NIS, atau Nama siswa.
  - Dukungan **QR Code Scanner** langsung via kamera webcam atau unggah file gambar QR (`html5-qrcode`).
- **Aktivasi Rekening Otomatis**: Calon penabung (`members` berstatus `inactive`) otomatis dipromosikan menjadi penabung aktif (`savers`) saat menyetor setoran awal minimal **Rp10.000** disertai pembuatan PIN keamanan 6-digit — sistem langsung menerbitkan nomor rekening `BM{YYYY}{0001}` dan `qr_code_token`.
- **Setoran & Penarikan Tunai**: Validasi otorisasi **PIN 6-digit** nasabah, penguncian baris basis data (`lockForUpdate`) untuk mencegah *race condition*, dan penegakan batas **saldo mengendap minimum Rp10.000**.
- **Riwayat Transaksi & Struk Thermal (`/teller/transactions`)**: Rekap transaksi Teller hari berjalan dan halaman cetak struk ukuran thermal 80mm.
- **Penutupan Kas Harian (`/teller/cash-closure`)**: Perhitungan otomatis saldo akhir sistem (`Kas Awal + Total Setoran - Total Penarikan`) dan validasi keseimbangan uang fisik laci sebelum diajukan ke Administrator/Supervisor.

### 3. Portal Web & REST API Nasabah / Siswa (`/nasabah/*` & `/api/saver/*`)
- **Beranda & Kartu Rekening Digital (`/nasabah/dashboard`)**: Status operasional loket Teller secara *real-time* (`LOKET BUKA`, `BELUM BUKA`, `LOKET TUTUP`), tampilan saldo berjalan, saldo maksimal yang dapat ditarik, serta **QR Code Kartu Nasabah** (`QRCode.js`) siap pindai di loket.
- **Buku Tabungan Digital (`/nasabah/mutations`)**: Riwayat mutasi setoran dan penarikan lengkap dengan filter jenis transaksi, periode/tanggal, dan pencarian nomor referensi.
- **Pusat Keamanan & Lupa PIN (`/nasabah/pin`)**: Pembuatan token reset PIN 8-karakter (berlaku 24 jam) dan konfirmasi penggantian PIN keamanan 6-digit.
- **REST API Mobile (Laravel Sanctum)**: Endpoint khusus aplikasi mobile nasabah (`saver`) mencakup autentikasi, profil, saldo, mutasi, status loket real-time, serta verifikasi, ubah, dan reset PIN.

---

## Peran & Hak Akses (RBAC)

Sistem menggunakan enum [`App\Enums\Role`](app/Enums/Role.php) dan middleware [`EnsureRole`](app/Http/Middleware/EnsureRole.php) (`role` alias pada [`bootstrap/app.php`](bootstrap/app.php)):

| Peran (`role`) | Label | Area Akses Utama | Batasan Keamanan |
|---|---|---|---|
| `admin` | Administrator & Supervisor | Manajemen pengguna, calon penabung (`members`), master rekening (`savers`), approval penutupan kas, audit jurnal, laporan harian/bulanan, pengaturan sistem. | Tidak memiliki akses eksekusi transaksi setoran/penarikan tunai di loket Teller. |
| `teller` | Piket Teller | Pembukaan kas awal sesi, lookup QR/rekening/NIS, aktivasi rekening via setoran awal, transaksi setor/tarik tunai, cetak struk, pengajuan penutupan kas harian. | Tidak dapat mengubah/menghapus transaksi tersimpan dan tidak dapat menyetujui laporan kasnya sendiri. |
| `nasabah` | Nasabah / Siswa (`saver` di API) | Cek saldo & status loket *real-time*, kartu QR Code digital, buku tabungan/mutasi pribadi, kelola & reset PIN 6-digit. | Bersifat *read-only* terhadap data keuangan pribadi; tidak dapat mengakses data nasabah lain, loket, maupun jurnal akuntansi. |

---

## Aturan Bisnis & Alur Akuntansi

1. **Aktivasi Rekening Otomatis (`ActivateMemberAction` & `DepositAction`)**:
   - Penambahan entitas `savers` tidak dilakukan secara manual oleh Admin, melainkan otomatis saat calon penabung (`members` berstatus `inactive`) melakukan setoran awal di loket Teller minimal **Rp10.000** beserta penetapan **PIN 6-digit**.
   - Format Nomor Rekening: `BM{YYYY}{0001}` (berbasis ID urut `savers` termasuk yang di-*SoftDelete*).
   - Format QR Code Token: `QR-{account_number}-{6_RANDOM_ALPHANUMERIC}`.
2. **Batas Saldo Mengendap Minimum (`WithdrawAction::MINIMUM_MAINTAINING_BALANCE`)**:
   - Setiap rekening wajib menyisakan saldo mengendap minimal **Rp10.000**. Penarikan yang membuat saldo akhir di bawah Rp10.000 otomatis ditolak oleh server.
   - Setiap penarikan tunai wajib melewati verifikasi **PIN 6-digit** yang tersimpan dalam bentuk *hash*.
3. **Jurnal Akuntansi Double-Entry (`CreateJournalEntryAction` & `AccountSeeder`)**:
   - Bagan akun inti:
     - `101` — **Kas Teller Loket** (`asset`)
     - `102` — **Kas Brankas Utama** (`asset`)
     - `201` — **Simpanan Tabungan Nasabah** (`liability`)
   - Pembukuan otomatis per kejadian:
     - **Setoran Tunai**: Debit `101` (Kas Teller Loket) / Kredit `201` (Simpanan Tabungan Nasabah).
     - **Penarikan Tunai**: Debit `201` (Simpanan Tabungan Nasabah) / Kredit `101` (Kas Teller Loket).
     - **ACC Penutupan Kas Harian**: Debit `102` (Kas Brankas Utama) / Kredit `101` (Kas Teller Loket) senilai saldo kas fisik akhir sesi Teller.
4. **Rekonsiliasi Tutup Kas Harian (`CloseCashAction` & `Teller\CashClosureController`)**:
   - Rumus sistem: `Saldo Akhir Sistem = Kas Awal + Total Setoran - Total Penarikan`.
   - Pada loket web Teller, pengajuan tutup kas hanya diterima apabila uang fisik laci sama persis dengan saldo akhir sistem (`difference === 0`).
5. **Integritas Audit SoftDelete (`Saver` & `ReportController`)**:
   - Penghapusan rekening pada master `savers` menggunakan `SoftDeletes`. Riwayat transaksi dan laporan PDF harian/bulanan tetap menampilkan transaksi dari akun terhapus (`withTrashed()`) agar keseimbangan buku besar tetap terjaga.

---

## Teknologi yang Digunakan

- **Backend**: PHP `^8.3`, Laravel Framework `^13.0`, Laravel Sanctum `^4.0`, Laravel Breeze `^2.4`, Laravel Tinker `^3.0`
- **Frontend**: Blade Templates, Tailwind CSS `^3.1.0` (`@tailwindcss/forms`, `@tailwindcss/vite`), Alpine.js `^3.4.2`, Axios `^1.11.0`, Vite `^7.0.7`
- **Pustaka UI (CDN pada View)**: Chart.js, `html5-qrcode`, `qrcodejs`, Google Fonts (`Figtree`, `Inter`, `Plus Jakarta Sans`), Material Symbols Outlined
- **Database**: SQLite (default `.env.example` & `:memory:` untuk PHPUnit) atau MySQL / MariaDB (dengan *check constraints* `balance >= 0` & `amount > 0` pada migrasi MySQL)
- **Pengujian & Quality Tools**: PHPUnit `^12.5.12`, Laravel Pint `^1.27`, Laravel Pail `^1.2.5`, Mockery, Faker

---

## Struktur Direktori Utama

```text
bank-ukk/
├── app/
│   ├── Actions/               # Logika bisnis inti (Deposit, Withdraw, ActivateMember, CloseCash, Journal, dll.)
│   ├── Enums/                 # Role, TransactionType, SaverStatus, MemberAccountStatus, CashClosureStatus, dll.
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/         # Portal web Administrator & Supervisor
│   │   │   ├── Api/           # REST API (Auth, Admin, Teller, Nasabah/Saver, Report)
│   │   │   ├── Auth/          # Autentikasi web (Breeze, mendukung login email/username)
│   │   │   ├── Nasabah/       # Portal web Nasabah / Siswa
│   │   │   └── Teller/        # Portal web Meja Layanan Loket Teller
│   │   ├── Middleware/        # EnsureRole (RBAC guard & status aktif akun)
│   │   ├── Requests/          # Form Request validation
│   │   └── Resources/         # Eloquent API Resources
│   ├── Models/                # User, Member, Saver (SoftDeletes), Account, Transaction, CashClosure, JournalEntry, PinResetRequest
│   └── Services/              # MemberExcelImportService (.xlsx & .csv parser/template)
├── database/
│   ├── data/raw_students.txt  # Data mentah daftar siswa untuk seeder
│   ├── migrations/            # Skema tabel & constraints
│   └── seeders/               # DatabaseSeeder, AccountSeeder, StudentDataSeeder
├── guides/                    # Dokumen spesifikasi soal UKK & rangkuman pertemuan klien
├── resources/views/
│   ├── admin/                 # Tampilan modul Administrator & Supervisor
│   ├── auth/                  # Halaman login, register, reset password
│   ├── layouts/               # Layout khusus admin, teller, nasabah, app, guest
│   ├── nasabah/               # Beranda, mutasi buku tabungan, keamanan PIN
│   ├── teller/                # POS Counter, riwayat transaksi, struk thermal, tutup kas
│   └── landing.blade.php      # Halaman landing publik Bank Mini
├── routes/
│   ├── api.php                # Rute REST API (Sanctum)
│   ├── auth.php               # Rute autentikasi Breeze
│   └── web.php                # Rute web per peran (admin, teller, nasabah)
├── tests/Feature/             # Suite pengujian fitur RBAC, transaksi, jurnal, API mobile, & layar web
├── .github/workflows/         # CI/CD build & FTP deploy (deploy.yml)
├── AGENTS.md                  # Panduan pengembangan Laravel Boost proyek
└── phpunit.xml                # Konfigurasi pengujian PHPUnit
```

---

## Skema Basis Data

Tabel-tabel utama yang didefinisikan pada `database/migrations/`:

| Tabel | Keterangan | Relasi & Constraint Penting |
|---|---|---|
| `users` | Akun autentikasi semua peran (`admin`, `teller`, `nasabah`) | `username` & `email` unik, status `is_active` |
| `members` | Data siswa calon penabung (termasuk `nis`, `absen`, `class_name`, `major`, `academic_year`, `gender`, `graduation_year`, `account_status`) | `user_id` unique FK -> `users` (`ON DELETE RESTRICT`) |
| `savers` | Rekening tabungan nasabah aktif (`account_number`, `pin`, `qr_code_token`, `balance`, `status`, `deleted_at`) | `user_id` & `member_id` unique FK (`RESTRICT`), `SoftDeletes`, MySQL `CHECK (balance >= 0)` |
| `accounts` | Bagan akun akuntansi (`101`, `102`, `201`) | `code` unik, `category` (`asset`, `liability`, dll.) |
| `transactions` | Mutasi setoran & penarikan (`transaction_number`, `type`, `amount`, `opening_balance`, `closing_balance`) | `saver_id` & `teller_id` FK (`RESTRICT`), MySQL `CHECK (amount > 0)` |
| `cash_closures` | Sesi & penutupan kas harian per Teller | Unique (`teller_id`, `closure_date`), `approved_by` FK -> `users` |
| `journal_entries` | Jurnal akuntansi berpasangan (`entry_number`, `account_code`, `type`, `amount`, `entry_date`) | FK ke `transactions`, `cash_closures`, & `accounts.code` (`RESTRICT`), MySQL `CHECK (amount > 0)` |
| `pin_reset_requests` | Permohonan & token reset PIN 6-digit (`reset_token`, `status`, `expires_at`) | `saver_id` FK -> `savers` (`RESTRICT`), `verified_by` FK -> `users` |
| `personal_access_tokens` | Token akses Sanctum untuk API / Mobile | Bawaan Laravel Sanctum |

---

## Instalasi & Konfigurasi Lokal

### Prasyarat
- **PHP** >= 8.3 dengan ekstensi: `mbstring`, `xml`, `ctype`, `iconv`, `intl`, `bcmath`, `fileinfo`, `gd`, `zip`, `curl`, serta `pdo_mysql` atau `pdo_sqlite`
- **Composer**
- **Node.js** (disarankan v20+) & **npm**
- **Database**: SQLite (default di `.env.example`) atau MySQL / MariaDB

### Langkah Instalasi Cepat

```bash
# 1. Clone atau masuk ke direktori proyek
cd bank-ukk

# 2. Pasang dependency PHP dan JavaScript
composer install
npm install

# 3. Salin konfigurasi environment dan buat application key
cp .env.example .env
php artisan key:generate
```

*(Pada Windows PowerShell, gunakan `Copy-Item .env.example .env` sebagai pengganti `cp`.)*

### Konfigurasi Database
- **Opsi A — SQLite (default di `.env.example`)**: Pastikan berkas `database/database.sqlite` tersedia (`touch database/database.sqlite` atau buat file kosong), lalu jalankan migrasi & seeder.
- **Opsi B — MySQL / MariaDB**: Ubah pengaturan `DB_*` di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bank_ukk
DB_USERNAME=root
DB_PASSWORD=
```

### Migrasi, Seeding, dan Menjalankan Aplikasi

```bash
# Jalankan migrasi tabel dan seeder awal (akun & bagan akun + data siswa)
php artisan migrate --seed

# Kompilasi aset frontend untuk produksi
npm run build
```

Untuk menjalankan server pengembangan secara bersamaan (Laravel server, queue listener, Pail log, dan Vite dev server):

```bash
composer run dev
```

Atau jalankan secara terpisah:

```bash
php artisan serve
npm run dev
```

*(Tersedia juga skrip komposit `composer run setup` di `composer.json` untuk instalasi otomatis awal.)*

---

## Akun Bawaan (Seeder)

Menjalankan `php artisan db:seed` (`DatabaseSeeder`) akan membuat akun berikut secara idempoten (`firstOrCreate` / `updateOrCreate`):

| Peran | Nama | Username | Email | Password |
|---|---|---|---|---|
| **Administrator & Supervisor** | Administrator & Supervisor | `admin` | `admin@bankmini.test` | `password` |
| **Piket Teller** | Piket Teller 1 | `teller1` | `teller@bankmini.test` | `password` |
| **Calon Nasabah / Siswa** | *(Dari `database/data/raw_students.txt`)* | `<NIS>` (contoh: `18910`) | `<NIS>@siswa.bankmini.test` | `password123` |

> **Catatan:** Seluruh akun siswa hasil `StudentDataSeeder` dibuat dengan status awal `inactive` pada tabel `members`. Rekening tabungan (`savers`) akan aktif otomatis setelah siswa melakukan setoran awal minimal **Rp10.000** di loket Teller.

---

## Daftar Endpoint API

Seluruh endpoint di bawah prefix `/api` didefinisikan pada [`routes/api.php`](routes/api.php). Endpoint terproteksi menggunakan header `Authorization: Bearer <token>` (Laravel Sanctum).

### Publik & Autentikasi
- `POST /api/auth/login` — Login semua peran menggunakan `login` (email/username) & `password`
- `POST /api/saver/login` | `POST /api/nasabah/login` — Login khusus aplikasi mobile nasabah (`saver`)
- `POST /api/saver/pin-reset/request` | `POST /api/pin-reset/request` — Ajukan token reset PIN
- `POST /api/saver/pin-reset/confirm` | `POST /api/pin-reset/confirm` — Konfirmasi reset PIN dengan token & PIN 6-digit baru

### Umum (Terautentikasi `auth:sanctum`)
- `POST /api/auth/logout` | `POST /api/saver/logout` | `POST /api/nasabah/logout`
- `GET /api/auth/me` — Profil pengguna, data `member`, dan ringkasan `saver`
- `GET /api/reports/receipt/{transaction}` — Data struk transaksi
- `GET /api/reports/monthly` — Rekap laporan bulanan
- `GET /api/reports/daily-teller/{closure}` — Detail laporan harian Teller

### Administrator (`role:admin`)
- `GET/POST/PUT/DELETE /api/admin/users` — Manajemen pengguna
- `GET/POST/PUT/DELETE /api/admin/members` — Manajemen calon penabung
- `GET /api/admin/members/template-import` — Unduh template CSV impor siswa
- `POST /api/admin/members/import` — Impor siswa dari file `.xlsx` / `.csv`
- `POST /api/admin/members/promote` & `POST /api/admin/members/{member}/promote` — Kenaikan kelas siswa
- `GET /api/admin/audit-journals` & `GET /api/admin/audit-journals/summary` — Audit jurnal & cek keseimbangan
- `GET /api/admin/cash-closures/pending`, `/disputes`, `/{closure}`, `POST /api/admin/cash-closures/{closure}/process` — Verifikasi & approval penutupan kas
- `GET /api/admin/system-settings` — Pengaturan batas minimal sistem

### Piket Teller (`role:teller`)
- `GET /api/teller/savers`, `GET /api/teller/savers/lookup?code=...`, `GET /api/teller/savers/{saver}` — Lookup rekening/QR nasabah
- `GET /api/teller/members/pending` — Daftar calon penabung yang belum aktif
- `POST /api/teller/transactions/deposit` — Setoran tunai & aktivasi rekening awal
- `POST /api/teller/transactions/withdraw` — Penarikan tunai (verifikasi PIN 6-digit & saldo mengendap Rp10.000)
- `GET /api/teller/transactions/today` & `GET /api/teller/transactions/{transaction}`
- `GET /api/teller/opening-cash` & `POST /api/teller/opening-cash` — Cek & simpan kas awal sesi
- `GET /api/teller/cash-closure/calculate`, `POST /api/teller/cash-closure/submit`, `GET /api/teller/cash-closure/today`

### Nasabah / Mobile Saver (`role:saver,nasabah`)
*(Tersedia di prefix `/api/saver/*` dan `/api/nasabah/*`)*
- `GET /api/saver/profile` (`GET /api/nasabah/profile`) — Profil lengkap & metadata rekening
- `GET /api/saver/balance` (`GET /api/nasabah/account/balance`) — Saldo aktual, saldo dapat ditarik, & token QR
- `GET /api/saver/mutations` (`GET /api/nasabah/account/mutations`) — Riwayat mutasi & ringkasan total setor/tarik
- `GET /api/saver/counter-status` (`GET /api/nasabah/counter-status`) — Status buka/tutup loket Teller real-time
- `POST /api/saver/pin/verify` — Validasi PIN 6-digit
- `POST /api/saver/pin/change` — Ganti PIN menggunakan PIN saat ini
- `POST /api/saver/pin/forgot-request` & `POST /api/saver/pin/forgot-confirm` — Permohonan & konfirmasi lupa PIN saat login

---

## Pengujian & Pemformatan Kode

Suite pengujian berada di `tests/Feature/` dengan konfigurasi database SQLite `:memory:` di [`phpunit.xml`](phpunit.xml), mencakup pengujian:
- Autentikasi & RBAC (`AuthTest`, `RbacSecurityTest`)
- Manajemen Admin, Impor Excel/CSV, & Kenaikan Kelas (`AdminManagementTest`, `AdminMemberImportTest`, `AdminWebScreensTest`, `StudentPromotionTest`)
- Kas Awal, POS Teller, Aktivasi Rekening, Setoran, & Penarikan PIN (`TellerOpeningCashTest`, `TellerWebScreensTest`, `MemberActivationAndDepositTest`, `WithdrawalAndPinVerificationTest`)
- Penutupan Kas, Pemindahan Brankas, SoftDelete, Reset PIN, & Laporan (`CashClosureAndVaultTransferTest`, `SoftDeleteSaverIntegrityTest`, `PinResetAndReportTest`)
- Portal Web & Mobile API Nasabah (`NasabahWebScreensTest`, `SaverMobileApiTest`)

Jalankan pengujian dan formatter dengan perintah:

```bash
# Menjalankan seluruh unit & feature test
php artisan test

# Atau via Composer script
composer test

# Memeriksa/merapikan gaya penulisan PHP dengan Laravel Pint
vendor/bin/pint
```

---

## Deployment

Repositori menyertakan alur kerja GitHub Actions pada [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) yang secara otomatis:
1. Menyiapkan PHP 8.4 dan menginstal dependency Composer produksi (`--no-dev`).
2. Mengompilasi aset Vite (`npm run build`).
3. Mengunggah berkas ke server target melalui FTP (`SamKirkland/FTP-Deploy-Action`).
4. Memicu endpoint migrasi basis data pasca-deploy menggunakan `DEPLOY_SECRET`.
