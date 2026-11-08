<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F9FAFB]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}Portal Nasabah Siswa - Bank Mini SMKN 1</title>

    <!-- Google Fonts: Figtree -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Google Material Symbols Outlined -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Tailwind / Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>

<body class="h-full font-sans antialiased text-slate-800 bg-[#F9FAFB] flex flex-col justify-between">
    <!-- Top Header Bar (Fixed Top Nav) -->
    <header class="fixed top-0 left-0 right-0 h-16 sm:h-[72px] bg-white/95 backdrop-blur-md border-b border-slate-200/90 z-40 text-slate-800 px-3 sm:px-6 no-print shadow-xs">
        <div class="max-w-6xl mx-auto h-full flex items-center justify-between gap-2 sm:gap-4">
            <!-- Left: Brand Logo & Title -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="{{ route('nasabah.dashboard') }}" class="flex items-center gap-2 sm:gap-3 group">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-md shadow-emerald-700/20 group-hover:scale-105 transition shrink-0">
                        <span class="material-symbols-outlined text-xl sm:text-2xl fill">account_balance</span>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-slate-900 font-extrabold text-xs sm:text-base tracking-tight leading-none group-hover:text-emerald-700 transition truncate">BANK MINI SMKN 1</h1>
                        <p class="text-[9px] sm:text-[10px] text-emerald-600 font-bold tracking-wider mt-0.5 truncate">PORTAL NASABAH SISWA</p>
                    </div>
                </a>
            </div>

            <!-- Center: 3 Navigation Tabs (Desktop & Tablet) -->
            <nav class="hidden md:flex items-center gap-1.5 bg-slate-100/80 p-1.5 rounded-full border border-slate-200/80">
                <!-- 1. Beranda & QR -->
                <a href="{{ route('nasabah.dashboard') }}" 
                   class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold transition-all {{ request()->routeIs('nasabah.dashboard*') ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
                    <span class="material-symbols-outlined text-[18px]">credit_card</span>
                    <span>Beranda &amp; QR</span>
                </a>

                <!-- 2. Buku Tabungan Digital -->
                <a href="{{ route('nasabah.mutations.index') }}" 
                   class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold transition-all {{ request()->routeIs('nasabah.mutations.*') ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
                    <span class="material-symbols-outlined text-[18px]">menu_book</span>
                    <span>Buku Tabungan Digital</span>
                </a>

                <!-- 3. Keamanan & PIN -->
                <a href="{{ route('nasabah.pin.index') }}" 
                   class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold transition-all {{ request()->routeIs('nasabah.pin.*') ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
                    <span class="material-symbols-outlined text-[18px]">shield</span>
                    <span>Keamanan &amp; PIN</span>
                </a>
            </nav>

            <!-- Right: Status Badge & Student Profile -->
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Loket Teller Session Status Badge -->
                @if($tellerSessionStatus['is_open'] ?? false)
                    <div class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-[11px] font-bold shadow-2xs"
                         title="Loket Teller sedang aktif melayani (Jam Operasional: {{ $tellerSessionStatus['operating_hours'] ?? '07:00 - 15:30 WIB' }})">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="whitespace-nowrap">LOKET BUKA</span>
                    </div>
                @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                    <div class="flex items-center gap-1.5 bg-amber-50 border border-amber-300 text-amber-800 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-[11px] font-bold"
                         title="Teller belum memulai sesi loket (Jam buka: 07:00 - 15:30 WIB)">
                        <span class="h-2 w-2 rounded-full bg-amber-500 shrink-0"></span>
                        <span class="whitespace-nowrap">BELUM BUKA</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 bg-slate-100 border border-slate-300 text-slate-600 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full text-[10px] sm:text-[11px] font-bold"
                         title="Sesi layanan loket teller telah selesai / tutup (Jam operasional: 07:00 - 15:30 WIB)">
                        <span class="h-2 w-2 rounded-full bg-slate-400 shrink-0"></span>
                        <span class="whitespace-nowrap">LOKET TUTUP</span>
                    </div>
                @endif

                <!-- Account Status Badge -->
                @if($hasActiveAccount)
                    <div class="hidden md:flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-1.5 rounded-full text-[11px] font-bold">
                        <span class="material-symbols-outlined text-sm text-emerald-600 fill">verified</span>
                        <span>REKENING AKTIF</span>
                    </div>
                @else
                    <div class="hidden md:flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-800 px-3 py-1.5 rounded-full text-[11px] font-bold">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        <span>CALON NASABAH</span>
                    </div>
                @endif

                <!-- Student Persona -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 pl-1.5 sm:pl-2 border-l border-slate-200">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-700 border border-emerald-500/40 flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-xs"
                         title="{{ Auth::user()->name ?? 'Nasabah' }}">
                        {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 2)) }}
                    </div>
                    <div class="hidden lg:block text-left">
                        <p class="text-xs font-bold text-slate-900 leading-tight truncate max-w-[130px]">{{ Auth::user()->name ?? 'Nasabah' }}</p>
                        <span class="text-[10px] text-slate-500 font-medium">Siswa Nasabah</span>
                    </div>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline ml-0.5">
                        @csrf
                        <button type="submit" 
                                title="Keluar dari portal nasabah"
                                class="p-1.5 sm:p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-slate-100 transition flex items-center justify-center"
                                onclick="return confirm('Apakah Anda yakin ingin keluar dari Portal Nasabah?')">
                            <span class="material-symbols-outlined text-[18px] sm:text-[20px]">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Global Toast Alerts -->
    <div class="fixed top-[72px] sm:top-[84px] right-4 sm:right-6 z-50 space-y-2 max-w-md no-print" x-data="{ showSuccess: true, showError: true }">
        @if(session('success'))
            <div x-show="showSuccess" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-lg flex items-center justify-between gap-3 text-xs font-semibold">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-[20px] fill">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="showSuccess = false" class="text-emerald-600 hover:text-emerald-900">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div x-show="showError" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 shadow-lg flex items-center justify-between gap-3 text-xs font-semibold">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600 text-[20px] fill">error</span>
                    <span>{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</span>
                </div>
                <button type="button" @click="showError = false" class="text-red-600 hover:text-red-900">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Main Content Container -->
    <main class="pt-[76px] sm:pt-[88px] pb-24 md:pb-12 flex-1">
        {{ $slot }}
    </main>

    <!-- Mobile Bottom Tab Navigation Bar (Fixed di bawah layar ponsel) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 py-2 px-4 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] flex items-center justify-around no-print">
        <a href="{{ route('nasabah.dashboard') }}" 
           class="flex flex-col items-center gap-1 text-[10px] font-bold transition-all py-1 px-3 rounded-xl {{ request()->routeIs('nasabah.dashboard*') ? 'text-emerald-700 bg-emerald-50/80' : 'text-slate-500 hover:text-slate-800' }}">
            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('nasabah.dashboard*') ? 'fill' : '' }}">credit_card</span>
            <span>Beranda</span>
        </a>
        <a href="{{ route('nasabah.mutations.index') }}" 
           class="flex flex-col items-center gap-1 text-[10px] font-bold transition-all py-1 px-3 rounded-xl {{ request()->routeIs('nasabah.mutations.*') ? 'text-emerald-700 bg-emerald-50/80' : 'text-slate-500 hover:text-slate-800' }}">
            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('nasabah.mutations.*') ? 'fill' : '' }}">menu_book</span>
            <span>Buku Tabungan</span>
        </a>
        <a href="{{ route('nasabah.pin.index') }}" 
           class="flex flex-col items-center gap-1 text-[10px] font-bold transition-all py-1 px-3 rounded-xl {{ request()->routeIs('nasabah.pin.*') ? 'text-emerald-700 bg-emerald-50/80' : 'text-slate-500 hover:text-slate-800' }}">
            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('nasabah.pin.*') ? 'fill' : '' }}">shield</span>
            <span>Keamanan</span>
        </a>
    </nav>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 mb-16 md:mb-0 text-center text-xs text-slate-500 no-print">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="font-bold text-slate-700">Bank Mini SMKN 1</span>
                <span>• Terintegrasi SIAKAD &amp; SOP Kejuruan Akuntansi</span>
            </div>
            <p>&copy; {{ date('Y') }} Bank Mini SMKN 1. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
