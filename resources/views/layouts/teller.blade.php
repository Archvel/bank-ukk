<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F9FAFB]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Meja Layanan Loket' }} - {{ config('app.name', 'Bank Mini SMKN 1') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

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

<body class="h-full font-sans antialiased text-slate-800 bg-[#F9FAFB] select-none" x-data="{ currentDrawerBalance: {{ $drawerBalance ?? 0 }} }">
    <!-- Top Header Bar (Sederhana & Bersih) -->
    <header class="fixed top-0 left-0 right-0 h-[64px] bg-[#0F172A] border-b border-slate-800 z-50 text-white flex items-center justify-between px-6 no-print shadow-xs">
        <!-- Kiri: Nama Bank Mini Sekolah -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-xs">
                <span class="material-symbols-outlined text-[20px] fill">account_balance</span>
            </div>
            <span class="text-white font-bold text-base tracking-tight">Bank Mini Sekolah</span>
        </div>

        <!-- Tengah: 3 Link Navigasi Bersih -->
        <nav class="hidden md:flex items-center gap-1 bg-slate-800/60 p-1 rounded-xl border border-slate-700/50">
            <!-- 1. Meja Layanan -->
            <a href="{{ route('teller.counter') }}"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('teller.counter*') ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                <span class="material-symbols-outlined text-[17px]">point_of_sale</span>
                <span>Meja Layanan</span>
            </a>

            <!-- 2. Riwayat Hari Ini -->
            <a href="{{ route('teller.transactions.index') }}"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('teller.transactions.*') ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                <span class="material-symbols-outlined text-[17px]">history</span>
                <span>Riwayat Hari Ini</span>
            </a>

            <!-- 3. Penutupan Kas -->
            <a href="{{ route('teller.cash-closure.index') }}"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('teller.cash-closure.*') ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                <span class="material-symbols-outlined text-[17px]">payments</span>
                <span>Penutupan Kas</span>
            </a>
        </nav>

        <!-- Kanan: Kas Laci & Profil Petugas -->
        <div class="flex items-center gap-4">
            <!-- Kas Laci Ringkas -->
            <div class="flex items-center gap-2 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700/60 text-xs">
                <span class="text-slate-400">Kas Laci:</span>
                <span class="font-mono text-xs font-bold text-emerald-400 tabular-nums" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(currentDrawerBalance))">
                    Rp {{ number_format($drawerBalance ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <!-- Profil Petugas & Logout -->
            <div class="flex items-center gap-3 pl-2 border-l border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-emerald-700 border border-emerald-500/40 flex items-center justify-center text-white font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'TL', 0, 2)) }}
                    </div>
                    <span class="hidden sm:block text-xs font-medium text-white truncate max-w-[130px]">{{ auth()->user()->name ?? 'Petugas Teller' }}</span>
                </div>

                <!-- Tombol Logout -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        title="Keluar"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-800 transition"
                        onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Global Toast Alerts -->
    <div class="fixed top-[78px] right-6 z-50 space-y-2 max-w-md no-print" x-data="{ showSuccess: true, showError: true }">
        @if(session('success'))
        <div x-show="showSuccess"
            x-transition:enter="transition ease-out duration-300"
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
        <div x-show="showError"
            x-transition:enter="transition ease-out duration-300"
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

    @stack('scripts')
</body>

</html>