<x-guest-layout :fullscreen="true">
    <div class="min-h-screen w-full flex flex-col lg:flex-row font-sans">
        <!-- PANEL KIRI: Supabase-Style Dark Slate Brand (55% di Layar Desktop) -->
        <div
            class="hidden lg:flex lg:w-[55%] min-h-screen bg-slate-900 relative flex-col justify-between p-8 sm:p-12 lg:p-16 border-r border-slate-800 overflow-hidden">
            <!-- Subtle Architectural Grid Pattern -->
            <div class="absolute inset-0 pointer-events-none opacity-60"
                style="background-size: 32px 32px; background-image: linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);">
            </div>
            <div
                class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-600/10 rounded-full blur-[140px] pointer-events-none">
            </div>

            <!-- Centered Wrapper for Left Panel Content -->
            <div class="relative z-10 w-full max-w-xl mx-auto flex flex-col justify-between flex-1 min-h-full">
                <!-- Top Header & Brand Identity -->
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-950/60 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 3l9 7H3l9-7z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-white font-bold text-lg tracking-tight block">E-Teller Bank Mini
                            Sekolah</span>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        Sistem Operasional Perbankan SMK
                    </span>
                </div>

                <!-- Value Proposition & Feature Cards (Centered Vertically) -->
                <div class="my-auto py-10">
                    <h1 class="text-white text-3xl xl:text-4xl font-bold tracking-tight leading-tight mb-4">
                        Keamanan Berlapis RBAC, Rekonsiliasi Kas Presisi, & Pencatatan Jurnal Double-Entry Otomatis
                    </h1>
                    <p class="text-slate-400 text-sm xl:text-base leading-relaxed mb-8">
                        Platform perbankan mini kejuruan terintegrasi untuk melatih tata kelola kas riil, otorisasi
                        transaksi loket teller, serta pembukuan akuntansi siswa secara akuntabel.
                    </p>

                    <div class="space-y-3">
                        <div
                            class="border border-slate-800 bg-slate-800/40 rounded-xl p-3.5 flex items-start gap-3.5 backdrop-blur-sm">
                            <div
                                class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0 text-emerald-400 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm mb-0.5">Role-Based Access Control</div>
                                <p class="text-slate-400 text-xs leading-normal">Akses terisolasi untuk Administrator
                                    Sistem, Petugas Loket Kas Teller, dan Savers Siswa.</p>
                            </div>
                        </div>

                        <div
                            class="border border-slate-800 bg-slate-800/40 rounded-xl p-3.5 flex items-start gap-3.5 backdrop-blur-sm">
                            <div
                                class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0 text-emerald-400 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm mb-0.5">Audit Trail Real-Time</div>
                                <p class="text-slate-400 text-xs leading-normal">Pencegahan selisih kas fisik laci vs
                                    saldo mutasi sistem dengan validasi tutup buku harian otomatis.</p>
                            </div>
                        </div>

                        <div
                            class="border border-slate-800 bg-slate-800/40 rounded-xl p-3.5 flex items-start gap-3.5 backdrop-blur-sm">
                            <div
                                class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0 text-emerald-400 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-white font-medium text-sm mb-0.5">Standar Akuntansi Perbankan</div>
                                <p class="text-slate-400 text-xs leading-normal">Integrasi debit/kredit double-entry
                                    instan ke buku besar serta cetak slip kasir thermal & PDF resmi.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- School Copyright Footer -->
                <div class="pt-6 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span>© Bank Mini Sekolah • Lab Perbankan Kejuruan</span>
                    <span class="text-slate-600">SMKN 1 Bantul</span>
                </div>
            </div>
        </div>

        <!-- PANEL KANAN: Form Area Bersih & Tajam (45% di Layar Desktop) -->
        <div
            class="w-full lg:w-[45%] min-h-screen bg-white flex flex-col justify-between p-6 sm:p-10 lg:p-14 overflow-y-auto">
            <!-- Centered Wrapper for Right Panel Content -->
            <div class="w-full max-w-md mx-auto flex flex-col justify-between flex-1 min-h-full">
                <!-- Header Operasional Atas -->
                <div class="flex items-center justify-between text-xs text-slate-500 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Layanan Loket: <strong class="text-slate-700 font-semibold">07.30 - 15.30
                                WIB</strong></span>
                    </div>
                    <span class="bg-slate-50 border border-slate-200 px-2 py-0.5 rounded text-slate-600 font-medium">
                        Bantuan: Ext. 104
                    </span>
                </div>

                <!-- Form Tengah (Centered Vertically & Horizontally) -->
                <div class="my-auto py-6">
                    <!-- Mobile Logo Header (Tampil pada layar tablet/mobile) -->
                    <div class="lg:hidden flex items-center gap-2.5 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 21h18M3 10h18M5 10v11M19 10v11M9 10v11M15 10v11M12 3l9 7H3l9-7z" />
                            </svg>
                        </div>
                        <span class="text-slate-900 font-bold text-base">Bank Mini Sekolah</span>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                            Masuk ke Portal Bank-Mini
                        </h2>
                        <p class="text-sm text-slate-500 mt-1.5">
                            Gunakan kredensial akun sesuai peran Anda (Administrator, Teller, atau Nasabah).
                        </p>
                    </div>

                    <!-- Session Status Alert -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Input 1: Email atau Username -->
                        <div>
                            <label for="email"
                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Email atau Username
                            </label>
                            <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="username" placeholder="nama@sekolah.sch.id atau username"
                                class="w-full rounded-lg border-slate-300 text-slate-900 placeholder:text-slate-400 py-2.5 px-3.5 text-sm focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition shadow-sm @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" />
                            @error('email')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Input 2: Kata Sandi with Alpine.js Toggle -->
                        <div x-data="{ show: false }">
                            <label for="password"
                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kata Sandi
                            </label>
                            <div class="relative">
                                <input id="password" :type="show ? 'text' : 'password'" type="password" name="password"
                                    required autocomplete="current-password" placeholder="••••••••••••"
                                    class="w-full rounded-lg border-slate-300 text-slate-900 placeholder:text-slate-400 py-2.5 pl-3.5 pr-10 text-sm focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 transition shadow-sm @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500 @enderror" />
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    aria-label="Toggle password visibility">
                                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" style="display: none;" class="w-4 h-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Baris Opsi: Remember Me -->
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input id="remember_me" type="checkbox" name="remember"
                                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-600 border-slate-300 transition cursor-pointer" />
                                <span class="text-xs text-slate-600 font-medium">Ingat sesi saya</span>
                            </label>
                            <span class="text-xs text-slate-400 font-medium">Otorisasi 2-Faktor Aktif</span>
                        </div>

                        <!-- Tombol Submit -->
                        <button type="submit"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-medium py-2.5 px-4 rounded-lg border border-emerald-700 text-sm transition shadow-sm cursor-pointer flex items-center justify-center gap-2 mt-2">
                            <span>Masuk ke Sistem</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>

                    <!-- Help & Security Alert Box -->
                    <div
                        class="mt-6 bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-600 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="leading-relaxed">
                            Lupa kata sandi atau PIN 6-digit? Hubungi petugas Loket Teller atau Administrator Bank Mini
                            untuk pemulihan akses akun Anda.
                        </p>
                    </div>

                    <!-- Footnote Aktivasi -->
                    <p class="text-center text-[11px] text-slate-400 mt-6">
                        Aktivasi rekening nasabah siswa baru diproses otomatis saat setoran awal di loket teller.
                    </p>
                </div>

                <!-- Footer Build Version -->
                <div class="pt-3 border-t border-slate-100 text-center text-[11px] text-slate-400">
                    Bank Mini • SMKN 1 Bantul
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>