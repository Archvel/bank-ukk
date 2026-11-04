<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Portal Administrator' }} - {{ config('app.name', 'Bank Mini SMKN 1') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; color: black !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
        /* Custom subtle scrollbar for fixed dark sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.2);
            border-radius: 9999px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.4);
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-[#F9FAFB]" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm lg:hidden no-print" 
             @click="sidebarOpen = false" 
             x-cloak></div>

        <!-- Left Command Rail / Sidebar (Fixed 260px Slate #0F172A) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 lg:z-40 w-64 bg-[#0F172A] text-slate-300 transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col justify-between shrink-0 shadow-xl border-r border-slate-800 no-print">
            
            <div class="flex flex-col flex-1 overflow-y-auto sidebar-scroll">
                <!-- Brand Header -->
                <div class="p-5 border-b border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white shadow-md shadow-emerald-900/40 shrink-0">
                            <span class="material-symbols-outlined text-2xl">account_balance</span>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">BANK MINI SMKN 1</h1>
                            <p class="text-[11px] text-slate-400 tracking-wide font-medium">Sistem E-Teller & Akuntansi</p>
                        </div>
                    </div>

                    <!-- Branch Indicator Badge -->
                    <div class="mt-4 flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-slate-800/80 border border-slate-700/60 text-xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-slate-300 font-medium">Cabang Utama Kampus 1</span>
                    </div>
                </div>

                <!-- Navigation Section -->
                <div class="p-3">
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Navigasi Utama</p>
                    <nav class="space-y-1">
                        <!-- 1. Dashboard -->
                        <a href="{{ url('/admin/dashboard') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/dashboard') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/dashboard') ? 'text-white' : 'text-slate-400' }}">dashboard</span>
                            <span>Dashboard</span>
                        </a>

                        <!-- 2. Manajemen Pengguna -->
                        <a href="{{ url('/admin/users') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/users*') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/users*') ? 'text-white' : 'text-slate-400' }}">manage_accounts</span>
                            <span>Manajemen Pengguna</span>
                        </a>

                        <!-- 3. Data Calon Penabung (Members) -->
                        <a href="{{ url('/admin/members') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/members*') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/members*') ? 'text-white' : 'text-slate-400' }}">person_add</span>
                            <span>Data Calon Penabung</span>
                        </a>

                        <!-- 4. Master Savers / Rekening -->
                        <a href="{{ url('/admin/savers') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/savers*') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/savers*') ? 'text-white' : 'text-slate-400' }}">account_balance_wallet</span>
                            <span>Master Savers / Rekening</span>
                        </a>

                        <!-- 5. Verifikasi Kas Harian -->
                        <a href="{{ url('/admin/cash-closures') }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/cash-closures*') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/cash-closures*') ? 'text-white' : 'text-slate-400' }}">verified_user</span>
                                <span>Verifikasi Kas Harian</span>
                            </div>
                            @if($pendingClosureCount > 0)
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-amber-500 text-slate-900 animate-pulse">{{ $pendingClosureCount }}</span>
                            @endif
                        </a>

                        <!-- 6. Audit Jurnal Akuntansi -->
                        <a href="{{ url('/admin/audit-journals') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/audit-journals*') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/audit-journals*') ? 'text-white' : 'text-slate-400' }}">menu_book</span>
                            <span>Audit Jurnal Akuntansi</span>
                        </a>

                        <!-- 7. Laporan Transaksi & PDF -->
                        <a href="{{ url('/admin/reports') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/reports*') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/reports*') ? 'text-white' : 'text-slate-400' }}">description</span>
                            <span>Laporan Transaksi & PDF</span>
                        </a>

                        <!-- 8. Konfigurasi Sistem -->
                        <a href="{{ url('/admin/settings') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/settings*') ? 'bg-[#059669] text-white shadow-md shadow-emerald-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->is('admin/settings*') ? 'text-white' : 'text-slate-400' }}">settings</span>
                            <span>Konfigurasi Sistem</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Profile & Logout Footer -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-emerald-700/80 border border-emerald-500/30 flex items-center justify-center text-white font-bold text-sm shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Administrator Sistem' }}</p>
                            <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold rounded bg-emerald-950 text-emerald-400 border border-emerald-800/50">
                                Administrator
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" 
                                title="Keluar dari Sistem"
                                class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-800 transition">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Viewport Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64 print:pl-0">
            <!-- Top Header Bar -->
            <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 px-6 py-3.5 flex items-center justify-between shadow-xs no-print">
                <div class="flex items-center gap-4">
                    <!-- Mobile Hamburger Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            type="button" 
                            class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden focus:outline-hidden">
                        <span class="material-symbols-outlined">menu</span>
                    </button>

                    <!-- Breadcrumbs & Heading -->
                    <div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                            <span>Bank Mini SMKN 1</span>
                            <span>/</span>
                            <span class="text-slate-600">{{ $breadcrumb ?? 'Administrator' }}</span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900 leading-tight">{{ $header ?? $title ?? 'Portal Administrator' }}</h2>
                    </div>
                </div>

                <!-- Right Utility Bar -->
                <div class="flex items-center gap-4">
                    <!-- Real-Time Sync Indicator -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Real-Time Sync: Aktif</span>
                    </div>

                    <!-- Date Badge -->
                    <div class="hidden md:flex items-center gap-2 text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span>
                        <span>{{ now()->translatedFormat('d M Y') }}</span>
                    </div>

                    <!-- Notification Link to Cash Closures -->
                    @if($pendingClosureCount > 0)
                        <a href="{{ route('admin.cash-closures.index') }}" 
                           class="relative p-2 rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition"
                           title="{{ $pendingClosureCount }} Laporan Kas Perlu Verifikasi">
                            <span class="material-symbols-outlined text-[20px]">notifications_active</span>
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white">{{ $pendingClosureCount }}</span>
                        </a>
                    @endif
                </div>
            </header>

            <!-- Flash Alert Messages -->
            <div class="px-6 pt-5 no-print">
                @if (session('success'))
                    <div class="mb-4 flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-xs" role="alert">
                        <span class="material-symbols-outlined text-emerald-600 text-2xl">check_circle</span>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 shadow-xs" role="alert">
                        <span class="material-symbols-outlined text-red-600 text-2xl">error</span>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 shadow-xs" role="alert">
                        <div class="flex items-center gap-2 font-bold text-sm mb-1">
                            <span class="material-symbols-outlined text-red-600 text-xl">warning</span>
                            <span>Terdapat beberapa kesalahan input:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Main Content Slot -->
            <main class="flex-1 p-6">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
