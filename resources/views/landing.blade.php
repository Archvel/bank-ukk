<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'E-Teller Bank Mini') }} - Sistem Informasi Bank Mini</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Scripts and Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        .font-headline {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .bg-grid-subtle {
            background-size: 36px 36px;
            background-image:
                linear-gradient(to right, rgba(51, 65, 85, 0.12) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(51, 65, 85, 0.12) 1px, transparent 1px);
        }

        .glow-emerald {
            box-shadow: 0 0 35px -5px rgba(16, 185, 129, 0.22);
        }

        .specular-top-edge {
            position: relative;
        }

        .specular-top-edge::before {
            content: '';
            position: absolute;
            top: 0;
            left: 8%;
            right: 8%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(52, 211, 153, 0.35), transparent);
        }
    </style>
</head>

<body
    class="bg-[#090D16] text-slate-100 antialiased selection:bg-emerald-500 selection:text-slate-950 min-h-screen flex flex-col">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-40 bg-[#090D16]/85 backdrop-blur-md border-b border-slate-800/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                <div
                    class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center p-1 group-hover:border-emerald-500/50 shadow-inner transition-colors overflow-hidden">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Mini" class="w-full h-full object-contain">
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-headline font-bold text-lg text-white tracking-tight">Bank Mini</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
                <a href="#fitur" class="text-slate-300 hover:text-emerald-400 transition-colors">Fitur Layanan</a>
                <a href="#faq" class="text-slate-300 hover:text-emerald-400 transition-colors">FAQ</a>
            </nav>

            <!-- Trailing Action (Masuk Portal / Dashboard) -->
            <div class="flex items-center gap-3">
                @auth
                    @php
                        $user = auth()->user();
                        $targetDashboard = route('dashboard');
                        if ($user?->isAdmin()) {
                            $targetDashboard = route('admin.dashboard');
                        } elseif ($user?->isTeller()) {
                            $targetDashboard = route('teller.counter');
                        } elseif ($user?->isNasabah()) {
                            $targetDashboard = route('nasabah.dashboard');
                        }
                    @endphp
                    <a href="{{ $targetDashboard }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-sm shadow-md shadow-emerald-500/20 transition-all duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard ({{ $user->name }})</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-100 border border-slate-800 hover:border-emerald-500/50 transition-all duration-150 text-sm font-medium group">
                        <span>Login</span>
                        <svg class="w-4 h-4 text-emerald-400 group-hover:translate-x-0.5 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-grow">

        <!-- HERO SECTION -->
        <section class="relative pt-16 pb-20 sm:pt-20 sm:pb-24 overflow-hidden bg-grid-subtle">
            <!-- Ambient Emerald Glow -->
            <div
                class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[650px] h-[360px] bg-emerald-500/10 blur-[130px] rounded-full pointer-events-none">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Headline & Subtitle -->
                <div class="max-w-4xl mx-auto text-center mb-10">
                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-headline font-extrabold text-white tracking-tight leading-tight mb-5">
                        Kelola Tabungan Siswa &amp; Operasional Loket Sekolah yang Transparan, Akuntabel, dan Real-Time
                    </h1>
                    <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
                        Solusi terpadu kasir loket Teller sekolah dan akses rekening mandiri siswa melalui aplikasi
                        mobile saver digital. Aman, cepat, dan terverifikasi berjenjang.
                    </p>
                </div>

                <!-- Dual CTA Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <!-- Primary CTA (Modal Trigger) -->
                    <div class="relative flex items-center">
                        <button type="button" onclick="openDownloadModal()"
                            class="px-6 py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-headline font-bold text-base flex items-center gap-2.5 shadow-lg shadow-emerald-500/25 transition-all duration-150 active:scale-95 group cursor-pointer">
                            <!-- Android Icon -->
                            <svg class="w-5 h-5 text-slate-950" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993s-.4483.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9994.4482.9994.9993s-.4483.9997-.9994.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.587 8.4286 13.856 8.125 12 8.125s-3.587.3036-5.1368.8247L4.8409 5.4467a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3432-4.1021-2.6889-7.5743-6.1185-9.4396" />
                            </svg>
                            <span>Download Aplikasi Nasabah (.APK)</span>
                            <svg class="w-4 h-4 group-hover:translate-y-0.5 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                        <span
                            class="absolute -top-3 -right-2 px-2.5 py-0.5 rounded-full text-[10px] font-headline font-bold bg-amber-500 text-slate-950 shadow-md animate-pulse">
                            Segera Hadir
                        </span>
                    </div>

                    <!-- Secondary CTA -->
                    <a href="{{ route('login') }}"
                        class="px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white border border-slate-800 hover:border-emerald-500/50 font-headline font-semibold text-base flex items-center gap-2.5 transition-all duration-150 active:scale-95">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Login</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- HIGHLIGHT FITUR & ATURAN LAYANAN (3-COLUMN BENTO GRID) -->
        <section class="py-20 bg-slate-950 border-t border-slate-800/80" id="fitur">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-900 text-emerald-400 text-xs font-semibold border border-slate-800 mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>Aturan &amp; Layanan Sistem Bank Mini</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-headline font-bold text-white tracking-tight">
                        Standar Operasional Tabungan Siswa &amp; Loket Kasir Sekolah
                    </h2>
                    <p class="text-slate-400 text-base mt-2">
                        Ketentuan resmi pembukaan rekening tabungan siswa, pemindaian QR Code loket, dan otorisasi
                        transaksi tunai.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Bento Card 1: Aplikasi Mobile Saver -->
                    <div
                        class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col justify-between hover:border-emerald-500/40 transition-all duration-300 specular-top-edge shadow-xl">
                        <div>
                            <div
                                class="w-12 h-12 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-emerald-400 mb-6">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-headline font-bold text-white mb-2">Aplikasi Mobile Khusus Nasabah
                            </h3>
                            <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                                Aplikasi mobile mandiri khusus siswa penabung (<span
                                    class="text-emerald-400 font-mono">saver</span>) untuk memantau tabungan sekolah
                                secara transparan dan aman.
                            </p>

                            <ul class="space-y-3 mb-6">
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>Cek Saldo Mandiri (Read-Only):</strong> Akses cek saldo buku tabungan
                                        digital secara mandiri kapan saja tanpa antre.</span>
                                </li>
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>Riwayat Mutasi Transaksi:</strong> Catatan historis setoran dan
                                        penarikan yang telah divalidasi oleh petugas kasir Teller.</span>
                                </li>
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>QR Code Kartu Nasabah:</strong> Kode QR rekening digital siap dipindai
                                        oleh barcode scanner di loket kasir Teller.</span>
                                </li>
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>Otorisasi PIN 6-Digit:</strong> Proteksi keamanan akun nasabah dan
                                        fitur pemulihan token jika siswa lupa PIN.</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Mini App Preview Box -->
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="flex items-center justify-between mb-3 text-xs text-slate-400 font-medium">
                                <span>Saver Mobile App</span>
                                <span class="text-emerald-400 font-mono">Flutter Engine</span>
                            </div>
                            <button type="button" onclick="openDownloadModal()"
                                class="w-full py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-400 font-semibold text-xs border border-slate-700 text-center transition-colors cursor-pointer">
                                Unduh Berkas .APK
                            </button>
                        </div>
                    </div>

                    <!-- Bento Card 2: Alur Aktivasi Rekening Otomatis & Saldo Mengendap -->
                    <div
                        class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col justify-between hover:border-emerald-500/40 transition-all duration-300 specular-top-edge shadow-xl">
                        <div>
                            <div
                                class="w-12 h-12 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-emerald-400 mb-6">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-headline font-bold text-white mb-2">Aktivasi Otomatis &amp; Saldo
                                Mengendap</h3>
                            <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                                Calon penabung terdaftar (<span class="text-emerald-400 font-mono">member</span>)
                                otomatis aktif menjadi nasabah penabung (<span
                                    class="text-emerald-400 font-mono">saver</span>) saat pertama kali menyetor uang di
                                loket.
                            </p>

                            <!-- 3-Step Ladder -->
                            <div class="space-y-4 mb-6">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">
                                        1</div>
                                    <div>
                                        <div class="text-xs font-semibold text-white">Verifikasi Data Siswa (Member)
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">Siswa mendatangi loket Teller dan
                                            menyebutkan NISN terdaftar.</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">
                                        2</div>
                                    <div>
                                        <div class="text-xs font-semibold text-white">Setoran Awal Minimal Rp 10.000
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">Setoran tunai pertama langsung
                                            mengaktifkan status rekening menjadi <span
                                                class="text-emerald-400 font-mono">saver</span>.</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">
                                        3</div>
                                    <div>
                                        <div class="text-xs font-semibold text-white">Terbitkan PIN 6-Digit &amp; QR
                                            Card</div>
                                        <div class="text-xs text-slate-400 mt-0.5">Rekening langsung aktif seketika dan
                                            PIN 6-digit diserahkan kepada siswa.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Value Prop Highlight: Saldo Mengendap -->
                        <div
                            class="p-3.5 rounded-xl bg-emerald-500/5 border border-emerald-500/20 flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-xs text-slate-300">
                                <strong class="text-emerald-400">Aturan Saldo Mengendap:</strong> Saldo minimal
                                mengendap di rekening adalah Rp 10.000 yang tidak dapat ditarik agar rekening tetap
                                berstatus aktif.
                            </div>
                        </div>
                    </div>

                    <!-- Bento Card 3: Operasional Loket & Otorisasi Penarikan -->
                    <div
                        class="rounded-2xl bg-slate-900 border border-slate-800 p-6 flex flex-col justify-between hover:border-emerald-500/40 transition-all duration-300 specular-top-edge shadow-xl">
                        <div>
                            <div
                                class="w-12 h-12 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-emerald-400 mb-6">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-headline font-bold text-white mb-2">Operasional Loket &amp;
                                Otorisasi Penarikan</h3>
                            <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                                Sistem operasional loket sekolah dengan pemindai QR Code, validasi penarikan berbasis
                                PIN, dan rekonsiliasi kas harian.
                            </p>

                            <ul class="space-y-3 mb-6">
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>Pemindaian QR Code Cepat:</strong> Teller memindai QR Code nasabah
                                        secara instan tanpa input nomor rekening manual.</span>
                                </li>
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>Otorisasi PIN Khusus Penarikan:</strong> Penarikan tunai wajib
                                        memasukkan PIN 6-digit nasabah demi mencegah pencairan tanpa izin.</span>
                                </li>
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>Penutupan Kas Harian (Cash Closure):</strong> Rekonsiliasi fisik uang
                                        kas loket dengan catatan sistem setiap akhir sesi.</span>
                                </li>
                                <li class="flex items-start gap-2.5 text-xs text-slate-200">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span><strong>Jurnal Akuntansi Berpasangan:</strong> Setiap mutasi kas langsung
                                        tercatat dalam jurnal akuntansi double-entry otomatis.</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Card Rule Badge -->
                        <div
                            class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="text-xs text-slate-200 font-medium">Otorisasi PIN 6-Digit
                                    Terproteksi</span>
                            </div>
                            <span
                                class="text-[10px] text-emerald-400 font-mono font-bold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                TERVALIDASI
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ SECTION -->
        <section class="py-20 bg-[#090D16]" id="faq">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-900 text-emerald-400 text-xs font-semibold border border-slate-800 mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Pertanyaan Umum</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-headline font-bold text-white">
                        Informasi Operasional Bank Mini Sekolah
                    </h2>
                    <p class="text-slate-400 text-sm mt-2">
                        Panduan ringkas pembukaan rekening siswa, penarikan, keamanan PIN, dan jadwal loket.
                    </p>
                </div>

                <div class="space-y-3.5">
                    <!-- FAQ 1 -->
                    <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
                        <button type="button" onclick="toggleFaq('faq-1')"
                            class="w-full p-5 text-left flex items-center justify-between text-white hover:text-emerald-400 transition-colors cursor-pointer">
                            <span class="font-headline font-semibold text-sm sm:text-base">Bagaimana alur aktivasi
                                rekening tabungan siswa dari status member ke saver?</span>
                            <svg id="faq-1-icon" class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="faq-1"
                            class="hidden px-5 pb-5 text-sm text-slate-400 border-t border-slate-800/80 pt-3 leading-relaxed">
                            Siswa terdaftar (<span class="text-emerald-400 font-mono">member</span>) cukup mendatangi
                            loket Teller sekolah dan melakukan setoran tunai awal minimal <strong
                                class="text-emerald-400">Rp 10.000</strong>. Sistem akan secara otomatis mengaktifkan
                            status menjadi <span class="text-emerald-400 font-mono">saver</span> dan menerbitkan PIN
                            6-digit awal. Seluruh nominal setoran awal utuh masuk ke saldo tabungan siswa.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
                        <button type="button" onclick="toggleFaq('faq-2')"
                            class="w-full p-5 text-left flex items-center justify-between text-white hover:text-emerald-400 transition-colors cursor-pointer">
                            <span class="font-headline font-semibold text-sm sm:text-base">Berapa batas ketentuan saldo
                                minimal mengendap di rekening?</span>
                            <svg id="faq-2-icon" class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="faq-2"
                            class="hidden px-5 pb-5 text-sm text-slate-400 border-t border-slate-800/80 pt-3 leading-relaxed">
                            Setiap rekening tabungan memiliki batas <strong class="text-white">saldo minimal mengendap
                                sebesar Rp 10.000</strong>. Batas saldo ini tidak dapat ditarik saat penarikan tunai
                            untuk menjaga agar rekening siswa tetap berstatus aktif di sistem Bank Mini Sekolah.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
                        <button type="button" onclick="toggleFaq('faq-3')"
                            class="w-full p-5 text-left flex items-center justify-between text-white hover:text-emerald-400 transition-colors cursor-pointer">
                            <span class="font-headline font-semibold text-sm sm:text-base">Mengapa penarikan tunai di
                                loket memerlukan otorisasi PIN 6-digit?</span>
                            <svg id="faq-3-icon" class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="faq-3"
                            class="hidden px-5 pb-5 text-sm text-slate-400 border-t border-slate-800/80 pt-3 leading-relaxed">
                            Otorisasi <strong class="text-white">PIN 6-digit</strong> merupakan pengamanan wajib khusus
                            penarikan uang tunai di loket Teller untuk memastikan bahwa penarikan saldo hanya diproses
                            atas persetujuan sah siswa penabung pemilik rekening. Petugas loket tidak dapat mencairkan
                            dana tanpa otorisasi PIN tersebut.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
                        <button type="button" onclick="toggleFaq('faq-4')"
                            class="w-full p-5 text-left flex items-center justify-between text-white hover:text-emerald-400 transition-colors cursor-pointer">
                            <span class="font-headline font-semibold text-sm sm:text-base">Apakah ada potongan biaya
                                administrasi bulanan?</span>
                            <svg id="faq-4-icon" class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="faq-4"
                            class="hidden px-5 pb-5 text-sm text-slate-400 border-t border-slate-800/80 pt-3 leading-relaxed">
                            <strong class="text-white">100% Gratis Tanpa Biaya Administrasi</strong>. Bank Mini Sekolah
                            didirikan sebagai sarana edukasi literasi keuangan dan sarana praktikum kejuruan siswa
                            Akuntansi/Perbankan, sehingga tidak membebankan biaya pengelolaan buku tabungan kepada siswa
                            penabung.
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
                        <button type="button" onclick="toggleFaq('faq-5')"
                            class="w-full p-5 text-left flex items-center justify-between text-white hover:text-emerald-400 transition-colors cursor-pointer">
                            <span class="font-headline font-semibold text-sm sm:text-base">Bagaimana jika siswa lupa
                                kode PIN aplikasi atau kartu tabungan?</span>
                            <svg id="faq-5-icon" class="w-5 h-5 text-slate-400 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="faq-5"
                            class="hidden px-5 pb-5 text-sm text-slate-400 border-t border-slate-800/80 pt-3 leading-relaxed">
                            Siswa cukup membawa Kartu Pelajar atau menyebutkan NISN kepada petugas loket Teller sekolah.
                            Petugas loket Teller atau Administrator akan mencocokkan identitas siswa dan memproses
                            penerbitan token reset PIN sementara dengan cepat dan aman.
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-950 border-t border-slate-800/80 mt-auto">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="space-y-1.5 text-center md:text-left">
                <div
                    class="font-headline font-bold text-white flex items-center justify-center md:justify-start gap-2.5">
                    <div
                        class="w-6 h-6 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-center p-0.5 shadow-inner overflow-hidden">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Mini"
                            class="w-full h-full object-contain">
                    </div>
                    <span>Bank Mini Sekolah</span>
                </div>
                <p class="text-xs text-slate-400">
                    &copy; 2026 Bank Mini Sekolah. Platform Perbankan Edukasi &amp; Rekening Siswa
                    Terintegrasi.
                </p>
            </div>

            <div class="flex flex-wrap justify-center items-center gap-x-6 gap-y-2 text-xs text-slate-400 font-medium">
                <a href="#fitur" class="hover:text-emerald-400 transition-colors">Fitur Layanan</a>
                <a href="#faq" class="hover:text-emerald-400 transition-colors">FAQ</a>
                <a href="{{ route('login') }}"
                    class="text-emerald-400 hover:text-emerald-300 transition-colors font-semibold">Login</a>
            </div>
        </div>
    </footer>

    <!-- INTERACTIVE DOWNLOAD MODAL DIALOG -->
    <div id="downloadModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200 p-4"
        onclick="handleBackdropClick(event)">
        <div id="modalContainer"
            class="w-full max-w-md rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl transform scale-95 transition-transform duration-200 specular-top-edge">
            <!-- Modal Header -->
            <div class="flex items-start justify-between mb-4">
                <div
                    class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <!-- Flutter / Mobile Phone Icon -->
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M14.314 0L2.3 12 6.557 16.257l12.014-12.014h-4.257zM14.314 11.257L8.743 16.828l3.186 3.186 5.571-5.571h-3.186zM18.571 15.514L15.385 18.7l5.3 5.3h4.257l-6.371-6.371v-2.115z" />
                    </svg>
                </div>
                <button type="button" onclick="closeDownloadModal()"
                    class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="space-y-3 mb-6">
                <div class="flex items-center gap-2">
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        TAHAP FINALISASI
                    </span>
                    <span class="text-xs font-mono text-slate-400">Flutter Mobile Engine</span>
                </div>
                <h3 class="text-lg font-headline font-bold text-white">
                    Aplikasi Mobile (Nasabah) Sedang Tahap Final
                </h3>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Aplikasi Mobile Bank Mini khusus Nasabah sedang dalam tahap pengembangan akhir. Silakan
                    kunjungi loket Teller untuk aktivasi rekening atau informasi lebih lanjut.
                </p>

                <div
                    class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-400 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Layanan cek saldo &amp; transaksi tetap dapat dilayani secara penuh di Loket Teller
                        sekolah.</span>
                </div>
            </div>

            <!-- Modal Footer Button -->
            <div class="flex justify-end">
                <button type="button" onclick="closeDownloadModal()"
                    class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-headline font-bold text-sm transition-colors text-center cursor-pointer shadow-md shadow-emerald-500/20">
                    Oke, Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- INTERACTIVE JAVASCRIPT (ZERO DEPENDENCY) -->
    <script>
        // 1. Download Modal Functions
        function openDownloadModal() {
            const modal = document.getElementById("downloadModal");
            const container = document.getElementById("modalContainer");
            modal.classList.remove("opacity-0", "pointer-events-none");
            modal.classList.add("opacity-100", "pointer-events-auto");
            container.classList.remove("scale-95");
            container.classList.add("scale-100");
            document.body.style.overflow = "hidden";
        }

        function closeDownloadModal() {
            const modal = document.getElementById("downloadModal");
            const container = document.getElementById("modalContainer");
            modal.classList.add("opacity-0", "pointer-events-none");
            modal.classList.remove("opacity-100", "pointer-events-auto");
            container.classList.add("scale-95");
            container.classList.remove("scale-100");
            document.body.style.overflow = "";
        }

        function handleBackdropClick(event) {
            if (event.target === document.getElementById("downloadModal")) {
                closeDownloadModal();
            }
        }

        // Escape Key Listener
        window.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                closeDownloadModal();
            }
        });

        // 2. FAQ Accordion Toggle
        function toggleFaq(id) {
            const content = document.getElementById(id);
            const icon = document.getElementById(id + "-icon");
            if (content.classList.contains("hidden")) {
                content.classList.remove("hidden");
                icon.classList.add("rotate-180");
            } else {
                content.classList.add("hidden");
                icon.classList.remove("rotate-180");
            }
        }
    </script>
</body>

</html>