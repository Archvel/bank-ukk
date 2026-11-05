<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F9FAFB]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Meja Layanan Loket' }} - {{ config('app.name', 'Bank Mini') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Laser scanning animation for camera HUD */
        @keyframes laserScan {
            0% {
                top: 8%;
                opacity: 0.8;
            }

            50% {
                top: 88%;
                opacity: 1;
            }

            100% {
                top: 8%;
                opacity: 0.8;
            }
        }

        .laser-line {
            animation: laserScan 2.4s ease-in-out infinite;
        }

        /* High-fidelity POS print stylesheet */
        @media print {
            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }

            .thermal-slip {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                box-shadow: none !important;
                border: none !important;
                font-family: monospace !important;
            }
        }
    </style>
</head>

<body class="h-full font-sans antialiased text-slate-800 bg-[#F9FAFB] select-none"
    x-data="{ currentDrawerBalance: {{ $drawerBalance ?? 0 }}, currentOpeningBalance: {{ $openingBalance ?? 0 }}, openKasAwalModal: false }"
    @drawer-updated.window="currentDrawerBalance = $event.detail"
    @opening-cash-updated.window="currentOpeningBalance = $event.detail.openingBalance; if($event.detail.drawerBalance !== undefined) currentDrawerBalance = $event.detail.drawerBalance"
    @open-kas-awal-modal.window="openKasAwalModal = true">
    <!-- Top Header Bar (Fixed 70px POS Head Bar) -->
    <header
        class="fixed top-0 left-0 right-0 h-[70px] bg-[#0F172A] border-b border-slate-800 z-50 text-white flex items-center justify-between px-6 no-print shadow-md">
        <!-- Left: Brand Logo & Station Badge -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-md shadow-emerald-950/40">
                    <span class="material-symbols-outlined text-2xl fill">account_balance</span>
                </div>
                <div>
                    <h1 class="text-white font-extrabold text-base tracking-tight leading-none">Bank Mini</h1>
                    <p class="text-[11px] text-emerald-400 font-mono tracking-wider mt-0.5">SMK Negeri 1 Bantul</p>
                </div>
            </div>
        </div>

        <!-- Center: 3 Navigation Tabs -->
        <nav class="hidden md:flex items-center gap-1.5 bg-slate-800/80 p-1.5 rounded-xl border border-slate-700/60">
            <!-- 1. Meja Layanan Loket -->
            <a href="{{ route('teller.counter') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('teller.counter*') ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                <span class="material-symbols-outlined text-[18px]">point_of_sale</span>
                <span>Meja Layanan Loket</span>
            </a>

            <!-- 2. Riwayat Transaksi Hari Ini -->
            <a href="{{ route('teller.transactions.index') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('teller.transactions.*') ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                <span class="material-symbols-outlined text-[18px]">history</span>
                <span>Riwayat Transaksi Hari Ini</span>
            </a>

            <!-- 3. Penutupan Kas Harian -->
            <a href="{{ route('teller.cash-closure.index') }}"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('teller.cash-closure.*') ? 'bg-[#059669] text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-700/60' }}">
                <span class="material-symbols-outlined text-[18px]">payments</span>
                <span>Penutupan Kas Harian</span>
            </a>
        </nav>

        <!-- Right: Status Widgets & Teller Persona -->
        <div class="flex items-center gap-3">
            <!-- Kas Awal Session Widget / Button -->
            <button type="button" @click="openKasAwalModal = true"
                title="{{ $hasOpenedSession ? 'Kas awal sesi aktif. Klik untuk lihat atau perbarui' : 'Sesi loket baru: Klik untuk input uang kas awal laci' }}"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-semibold transition {{ $hasOpenedSession ? 'bg-slate-800/90 hover:bg-slate-700/80 border-slate-700/80 text-slate-200' : 'bg-amber-500/20 hover:bg-amber-500/30 border-amber-400/50 text-amber-300 ring-2 ring-amber-400/20 animate-pulse' }}">
                <span
                    class="material-symbols-outlined text-[19px] {{ $hasOpenedSession ? 'text-emerald-400' : 'text-amber-400' }}">savings</span>
                <div class="flex flex-col text-left">
                    <span
                        class="text-[9px] uppercase tracking-wider leading-none {{ $hasOpenedSession ? 'text-slate-400' : 'text-amber-300 font-bold' }}">
                        Kas Awal
                    </span>
                    <span class="font-mono text-xs font-bold tabular-nums"
                        x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(currentOpeningBalance))">
                        Rp {{ number_format($openingBalance ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </button>

            <!-- Real-Time Cash Drawer Balance Widget -->
            <div
                class="flex items-center gap-2.5 bg-emerald-600/90 text-white px-3.5 py-1.5 rounded-xl border border-emerald-400/30 shadow-inner">
                <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                <div class="flex flex-col text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-100 leading-none">Kas
                        Laci</span>
                    <span class="font-mono text-sm font-bold tabular-nums"
                        x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(currentDrawerBalance))">
                        Rp {{ number_format($drawerBalance ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Teller Profile & Logout -->
            <div class="flex items-center gap-3 pl-2 border-l border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-full bg-emerald-700 border border-emerald-500/40 flex items-center justify-center text-white font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'TL', 0, 2)) }}
                    </div>
                    <div class="hidden xl:block text-left">
                        <p class="text-xs font-bold text-white leading-tight truncate max-w-[120px]">
                            {{ auth()->user()->name ?? 'Piket Teller' }}
                        </p>
                        <span class="text-[10px] text-slate-400 font-medium">Piket Teller 1</span>
                    </div>
                </div>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Keluar dari sesi kasir"
                        class="p-2 rounded-xl text-slate-400 hover:text-red-400 hover:bg-slate-800 transition"
                        onclick="return confirm('Apakah Anda yakin ingin keluar dari sesi loket teller?')">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Global Toast Alerts -->
    <div class="fixed top-[78px] right-6 z-50 space-y-2 max-w-md no-print"
        x-data="{ showSuccess: true, showError: true }">
        @if(session('success'))
            <div x-show="showSuccess" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-lg flex items-center justify-between gap-3 text-xs font-medium">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="showSuccess = false" class="text-emerald-500 hover:text-emerald-800">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div x-show="showError" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 shadow-lg flex items-center justify-between gap-3 text-xs font-medium">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600 text-[20px]">error</span>
                    <span>{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</span>
                </div>
                <button type="button" @click="showError = false" class="text-red-500 hover:text-red-800">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Main Workspace Canvas -->
    <main class="pt-[70px] min-h-screen">
        {{ $slot }}
    </main>

    <!-- Global Kas Awal Modal Component -->
    @include('teller.partials.kas-awal-modal')

    @stack('scripts')
</body>

</html>