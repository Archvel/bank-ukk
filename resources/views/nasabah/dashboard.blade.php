<x-nasabah-layout title="Beranda & Kartu Rekening">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-6" x-data="nasabahDashboard('{{ $qrCodeToken ?? '' }}')">

        <!-- ======================================================== -->
        <!-- LIVE STATUS LOKET / TELLER BANK MINI                     -->
        <!-- ======================================================== -->
        <div class="rounded-3xl p-5 sm:p-6 border transition-all shadow-xs
            @if($tellerSessionStatus['is_open'] ?? false)
                bg-gradient-to-r from-emerald-50 via-teal-50/60 to-white border-emerald-300 text-emerald-950
            @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                bg-gradient-to-r from-amber-50 via-orange-50/50 to-white border-amber-300 text-amber-950
            @else
                bg-gradient-to-r from-slate-50 via-gray-50 to-white border-slate-300 text-slate-800
            @endif">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Left: Status Indicator & Info -->
                <div class="flex items-start sm:items-center gap-4">
                    <!-- Icon Box with Live Status Badge -->
                    <div class="w-13 h-13 rounded-2xl flex items-center justify-center shrink-0 shadow-sm
                        @if($tellerSessionStatus['is_open'] ?? false)
                            bg-emerald-600 text-white shadow-emerald-700/20
                        @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                            bg-amber-500 text-white shadow-amber-600/20
                        @else
                            bg-slate-600 text-white shadow-slate-600/20
                        @endif">
                        <span class="material-symbols-outlined text-2xl">
                            @if($tellerSessionStatus['is_open'] ?? false)
                                storefront
                            @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                                hourglass_top
                            @else
                                door_front
                            @endif
                        </span>
                    </div>

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Live Pulsing Badge -->
                            @if($tellerSessionStatus['is_open'] ?? false)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                    <span>LOKET BUKA (SESI AKTIF)</span>
                                </span>
                            @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    <span>LOKET BELUM MEMULAI SESI</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-slate-200 text-slate-800 border border-slate-300">
                                    <span class="h-2 w-2 rounded-full bg-slate-500"></span>
                                    <span>LOKET TUTUP</span>
                                </span>
                            @endif

                            <span class="text-xs font-bold text-slate-400 hidden sm:inline">•</span>
                            <span class="text-xs font-semibold text-slate-600 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-slate-400">schedule</span>
                                Jam Operasional: <strong class="text-slate-900 font-mono">{{ $tellerSessionStatus['operating_hours'] ?? '07:00 - 15:30 WIB' }}</strong>
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-700 leading-snug font-medium">
                            {{ $tellerSessionStatus['message'] ?? 'Status loket teller Bank Mini.' }}
                        </p>
                    </div>
                </div>

                <!-- Right: Active Teller Pills or Advice Note -->
                <div class="shrink-0 flex flex-wrap items-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-200/60">
                    @if($tellerSessionStatus['is_open'] ?? false)
                        <div class="bg-white/85 backdrop-blur-xs px-3.5 py-2 rounded-xl border border-emerald-200 shadow-2xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-base">badge</span>
                            <div class="text-left">
                                <span class="block text-[10px] uppercase font-bold text-slate-400 leading-tight">Teller Melayani</span>
                                <span class="block text-xs font-bold text-slate-900">{{ implode(', ', $tellerSessionStatus['active_tellers'] ?? []) ?: 'Petugas Piket' }}</span>
                            </div>
                        </div>
                    @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                        <div class="bg-white/85 backdrop-blur-xs px-3.5 py-2 rounded-xl border border-amber-200 shadow-2xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600 text-base">pending_actions</span>
                            <div class="text-left">
                                <span class="block text-[10px] uppercase font-bold text-slate-400 leading-tight">Status Sesi Loket</span>
                                <span class="block text-xs font-bold text-amber-900">Menunggu Kas Awal Teller</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-white/85 backdrop-blur-xs px-3.5 py-2 rounded-xl border border-slate-200 shadow-2xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-slate-500 text-base">lock_clock</span>
                            <div class="text-left">
                                <span class="block text-[10px] uppercase font-bold text-slate-400 leading-tight">Jadwal Loket</span>
                                <span class="block text-xs font-bold text-slate-800">Buka Esok Pukul 07:00 WIB</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        @if($saver)
            <!-- ======================================================== -->
            <!-- STATE 1: NASABAH AKTIF (MEMILIKI REKENING TABUNGAN)       -->
            <!-- ======================================================== -->

            <!-- 1. Top Section - Split 2/3 and 1/3 Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                <!-- Left: Hero Digital Card (col-span-2) -->
                <div class="lg:col-span-2 relative overflow-hidden rounded-3xl p-7 sm:p-8 text-white bg-gradient-to-br from-[#059669] via-[#047857] to-[#064e3b] shadow-xl shadow-emerald-950/20 flex flex-col justify-between min-h-[340px] border border-white/15">
                    <!-- Background decorative watermark shapes -->
                    <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
                    <div class="absolute -left-12 -bottom-12 w-64 h-64 rounded-full bg-emerald-400/10 pointer-events-none blur-xl"></div>
                    <div class="absolute right-10 bottom-6 opacity-10 pointer-events-none">
                        <span class="material-symbols-outlined text-[150px]">shield</span>
                    </div>

                    <!-- Top Row: Card Header & EMV Chip -->
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-xs">
                                <span class="material-symbols-outlined text-white text-2xl fill">account_balance</span>
                            </div>
                            <div>
                                <p class="font-bold text-sm tracking-wide text-white leading-none">BANK MINI SMKN 1</p>
                                <p class="text-[10px] text-emerald-200 font-bold tracking-wider uppercase mt-1">SMART FINTECH SISWA</p>
                            </div>
                        </div>

                        <!-- EMV Chip & Contactless Wave Badge -->
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-white/80 text-xl">contactless</span>
                            <!-- Golden EMV Chip Graphic -->
                            <div class="w-11 h-8 rounded-lg bg-gradient-to-br from-amber-300 via-amber-200 to-amber-500 p-1 shadow-sm border border-amber-600/30 flex flex-col justify-between shrink-0">
                                <div class="h-1.5 w-full border-b border-amber-600/40"></div>
                                <div class="h-2 w-4 border-r border-amber-600/40"></div>
                                <div class="h-1.5 w-full border-t border-amber-600/40"></div>
                            </div>
                            <span class="hidden sm:inline-block text-[11px] font-bold px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 tracking-wider">
                                DEBIT SISWA DIGITAL
                            </span>
                        </div>
                    </div>

                    <!-- Middle Area: Balance & Account Information -->
                    <div class="relative z-10 my-6">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-200 uppercase tracking-widest text-[11px] font-bold">TOTAL SALDO BERJALAN</span>
                            <span class="text-emerald-300" title="Saldo resmi tersinkronisasi sistem">
                                <span class="material-symbols-outlined text-sm align-middle fill">verified</span>
                            </span>
                        </div>
                        <div class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white my-1.5 tabular-nums">
                            Rp {{ number_format($balance, 0, ',', '.') }}
                        </div>
                        <div class="flex flex-wrap items-center gap-2.5 text-xs text-emerald-100 font-medium pt-1">
                            <div class="flex items-center gap-1.5 bg-black/25 backdrop-blur-md px-3 py-1 rounded-lg border border-white/15">
                                <span class="font-mono text-white font-bold tracking-wider">{{ $saver->account_number }}</span>
                                <button type="button" 
                                        @click="copyAccountNumber('{{ $saver->account_number }}')" 
                                        class="text-white/80 hover:text-white transition-transform active:scale-90" 
                                        title="Salin Nomor Rekening">
                                    <span class="material-symbols-outlined text-sm">content_copy</span>
                                </button>
                            </div>
                            <span class="text-white/60">•</span>
                            <span class="text-white font-bold text-sm">{{ $saver->user?->name ?? Auth::user()->name }}</span>
                            @if($member?->graduation_year)
                                <span class="text-white/60">•</span>
                                <span class="text-emerald-200">Angkatan {{ $member->graduation_year }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Bottom Sub-boxes Grid -->
                    <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <!-- a) Saldo Siap Ditarik -->
                        <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl p-4 flex flex-col justify-between transition-all hover:bg-white/20">
                            <div class="flex items-center justify-between text-xs text-emerald-100">
                                <span class="font-semibold">Saldo Siap Ditarik</span>
                                <span class="material-symbols-outlined text-base text-emerald-200 fill">check_circle</span>
                            </div>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-xl sm:text-2xl font-extrabold text-white tabular-nums">
                                    Rp {{ number_format($withdrawableBalance, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-emerald-200 font-medium">Bebas Ditarik</span>
                            </div>
                        </div>

                        <!-- b) Batas Saldo Mengendap Minimum -->
                        <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl p-4 flex flex-col justify-between transition-all hover:bg-white/20">
                            <div class="flex items-center justify-between text-xs text-emerald-100">
                                <span class="font-semibold">Batas Saldo Mengendap</span>
                                <span class="material-symbols-outlined text-base text-amber-300">lock</span>
                            </div>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-xl sm:text-2xl font-extrabold text-white tabular-nums">
                                    Rp {{ number_format($minMaintainingBalance, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-amber-200 font-medium">SOP Bank Mini</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: QR Code Loket Scanner Pass Card (col-span-1) -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col items-center justify-between text-center min-h-[340px] hover:shadow-md transition">
                    <!-- Top Badge -->
                    <div class="w-full flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 font-bold text-[11px] px-3 py-1 rounded-full uppercase tracking-wider">
                            <span class="material-symbols-outlined text-xs">qr_code_scanner</span>
                            <span>ID SCANNER TELLER</span>
                        </span>
                        @if($tellerSessionStatus['is_open'] ?? false)
                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                <span>Loket Buka</span>
                            </span>
                        @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                            <span class="inline-flex items-center gap-1 text-[10px] text-amber-800 font-bold bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>Belum Mulai</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 font-bold bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                                <span>Tutup</span>
                            </span>
                        @endif
                    </div>

                    <!-- QR Code Render Container -->
                    <div class="my-4 flex flex-col items-center">
                        <div class="p-3.5 bg-white rounded-2xl border-2 border-slate-100 shadow-inner flex items-center justify-center">
                            <div id="qrcode-container" class="w-32 h-32 flex items-center justify-center"></div>
                        </div>
                        <span class="mt-2 text-xs font-mono font-bold text-slate-800 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">
                            {{ $qrCodeToken }}
                        </span>
                    </div>

                    <!-- Subtext & Action -->
                    <div class="w-full space-y-3">
                        @if($tellerSessionStatus['is_open'] ?? false)
                            <p class="text-xs text-emerald-800 font-medium leading-relaxed bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-100 text-left">
                                <span class="material-symbols-outlined text-sm align-middle text-emerald-600 mr-1">check_circle</span>
                                Loket sedang aktif melayani. Tunjukkan QR code ini ke scanner loket Teller untuk setor atau tarik tunai instan.
                            </p>
                        @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                            <p class="text-xs text-amber-800 font-medium leading-relaxed bg-amber-50/70 p-2.5 rounded-xl border border-amber-200 text-left">
                                <span class="material-symbols-outlined text-sm align-middle text-amber-600 mr-1">hourglass_top</span>
                                Teller belum memulai sesi. Anda dapat menyiapkan QR code ini terlebih dahulu sebelum loket dibuka.
                            </p>
                        @else
                            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-left">
                                <span class="material-symbols-outlined text-sm align-middle text-slate-400 mr-1">info</span>
                                Sesi loket telah selesai hari ini. QR code ini tetap berlaku untuk transaksi pada jam operasional esok hari.
                            </p>
                        @endif
                        <button type="button" 
                                @click="showQrModal = true" 
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-[#0F172A] hover:bg-slate-800 text-white shadow-xs flex items-center justify-center gap-2 transition active:scale-98">
                            <span class="material-symbols-outlined text-[16px]">fullscreen</span>
                            <span>Perbesar QR Code</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. Bottom Section - 5 Mutasi Rekening Terakhir -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]">history</span>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">5 Mutasi Rekening Terakhir</h3>
                            <p class="text-[11px] text-slate-400">Ringkasan transaksi setor &amp; tarik terbaru di loket sekolah</p>
                        </div>
                    </div>

                    <a href="{{ route('nasabah.mutations.index') }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 transition">
                        <span>Lihat Semua Riwayat Buku Tabungan</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                <!-- Table Preview -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Tanggal &amp; Waktu</th>
                                <th class="py-3 px-4">Kode Slip</th>
                                <th class="py-3 px-4">Jenis Mutasi</th>
                                <th class="py-3 px-4 text-right">Nominal (Rp)</th>
                                <th class="py-3 px-4 text-right">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($recentMutations as $trx)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 font-mono text-slate-600">
                                        {{ $trx->created_at?->format('d M Y, H:i') }} WIB
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        {{ $trx->transaction_number }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($trx->type->value === 'deposit')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <span class="material-symbols-outlined text-[14px]">arrow_downward</span>
                                                <span>Setoran Tunai</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-800 border border-red-200">
                                                <span class="material-symbols-outlined text-[14px]">arrow_upward</span>
                                                <span>Penarikan Tunai</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold tabular-nums {{ $trx->type->value === 'deposit' ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ $trx->type->value === 'deposit' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-900 font-bold tabular-nums">
                                        Rp {{ number_format($trx->closing_balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">receipt_long</span>
                                        <p class="text-xs font-semibold text-slate-600">Belum Ada Transaksi</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Transaksi setor atau tarik tunai Anda di loket sekolah akan muncul di sini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL PERBESAR QR CODE -->
            <div x-show="showQrModal" 
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
                 @keydown.escape.window="showQrModal = false">
                <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-200"
                     @click.away="showQrModal = false">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="text-left">
                            <h4 class="font-bold text-sm text-slate-900">QR Code Identitas Penabung</h4>
                            <p class="text-[11px] text-slate-400">Tunjukkan ke scanner loket Teller</p>
                        </div>
                        <button type="button" @click="showQrModal = false" class="text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-2xl flex flex-col items-center justify-center border border-slate-200">
                        <div id="qrcode-modal-container" class="w-56 h-56 flex items-center justify-center bg-white p-4 rounded-xl shadow-xs"></div>
                        <p class="mt-4 font-mono font-bold text-sm text-slate-900">{{ $qrCodeToken }}</p>
                        <p class="text-xs font-bold text-emerald-700 mt-1">{{ $saver->user?->name ?? Auth::user()->name }}</p>
                        <span class="text-[10px] text-slate-400 font-mono">No. Rekening: {{ $saver->account_number }}</span>
                    </div>

                    <button type="button" 
                            @click="showQrModal = false" 
                            class="w-full py-2.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Tutup
                    </button>
                </div>
            </div>

        @else
            <!-- ======================================================== -->
            <!-- STATE 2: CALON PENABUNG (MEMBER BELUM AKTIF)              -->
            <!-- ======================================================== -->
            <div class="space-y-6">
                <!-- Friendly Inactive Member Warning Alert -->
                <div class="bg-gradient-to-r from-amber-50 to-emerald-50 border border-amber-300/80 p-6 rounded-3xl shadow-sm text-amber-950 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-100 border border-amber-300 text-amber-800 flex items-center justify-center shrink-0 shadow-xs">
                            <span class="material-symbols-outlined text-3xl">info</span>
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900 uppercase tracking-wider mb-1">
                                STATUS CALON PENABUNG (BELUM AKTIF)
                            </span>
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight">Selamat Datang di Bank Mini SMKN 1, {{ Auth::user()->name }}!</h3>
                            <p class="text-xs text-slate-600 mt-1 max-w-2xl leading-relaxed">
                                Akun Anda saat ini telah terdaftar sebagai Calon Nasabah Siswa. Untuk mengaktifkan <strong>Buku Tabungan Digital</strong> dan mendapatkan <strong>Nomor Rekening Resmi</strong>, silakan lakukan <strong>Setoran Awal minimal Rp 10.000</strong> di Meja Layanan Loket Teller.
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 w-full md:w-auto">
                        <div class="bg-white/80 backdrop-blur-md p-4 rounded-2xl border border-amber-200 text-center space-y-1 shadow-xs">
                            <span class="text-[11px] font-bold uppercase text-slate-500">Setoran Awal Minimal</span>
                            <div class="text-2xl font-black text-emerald-700 font-mono">Rp 10.000</div>
                            <span class="inline-block text-[10px] text-slate-400">Otomatis Aktif &amp; Terverifikasi</span>
                        </div>
                    </div>
                </div>

                <!-- 3 Langkah Panduan Aktivasi Rekening -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-xs space-y-6">
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900">3 Langkah Mudah Aktivasi Rekening Penabung:</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Proses aktivasi berlangsung instan tanpa biaya administrasi buku tabungan.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Langkah 1 -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="w-9 h-9 rounded-xl bg-[#059669] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                    1
                                </div>
                                @if($tellerSessionStatus['is_open'] ?? false)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                        <span>Loket Buka Sekarang</span>
                                    </span>
                                @elseif(($tellerSessionStatus['status_key'] ?? '') === 'not_started')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Belum Buka</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                        <span>Tutup</span>
                                    </span>
                                @endif
                            </div>
                            <h5 class="font-bold text-sm text-slate-900">Kunjungi Loket Teller</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Datang ke Loket 01 - 03 Bank Mini SMKN 1 pada jam operasional ({{ $tellerSessionStatus['operating_hours'] ?? '07:00 - 15:30 WIB' }}) dengan membawa kartu pelajar / menyebutkan nama Anda.
                            </p>
                        </div>

                        <!-- Langkah 2 -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="w-9 h-9 rounded-xl bg-[#059669] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                2
                            </div>
                            <h5 class="font-bold text-sm text-slate-900">Setoran Awal &amp; Tentukan PIN</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Serahkan uang setoran awal minimal Rp 10.000 kepada Teller dan tentukan 6-digit PIN keamanan rahasia Anda pada keypad loket.
                            </p>
                        </div>

                        <!-- Langkah 3 -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="w-9 h-9 rounded-xl bg-[#059669] text-white flex items-center justify-center font-bold text-sm shadow-xs">
                                3
                            </div>
                            <h5 class="font-bold text-sm text-slate-900">Rekening Langsung Aktif</h5>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Sistem secara otomatis menerbitkan Nomor Rekening, QR Code Pass, dan buku tabungan digital Anda aktif seketika.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    @push('scripts')
    <!-- QRCode.js Library (Client-side QR Code Generator - Scoped to Dashboard) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function nasabahDashboard(qrCodeToken = '') {
            return {
                showQrModal: false,

                init() {
                    if (!qrCodeToken) {
                        return;
                    }

                    // Generate QR Code on page load
                    this.$nextTick(() => {
                        const container = document.getElementById("qrcode-container");
                        const modalContainer = document.getElementById("qrcode-modal-container");

                        if (container && window.QRCode) {
                            container.innerHTML = "";
                            new QRCode(container, {
                                text: qrCodeToken,
                                width: 128,
                                height: 128,
                                colorDark: "#0F172A",
                                colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.H
                            });
                        }

                        if (modalContainer && window.QRCode) {
                            modalContainer.innerHTML = "";
                            new QRCode(modalContainer, {
                                text: qrCodeToken,
                                width: 220,
                                height: 220,
                                colorDark: "#0F172A",
                                colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.H
                            });
                        }
                    });
                },

                copyAccountNumber(accountNumber) {
                    navigator.clipboard.writeText(accountNumber).then(() => {
                        alert('Nomor Rekening (' + accountNumber + ') berhasil disalin ke clipboard!');
                    });
                }
            };
        }
    </script>
    @endpush
</x-nasabah-layout>
