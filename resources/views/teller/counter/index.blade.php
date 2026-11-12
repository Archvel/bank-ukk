<x-teller-layout title="Meja Layanan Loket" :drawer-balance="$drawerBalance">
    <div class="max-w-[1700px] mx-auto p-4 lg:p-6 space-y-4" 
         x-data="posCounter({{ (int) ($minInitialDeposit ?? 10000) }})"
         @keydown.window.f10.prevent="if(!showPinModal && !showReceiptModal) openPinModal()">

        <!-- Sesi Loket & Kas Awal Status Bar (Satu Baris Ringkas & Terpadu) -->
        @if(!($hasOpenedSession ?? false))
        <div class="px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 flex flex-wrap items-center justify-between gap-3 text-xs shadow-xs">
            <div class="flex items-center gap-2.5 flex-wrap min-w-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-900 border border-amber-400/40 font-bold text-[11px] shrink-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Kas Awal Belum Diinput
                </span>
                <span class="text-amber-900 leading-normal">
                    Sesi hari ini ({{ now('Asia/Jakarta')->translatedFormat('d F Y') }}) belum diinisialisasi. Jam layanan loket: <strong>07:00 – 15:30 WIB</strong>.
                    @if(!($isWithinOperatingHours ?? true))
                    <span class="text-amber-800 font-medium hidden md:inline">• Di luar jam operasional (disarankan rekonsiliasi & tutup kas).</span>
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="font-mono text-[11px] font-bold text-amber-800 px-2.5 py-1 rounded-lg bg-amber-100/90 border border-amber-200 shrink-0">
                    {{ now('Asia/Jakarta')->format('H:i') }} WIB
                </span>
                <button type="button" 
                        @click="$dispatch('open-kas-awal-modal')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-xs transition flex items-center justify-center gap-1.5 shrink-0">
                    <span class="material-symbols-outlined text-[16px]">add_circle</span>
                    <span>Input Kas Awal Sesi</span>
                </button>
            </div>
        </div>
        @else
        <!-- Sesi Aktif Info Strip -->
        <div class="px-4 py-2.5 rounded-xl bg-slate-900 text-white border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs shadow-xs">
            <div class="flex items-center gap-2.5 font-mono">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ ($isWithinOperatingHours ?? true) ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30' }} font-bold text-[11px] border">
                    <span class="w-2 h-2 rounded-full {{ ($isWithinOperatingHours ?? true) ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                    {{ ($isWithinOperatingHours ?? true) ? 'SESI AKTIF' : 'WAKTU SESI BERAKHIR' }} (07:00 - 15:30 WIB)
                </span>
                <span class="text-slate-400">Kas Awal: <strong class="text-white font-bold">Rp {{ number_format($openingBalance ?? 0, 0, ',', '.') }}</strong></span>
                <span class="text-slate-600">•</span>
                <span class="text-slate-400">Transaksi: <strong class="text-emerald-400 font-bold">{{ $todayTrxCount ?? 0 }} Trx</strong></span>
            </div>
            <div class="flex items-center gap-2">
                @if(!($isWithinOperatingHours ?? true))
                <a href="{{ route('teller.cash-closure.index') }}" 
                   class="text-xs font-semibold text-amber-300 hover:text-white flex items-center gap-1 transition px-2.5 py-1 rounded-lg hover:bg-slate-800 border border-amber-500/40">
                    <span class="material-symbols-outlined text-[16px] text-amber-400">payments</span>
                    <span>Tutup Kas Harian</span>
                </a>
                @endif
                <button type="button" 
                        @click="$dispatch('open-kas-awal-modal')"
                        class="text-xs font-semibold text-slate-300 hover:text-white flex items-center gap-1 transition px-2.5 py-1 rounded-lg hover:bg-slate-800 border border-slate-700/60">
                    <span class="material-symbols-outlined text-[16px] text-emerald-400">edit</span>
                    <span>Sesuaikan Kas Awal</span>
                </button>
            </div>
        </div>
        @endif

        <!-- Grid Split Screen (12 Columns: 5 Left / 7 Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ========================================== -->
            <!-- KOLOM KIRI (5 Cols - 40% Width): IDENTIFIKASI & LOOKUP NASABAH -->
            <!-- ========================================== -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- Card 1: Interactive QR Code Scanner Viewfinder Box (Webcam HUD) -->
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-md text-white relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :class="isCameraScanning ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'"></span>
                            <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">QR Scanner Kamera Loket 01</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold border transition-colors"
                              :class="isCameraScanning ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40 animate-pulse' : 'bg-slate-800 text-slate-400 border-slate-700'"
                              x-text="isCameraScanning ? 'CAM LIVE' : 'CAM STANDBY'">
                            CAM STANDBY
                        </span>
                    </div>

                    <!-- Video / Scanner Display Area (Ringkas & Proporsional saat Standby) -->
                    <div class="relative w-full bg-black rounded-xl overflow-hidden border border-slate-700 flex items-center justify-center transition-all duration-200"
                         :class="isCameraScanning ? 'aspect-[4/3] min-h-[280px] sm:min-h-[320px]' : 'h-24 sm:h-28 cursor-pointer group hover:border-emerald-500/50 hover:bg-slate-950/80'"
                         @click="if(!isCameraScanning) toggleCameraScanner()">
                        <div id="qr-reader" class="w-full h-full" x-show="isCameraScanning"></div>

                        <!-- Emulated HUD when camera is standby or loading -->
                        <div class="absolute inset-0 flex items-center justify-center p-3 pointer-events-none" x-show="!isCameraScanning">
                            <!-- Tactical Green Corner HUD Crosshairs -->
                            <div class="absolute top-2.5 left-2.5 w-3 h-3 border-t-2 border-l-2 border-emerald-400/60"></div>
                            <div class="absolute top-2.5 right-2.5 w-3 h-3 border-t-2 border-r-2 border-emerald-400/60"></div>
                            <div class="absolute bottom-2.5 left-2.5 w-3 h-3 border-b-2 border-l-2 border-emerald-400/60"></div>
                            <div class="absolute bottom-2.5 right-2.5 w-3 h-3 border-b-2 border-r-2 border-emerald-400/60"></div>

                            <div class="flex items-center gap-3.5 px-2 w-full">
                                <div class="w-10 h-10 rounded-xl bg-slate-800/90 border border-slate-700/80 flex items-center justify-center shrink-0 text-emerald-400 group-hover:bg-emerald-500/20 group-hover:border-emerald-500/40 transition">
                                    <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-bold text-slate-200 group-hover:text-white transition">Kamera Scanner Loket (Standby)</p>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-slate-800 text-slate-400 border border-slate-700">OFF</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Klik untuk menyalakan kamera QR atau gunakan kolom pencarian di bawah.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scanner Controls -->
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <button type="button" 
                                @click="toggleCameraScanner()"
                                :class="isCameraScanning ? 'bg-red-600 hover:bg-red-700 shadow-red-900/30' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-900/30'"
                                class="flex-1 py-2 rounded-xl text-xs font-bold text-white flex items-center justify-center gap-1.5 transition shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="isCameraScanning ? 'videocam_off' : 'videocam'"></span>
                            <span x-text="isCameraScanning ? 'Hentikan Kamera' : 'Buka Kamera QR'"></span>
                        </button>

                        <label class="cursor-pointer py-2 px-3 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 flex items-center gap-1.5 transition shrink-0">
                            <span class="material-symbols-outlined text-[16px]">file_upload</span>
                            <span>Upload QR</span>
                            <input type="file" accept="image/*" class="hidden" @change="handleFileUpload($event)" />
                        </label>
                    </div>
                </div>

                <!-- Card 2: Manual Search & Mode Switcher -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs space-y-3">
                    <!-- Lookup Mode Switcher Tabs -->
                    <div class="flex items-center p-1 bg-slate-100 rounded-xl">
                        <button type="button" 
                                @click="lookupMode = 'saver'; if(identifiedUser && identifiedUser.type !== 'saver') clearLookup();"
                                :class="lookupMode === 'saver' ? 'bg-[#059669] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="flex-1 py-1.5 rounded-lg text-xs transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">credit_card</span>
                            <span>Penabung Aktif (Savers)</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono {{ ($activeSaversCount ?? $activeSavers->count()) > 0 ? 'bg-emerald-700/30 text-emerald-100' : 'bg-slate-200 text-slate-600' }}">{{ number_format($activeSaversCount ?? $activeSavers->count(), 0, ',', '.') }}</span>
                        </button>
                        <button type="button" 
                                @click="lookupMode = 'member'; if(identifiedUser && identifiedUser.type !== 'member') clearLookup();"
                                :class="lookupMode === 'member' ? 'bg-[#059669] text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="flex-1 py-1.5 rounded-lg text-xs transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">person_add</span>
                            <span>Aktivasi Akun (Members)</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono {{ ($pendingMembersCount ?? $pendingMembers->count()) > 0 ? 'bg-amber-700/30 text-amber-100' : 'bg-slate-200 text-slate-600' }}">{{ number_format($pendingMembersCount ?? $pendingMembers->count(), 0, ',', '.') }}</span>
                        </button>
                    </div>

                    <!-- Search Input Bar -->
                    <form @submit.prevent="performLookup(searchQuery)" class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-slate-400 text-[18px]">search</span>
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Ketik No. Rekening (BM...), Nama Siswa, atau NIS..."
                               class="w-full pl-10 pr-24 py-2.5 text-xs rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                        <button type="submit" 
                                :disabled="isLoadingLookup || !searchQuery.trim()"
                                class="absolute right-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 transition flex items-center gap-1">
                            <span x-show="!isLoadingLookup">Cari</span>
                            <span x-show="isLoadingLookup" class="material-symbols-outlined text-[14px] animate-spin">progress_activity</span>
                            <span class="text-[10px] text-slate-400 font-mono">[Enter]</span>
                        </button>
                    </form>
                </div>

                <!-- Card 3: Identified Customer Profile Result Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs transition">
                    <!-- State: Initial Empty / Directory Quick Selector -->
                    <template x-if="!identifiedUser">
                        <div class="space-y-3.5">
                            <!-- Header Info -->
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-500 text-lg">view_list</span>
                                    <span class="text-xs font-bold text-slate-800" x-text="lookupMode === 'member' ? 'Daftar Calon Penabung (Belum Aktif)' : 'Daftar Penabung Aktif (Savers)'"></span>
                                </div>
                                <span class="text-[10px] font-mono text-slate-400">Klik siswa untuk memilih:</span>
                            </div>

                            <!-- List 1: Calon Penabung Belum Aktif (Members Inactive) -->
                            <div x-show="lookupMode === 'member'" class="space-y-2 max-h-[360px] overflow-y-auto pr-1">
                                @forelse($pendingMembers as $m)
                                    <div class="p-3 rounded-xl border border-amber-200/80 bg-amber-50/50 hover:bg-amber-50 hover:border-amber-300 transition flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-lg bg-amber-200 text-amber-900 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($m->student_name, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-slate-900 truncate">{{ $m->student_name }}</span>
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-200 text-amber-900 shrink-0">BELUM AKTIF</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 font-mono">
                                                    Lulus: {{ $m->graduation_year ?? '-' }} • User: {{ $m->user?->username ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                @click="performLookup('{{ $m->student_name }}')"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-2xs transition shrink-0 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">how_to_reg</span>
                                            <span>Aktivasi</span>
                                        </button>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-400">
                                        <span class="material-symbols-outlined text-2xl mb-1 text-slate-300">check_circle</span>
                                        <p class="text-xs font-semibold text-slate-600">Semua Siswa Telah Teraktivasi</p>
                                        <p class="text-[10px] text-slate-400">Tidak ada calon penabung yang pending saat ini.</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- List 2: Penabung Aktif (Savers) -->
                            <div x-show="lookupMode === 'saver'" class="space-y-2 max-h-[360px] overflow-y-auto pr-1">
                                @forelse($activeSavers as $s)
                                    <div class="p-3 rounded-xl border border-slate-200 hover:border-emerald-300 bg-slate-50/60 hover:bg-emerald-50/30 transition flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($s->user?->name ?? $s->member?->student_name ?? 'S', 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-slate-900 truncate">{{ $s->user?->name ?? $s->member?->student_name ?? 'Nasabah' }}</span>
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 shrink-0">AKTIF</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 font-mono">
                                                    {{ $s->account_number }} • Saldo: <strong class="text-emerald-700">Rp {{ number_format($s->balance, 0, ',', '.') }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                @click="performLookup('{{ $s->account_number }}')"
                                                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-slate-900 hover:bg-emerald-700 text-white shadow-2xs transition shrink-0 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                            <span>Pilih</span>
                                        </button>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-400">
                                        <span class="material-symbols-outlined text-2xl mb-1 text-slate-300">group_off</span>
                                        <p class="text-xs font-semibold text-slate-600">Belum Ada Penabung Aktif</p>
                                        <p class="text-[10px] text-slate-400">Aktivasi akun calon penabung pada tab di atas.</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Small hint footer -->
                            <p class="text-[10px] text-slate-400 text-center pt-1 border-t border-slate-100">
                                Anda juga dapat mencari dengan scanner kamera QR atau mengetik nama/nomor rekening pada form di atas.
                            </p>
                        </div>
                    </template>

                    <!-- State: Found Active Saver -->
                    <template x-if="identifiedUser && identifiedUser.type === 'saver'">
                        <div class="space-y-4">
                            <!-- Profile Header -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold text-base shadow-sm shrink-0"
                                         x-text="identifiedUser.student_name.substring(0, 2).toUpperCase()">
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-extrabold text-slate-900" x-text="identifiedUser.student_name"></h3>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">AKTIF</span>
                                        </div>
                                        <p class="text-xs text-slate-500">
                                            Tahun Lulus: <span class="font-semibold text-slate-700" x-text="identifiedUser.graduation_year"></span>
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="clearLookup()" class="text-slate-400 hover:text-slate-700" title="Ganti Nasabah">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>

                            <!-- Metadata Grid -->
                            <div class="grid grid-cols-2 gap-2 text-xs p-3 rounded-xl bg-slate-50 border border-slate-100 font-mono">
                                <div>
                                    <span class="text-slate-400 text-[10px] uppercase block">No. Rekening</span>
                                    <span class="font-bold text-slate-900" x-text="identifiedUser.account_number"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[10px] uppercase block">Token QR</span>
                                    <span class="font-bold text-emerald-700" x-text="identifiedUser.qr_code_token"></span>
                                </div>
                            </div>

                            <!-- Balance & Safety Limit Showcase -->
                            <div class="p-4 rounded-xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-sm space-y-2">
                                <div class="flex items-center justify-between text-xs text-slate-300">
                                    <span>Saldo Rekening Saat Ini</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">TERVERIFIKASI</span>
                                </div>
                                <div class="text-2xl font-extrabold font-mono text-emerald-400 tabular-nums" x-text="identifiedUser.formatted_balance">
                                </div>
                                <div class="pt-2 border-t border-slate-700/60 flex items-center justify-between text-xs">
                                    <span class="text-slate-400">Maksimal Bisa Ditarik:</span>
                                    <span class="font-mono font-bold text-white tabular-nums" x-text="identifiedUser.formatted_max_withdrawable"></span>
                                </div>
                            </div>

                            <!-- Alert Ketentuan Saldo Mengendap -->
                            <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] flex items-start gap-2">
                                <span class="material-symbols-outlined text-[16px] text-amber-600 mt-0.5 shrink-0">info</span>
                                <span>Ketentuan: Saldo minimal mengendap wajib <strong>Rp 10.000</strong> untuk pemeliharaan buku tabungan.</span>
                            </div>
                        </div>
                    </template>

                    <!-- State: Found Inactive Candidate Member (Siap Aktivasi) -->
                    <template x-if="identifiedUser && identifiedUser.type === 'member'">
                        <div class="space-y-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-amber-500 flex items-center justify-center text-white font-bold text-base shadow-sm shrink-0"
                                         x-text="identifiedUser.student_name.substring(0, 2).toUpperCase()">
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-extrabold text-slate-900" x-text="identifiedUser.student_name"></h3>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">BELUM AKTIF</span>
                                        </div>
                                        <p class="text-xs text-slate-500">
                                            Tahun Lulus: <span class="font-semibold text-slate-700" x-text="identifiedUser.graduation_year"></span>
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="clearLookup()" class="text-slate-400 hover:text-slate-700" title="Ganti Nasabah">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>

                            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-xs space-y-1.5">
                                <p class="font-bold flex items-center gap-1.5 text-amber-900">
                                    <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                                    <span>Aktivasi Rekening Perdana</span>
                                </p>
                                <p class="text-[11px] leading-relaxed text-amber-800">
                                    Siswa terdaftar sebagai calon penabung. Lakukan <strong>Setoran Awal minimal Rp 10.000</strong> dan buat 6-digit PIN keamanan pada form di samping untuk otomatis menerbitkan buku tabungan.
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- KOLOM KANAN (7 Cols - 60% Width): FORM EKSEKUSI TRANSAKSI -->
            <!-- ========================================== -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
                    
                    <!-- 1. Transaction Type Tab Switcher -->
                    <div class="grid grid-cols-2 gap-3 p-1.5 bg-slate-100 rounded-2xl">
                        <!-- Tab 1: Setoran Tunai -->
                        <button type="button"
                                @click="transactionType = 'deposit'"
                                :class="transactionType === 'deposit' ? 'bg-[#059669] text-white shadow-sm font-bold' : 'text-slate-700 hover:bg-slate-200/70 font-semibold'"
                                class="py-3 rounded-xl text-sm flex items-center justify-center gap-2 transition">
                            <span class="material-symbols-outlined text-[20px]">arrow_downward</span>
                            <span>Setoran Tunai (Uang Masuk)</span>
                        </button>

                        <!-- Tab 2: Penarikan Tunai -->
                        <button type="button"
                                @click="if(!isMemberMode) transactionType = 'withdrawal'"
                                :disabled="isMemberMode"
                                :class="[
                                    transactionType === 'withdrawal' ? 'bg-[#DC2626] text-white shadow-sm font-bold' : 'text-slate-700 hover:bg-slate-200/70 font-semibold',
                                    isMemberMode ? 'opacity-40 cursor-not-allowed' : ''
                                ]"
                                class="py-3 rounded-xl text-sm flex items-center justify-center gap-2 transition">
                            <span class="material-symbols-outlined text-[20px]">arrow_upward</span>
                            <span>Penarikan Tunai (Uang Keluar)</span>
                        </button>
                    </div>

                    <!-- Notice if Member Mode locks Withdrawal -->
                    <div x-show="isMemberMode" class="text-xs text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">lock</span>
                        <span>Tab Penarikan Tunai dinonaktifkan karena calon penabung belum memiliki rekening aktif.</span>
                    </div>

                    <!-- 2. Large Currency Input Field -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Nominal Transaksi (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-xl font-bold font-mono text-slate-400 select-none">Rp</span>
                            <input type="number" 
                                   x-model.number="amount"
                                   min="1000"
                                   step="1000"
                                   placeholder="0"
                                   class="w-full pl-14 pr-4 py-4 text-3xl font-extrabold font-mono text-slate-900 rounded-2xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 tabular-nums" />
                        </div>

                        <!-- Quick Nominal Chips -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <button type="button" @click="addAmount(10000)" class="px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition">+Rp 10.000</button>
                            <button type="button" @click="addAmount(20000)" class="px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition">+Rp 20.000</button>
                            <button type="button" @click="addAmount(50000)" class="px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition">+Rp 50.000</button>
                            <button type="button" @click="addAmount(100000)" class="px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition">+Rp 100.000</button>
                            <button type="button" @click="addAmount(200000)" class="px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition">+Rp 200.000</button>
                            <button type="button" @click="amount = 0" class="px-3 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition">Reset [C]</button>
                        </div>
                    </div>

                    <!-- 3. Real-Time Balance Simulation Flow -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            Simulasi Saldo Buku Tabungan Real-Time
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
                            <!-- Saldo Awal -->
                            <div class="p-3 bg-white rounded-xl border border-slate-200">
                                <span class="text-[10px] text-slate-400 block font-medium">Saldo Awal</span>
                                <span class="text-sm font-bold font-mono text-slate-800 tabular-nums" 
                                      x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(currentSaverBalance))">
                                </span>
                            </div>

                            <!-- Nominal Transaksi -->
                            <div class="p-3 bg-white rounded-xl border"
                                 :class="transactionType === 'deposit' ? 'border-emerald-200 text-emerald-700' : 'border-red-200 text-red-700'">
                                <span class="text-[10px] block font-medium" x-text="transactionType === 'deposit' ? '(+) Setoran' : '(-) Penarikan'"></span>
                                <span class="text-sm font-bold font-mono tabular-nums" 
                                      x-text="(transactionType === 'deposit' ? '+' : '-') + ' Rp ' + (new Intl.NumberFormat('id-ID').format(amount || 0))">
                                </span>
                            </div>

                            <!-- Estimasi Saldo Akhir -->
                            <div class="p-3 rounded-xl shadow-xs"
                                 :class="isBalanceValid ? 'bg-[#059669] text-white' : 'bg-red-600 text-white'">
                                <span class="text-[10px] block font-medium opacity-80">(=) Estimasi Saldo Akhir</span>
                                <span class="text-sm font-extrabold font-mono tabular-nums" 
                                      x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(estimatedBalance))">
                                </span>
                            </div>
                        </div>

                        <!-- Error Message if Balance Rule Violated -->
                        <div x-show="!isBalanceValid" class="p-2.5 rounded-xl bg-red-100 text-red-800 text-xs font-medium flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">warning</span>
                            <span x-text="balanceValidationMessage"></span>
                        </div>
                    </div>

                    <!-- 4. Mandatory PIN input when activating new Member -->
                    <div x-show="isMemberMode" class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-2">
                        <label class="block text-xs font-bold text-amber-950">
                            Buat PIN Keamanan 6-Digit Siswa <span class="text-red-500">*</span>
                        </label>
                        <p class="text-[11px] text-amber-800">PIN ini akan digunakan oleh siswa untuk otorisasi setiap penarikan tunai.</p>
                        <input type="password" 
                               maxlength="6" 
                               pattern="[0-9]*" 
                               inputmode="numeric"
                               x-model="activationPin"
                               placeholder="6 Digit Angka (Contoh: 123456)" 
                               class="w-full text-sm font-mono tracking-widest text-center rounded-xl border-amber-300 focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>

                    <!-- 5. Transaction Notes Field -->
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Catatan / Berita Transaksi (Opsional)</label>
                        <input type="text" 
                               x-model="notes" 
                               placeholder="Contoh: Setoran tabungan mingguan siswa / Uang saku..."
                               class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>

                    <!-- 6. Mandatory SOP Checklist -->
                    <div class="p-3.5 rounded-xl bg-emerald-50/80 border border-emerald-200">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" 
                                   x-model="isSopVerified"
                                   class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-emerald-300 w-4 h-4" />
                            <span class="text-xs text-emerald-950 font-medium leading-relaxed">
                                <strong>SOP Validasi Teller:</strong> Slip transaksi telah diisi &amp; ditandatangani nasabah, fisik uang tunai telah dihitung bersama nasabah sesuai nominal yang tertera.
                            </span>
                        </label>
                    </div>

                    <!-- 7. Primary Action Button -->
                    <div>
                        <!-- Button Deposit Mode -->
                        <button type="button" 
                                x-show="transactionType === 'deposit'"
                                @click="openPinModal()"
                                :disabled="!canSubmitDeposit"
                                class="w-full py-4 rounded-xl text-base font-extrabold bg-[#059669] hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white shadow-md shadow-emerald-950/20 transition flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[22px]">lock</span>
                            <span x-text="isMemberMode ? 'Lanjut Otorisasi Buat PIN & Setor Awal [F10]' : 'Lanjut Otorisasi PIN 6-Digit Nasabah [F10]'"></span>
                        </button>

                        <!-- Button Withdrawal Mode (Triggers PIN modal) -->
                        <button type="button" 
                                x-show="transactionType === 'withdrawal'"
                                @click="openPinModal()"
                                :disabled="!canSubmitWithdrawal"
                                class="w-full py-4 rounded-xl text-base font-extrabold bg-[#DC2626] hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-white shadow-md shadow-red-950/20 transition flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[22px]">lock</span>
                            <span>Lanjut Otorisasi PIN 6-Digit Nasabah [F10]</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: OTORISASI PIN 6-DIGIT NASABAH     -->
        <!-- ========================================== -->
        <div x-show="showPinModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm no-print"
             @keydown.window="if(showPinModal) handlePinKeydown($event)">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-200"
                 @click.away="showPinModal = false">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto"
                     :class="transactionType === 'deposit' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600'">
                    <span class="material-symbols-outlined text-2xl">lock</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" 
                        x-text="isMemberMode ? 'Buat PIN 6-Digit Rekening Baru' : (transactionType === 'deposit' ? 'Otorisasi PIN Setoran Nasabah' : 'Otorisasi PIN Penarikan Nasabah')">
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        <span x-show="isMemberMode">Siswa wajib menentukan 6 digit PIN untuk mengaktifkan rekening dan keamanan transaksi selanjutnya.</span>
                        <span x-show="!isMemberMode && transactionType === 'deposit'">
                            Siswa wajib memasukkan 6 digit PIN untuk otorisasi setoran sebesar 
                            <strong class="text-emerald-700 font-mono" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(amount || 0))"></strong>
                        </span>
                        <span x-show="!isMemberMode && transactionType === 'withdrawal'">
                            Siswa wajib memasukkan 6 digit PIN untuk otorisasi penarikan sebesar 
                            <strong class="text-red-600 font-mono" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(amount || 0))"></strong>
                        </span>
                    </p>
                </div>

                <!-- Auto-Focused Keyboard Input with Eye Toggle -->
                <div class="relative max-w-[260px] mx-auto pt-1">
                    <input :type="showPinText ? 'text' : 'password'"
                           x-ref="pinInput"
                           x-model="transactionPin"
                           maxlength="6"
                           inputmode="numeric"
                           pattern="[0-9]*"
                           autocomplete="off"
                           autofocus
                           @input="transactionPin = transactionPin.replace(/\D/g, '').slice(0, 6)"
                           @keydown.enter.prevent="if(transactionPin.length === 6 && !isSubmittingTransaction) submitTransactionWithPin()"
                           @keydown.escape.prevent="showPinModal = false"
                           placeholder="••••••"
                           :class="transactionType === 'deposit' ? 'border-emerald-300 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15' : 'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-500/15'"
                           class="w-full py-2.5 pl-8 pr-10 text-center text-3xl font-mono tracking-[0.4em] font-extrabold rounded-2xl border-2 bg-slate-50 focus:bg-white text-slate-900 transition shadow-inner outline-hidden" />
                    
                    <!-- Eye Toggle Button -->
                    <button type="button" 
                            @click="showPinText = !showPinText; $nextTick(() => $refs.pinInput?.focus())"
                            tabindex="-1"
                            title="Tampilkan / Sembunyikan PIN"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition p-1">
                        <span class="material-symbols-outlined text-[20px]" x-text="showPinText ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>

                <!-- 6 Masked PIN Indicator Dots (Click to Focus) -->
                <div class="flex items-center justify-center gap-2 py-1 cursor-pointer" @click="$refs.pinInput?.focus()">
                    <template x-for="i in 6" :key="i">
                        <div class="w-3.5 h-3.5 rounded-full border-2 transition-all duration-150"
                             :class="[
                                transactionPin.length >= i 
                                    ? (transactionType === 'deposit' ? 'bg-emerald-600 border-emerald-600 scale-110' : 'bg-red-600 border-red-600 scale-110') 
                                    : 'bg-slate-100 border-slate-300'
                             ]">
                        </div>
                    </template>
                </div>

                <p class="text-[11px] text-slate-500 flex items-center justify-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-[15px] text-slate-400">keyboard</span>
                    <span>Ketik langsung di keyboard atau gunakan keypad di bawah</span>
                </p>

                <!-- Numpad 0-9 & Control Buttons -->
                <div class="grid grid-cols-3 gap-2 pt-2">
                    <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                        <button type="button" @click="pressPinDigit(n)" 
                                class="py-3 text-lg font-extrabold font-mono rounded-xl bg-slate-50 hover:bg-slate-200 text-slate-800 transition active:scale-95">
                            <span x-text="n"></span>
                        </button>
                    </template>
                    <button type="button" @click="clearPinDigit()" 
                            class="py-3 text-xs font-bold rounded-xl bg-slate-100 hover:bg-red-50 text-red-600 transition">
                        Clear
                    </button>
                    <button type="button" @click="pressPinDigit(0)" 
                            class="py-3 text-lg font-extrabold font-mono rounded-xl bg-slate-50 hover:bg-slate-200 text-slate-800 transition active:scale-95">
                        0
                    </button>
                    <button type="button" @click="deletePinDigit()" 
                            class="py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                        <span class="material-symbols-outlined text-[20px]">backspace</span>
                    </button>
                </div>

                <!-- Action Button in PIN Modal -->
                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="showPinModal = false" class="w-1/3 py-2.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700">
                        Batal [Esc]
                    </button>
                    <button type="button" 
                            @click="submitTransactionWithPin()"
                            :disabled="transactionPin.length !== 6 || isSubmittingTransaction"
                            :class="transactionType === 'deposit' ? 'bg-[#059669] hover:bg-emerald-700 shadow-emerald-950/20' : 'bg-[#DC2626] hover:bg-red-700 shadow-red-950/20'"
                            class="w-2/3 py-2.5 rounded-xl text-xs font-bold disabled:opacity-40 text-white shadow-md flex items-center justify-center gap-1.5 transition">
                        <span x-show="!isSubmittingTransaction" x-text="transactionType === 'deposit' ? (isMemberMode ? 'Verifikasi & Aktifkan' : 'Verifikasi & Simpan Setoran') : 'Verifikasi & Cairkan'"></span>
                        <span x-show="isSubmittingTransaction" class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: CETAK STRUK BUKTI TRANSAKSI (80MM THERMAL SLIP) -->
        <!-- ========================================== -->
        <div x-show="showReceiptModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-200"
                 @click.away="showReceiptModal = false">
                
                <!-- Printable Thermal Receipt Container -->
                <div id="printable-receipt" class="thermal-slip text-left bg-slate-50 p-5 rounded-2xl border border-dashed border-slate-300 font-mono text-xs text-slate-800 space-y-3">
                    <div class="text-center pb-2 border-b border-dashed border-slate-300">
                        <h4 class="font-bold text-sm tracking-tight text-slate-900">BANK MINI SMKN 1</h4>
                        <p class="text-[10px] text-slate-500">Jl. Budi Utomo No. 7, Jakarta Pusat</p>
                        <p class="text-[10px] text-slate-500">Loket 01 • Struk Transaksi</p>
                    </div>

                    <div class="space-y-1 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Ref:</span>
                            <span class="font-bold" x-text="lastTransaction?.transaction_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Waktu:</span>
                            <span x-text="lastTransaction?.date"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Teller:</span>
                            <span x-text="lastTransaction?.teller_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nasabah:</span>
                            <span class="font-bold" x-text="lastTransaction?.student_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Rekening:</span>
                            <span x-text="lastTransaction?.account_number"></span>
                        </div>
                    </div>

                    <div class="py-2 border-t border-b border-dashed border-slate-300 space-y-1 text-xs">
                        <div class="flex justify-between font-bold">
                            <span x-text="lastTransaction?.type === 'deposit' ? 'SETORAN TUNAI' : 'PENARIKAN TUNAI'"></span>
                            <span :class="lastTransaction?.type === 'deposit' ? 'text-emerald-700' : 'text-red-700'"
                                  x-text="lastTransaction?.formatted_amount"></span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-600">
                            <span>Saldo Akhir:</span>
                            <span class="font-bold text-slate-900" x-text="lastTransaction?.formatted_closing_balance"></span>
                        </div>
                    </div>

                    <div class="text-center pt-1 text-[10px] text-slate-400 space-y-1">
                        <p>Simpan tanda terima ini sebagai bukti sah transaksi Bank Mini.</p>
                        <p class="font-mono text-[9px] text-slate-300">*** TERIMA KASIH ***</p>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center gap-2 pt-2 no-print">
                    <button type="button" 
                            @click="showReceiptModal = false; resetTransactionForm();" 
                            class="flex-1 py-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Transaksi Baru [Esc]
                    </button>
                    <button type="button" 
                            @click="printReceipt()" 
                            class="flex-1 py-3 rounded-xl text-xs font-bold bg-[#059669] hover:bg-emerald-700 text-white shadow-md flex items-center justify-center gap-1.5 transition">
                        <span class="material-symbols-outlined text-[16px]">print</span>
                        <span>Cetak Struk [Ctrl+P]</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <!-- HTML5 QR Code Scanner Library for Browser Webcam (Scoped strictly to Counter) -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function posCounter(initialMinDeposit = 10000) {
            return {
                minInitialDeposit: initialMinDeposit,
                lookupMode: 'saver',
                searchQuery: '',
                isLoadingLookup: false,
                identifiedUser: null,
                
                transactionType: 'deposit',
                amount: 50000,
                notes: 'Setoran tabungan mingguan',
                activationPin: '',
                isSopVerified: true,
                
                // PIN Modal
                showPinModal: false,
                showPinText: false,
                transactionPin: '',
                isSubmittingTransaction: false,

                // Receipt Modal
                showReceiptModal: false,
                lastTransaction: null,

                // Camera Scanner
                isCameraScanning: false,
                html5QrCode: null,

                get isMemberMode() {
                    return this.identifiedUser && this.identifiedUser.type === 'member';
                },

                get currentSaverBalance() {
                    if (this.identifiedUser && this.identifiedUser.type === 'saver') {
                        return this.identifiedUser.balance || 0;
                    }
                    return 0;
                },

                get estimatedBalance() {
                    const base = this.currentSaverBalance;
                    const amt = this.amount || 0;
                    if (this.transactionType === 'deposit') {
                        return base + amt;
                    } else {
                        return base - amt;
                    }
                },

                get isBalanceValid() {
                    if (this.transactionType === 'deposit') {
                        if (this.isMemberMode) {
                            return (this.amount || 0) >= this.minInitialDeposit;
                        }
                        return (this.amount || 0) > 0;
                    } else {
                        // Withdrawal requires remaining balance >= 10,000
                        return this.estimatedBalance >= 10000;
                    }
                },

                get balanceValidationMessage() {
                    if (this.transactionType === 'deposit' && this.isMemberMode && (this.amount || 0) < this.minInitialDeposit) {
                        return 'Setoran awal untuk aktivasi rekening baru wajib minimal Rp ' + new Intl.NumberFormat('id-ID').format(this.minInitialDeposit) + '.';
                    }
                    if (this.transactionType === 'withdrawal' && this.estimatedBalance < 10000) {
                        return 'Penarikan melebihi batas! Sisa saldo minimal mengendap wajib Rp 10.000.';
                    }
                    return '';
                },

                get canSubmitDeposit() {
                    return this.identifiedUser && 
                           this.isSopVerified && 
                           this.isBalanceValid && 
                           (this.amount || 0) > 0;
                },

                get canSubmitWithdrawal() {
                    return this.identifiedUser && 
                           this.identifiedUser.type === 'saver' && 
                           this.isSopVerified && 
                           this.isBalanceValid && 
                           this.amount > 0;
                },

                addAmount(val) {
                    this.amount = (this.amount || 0) + val;
                },

                clearLookup() {
                    this.identifiedUser = null;
                    this.searchQuery = '';
                    this.transactionType = 'deposit';
                },

                async performLookup(query) {
                    if (!query || !query.trim()) return;
                    this.isLoadingLookup = true;

                    try {
                        const response = await fetch(`{{ route('teller.lookup') }}?code=${encodeURIComponent(query.trim())}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const res = await response.json();

                        if (response.ok && res.success) {
                            if (res.type === 'saver') {
                                this.identifiedUser = { type: 'saver', ...res.saver };
                                this.lookupMode = 'saver';
                            } else if (res.type === 'member') {
                                this.identifiedUser = { type: 'member', ...res.member };
                                this.lookupMode = 'member';
                                this.transactionType = 'deposit';
                                this.minInitialDeposit = res.member.min_initial_deposit || this.minInitialDeposit;
                                this.amount = Math.max(this.amount || 0, this.minInitialDeposit);
                            }
                        } else {
                            alert(res.message || 'Nasabah tidak ditemukan.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan saat mencari nasabah.');
                    } finally {
                        this.isLoadingLookup = false;
                    }
                },

                openPinModal() {
                    if (this.transactionType === 'deposit' && !this.canSubmitDeposit) return;
                    if (this.transactionType === 'withdrawal' && !this.canSubmitWithdrawal) return;
                    this.transactionPin = (this.isMemberMode && this.activationPin) ? this.activationPin : '';
                    this.showPinText = false;
                    this.showPinModal = true;
                    this.$nextTick(() => {
                        this.focusPinInput();
                    });
                    setTimeout(() => {
                        this.focusPinInput();
                    }, 80);
                },

                focusPinInput() {
                    if (this.$refs.pinInput) {
                        this.$refs.pinInput.focus();
                        this.$refs.pinInput.select();
                    }
                },

                pressPinDigit(n) {
                    if (this.transactionPin.length < 6) {
                        this.transactionPin += n.toString();
                    }
                    this.$nextTick(() => this.$refs.pinInput?.focus());
                },

                deletePinDigit() {
                    this.transactionPin = this.transactionPin.slice(0, -1);
                    this.$nextTick(() => this.$refs.pinInput?.focus());
                },

                clearPinDigit() {
                    this.transactionPin = '';
                    this.$nextTick(() => this.$refs.pinInput?.focus());
                },

                handlePinKeydown(e) {
                    if (!this.showPinModal) return;

                    // If typing directly into the input field itself, let the input field's native events handle it
                    if (document.activeElement === this.$refs.pinInput) {
                        return;
                    }

                    if (e.key >= '0' && e.key <= '9') {
                        e.preventDefault();
                        this.pressPinDigit(e.key);
                    } else if (e.key === 'Backspace') {
                        e.preventDefault();
                        this.deletePinDigit();
                    } else if (e.key === 'Escape') {
                        this.showPinModal = false;
                    } else if (e.key === 'Enter' && this.transactionPin.length === 6 && !this.isSubmittingTransaction) {
                        e.preventDefault();
                        this.submitTransactionWithPin();
                    }
                },

                async submitTransactionWithPin() {
                    if (this.transactionPin.length !== 6 || this.isSubmittingTransaction) return;
                    if (this.transactionType === 'deposit') {
                        await this.executeDepositWithPin();
                    } else {
                        await this.executeWithdrawalWithPin();
                    }
                },

                async executeDepositWithPin() {
                    if (!this.canSubmitDeposit) return;
                    this.isSubmittingTransaction = true;

                    const payload = {
                        amount: this.amount,
                        notes: this.notes,
                        pin: this.transactionPin
                    };

                    if (this.isMemberMode) {
                        payload.member_id = this.identifiedUser.id;
                    } else {
                        payload.account_number = this.identifiedUser.account_number;
                    }

                    try {
                        const response = await fetch(`{{ route('teller.transactions.deposit.web') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });

                        const res = await response.json();

                        if (response.ok && res.success) {
                            this.showPinModal = false;
                            this.transactionPin = '';
                            this.lastTransaction = res.transaction;
                            // Update global drawer balance
                            if (res.transaction.new_drawer_balance !== undefined) {
                                this.currentDrawerBalance = res.transaction.new_drawer_balance;
                            }
                            this.showReceiptModal = true;
                        } else {
                            const errMessage = res.errors?.pin?.[0] || res.message || 'Setoran gagal: Otorisasi PIN tidak cocok.';
                            alert(errMessage);
                            this.transactionPin = '';
                            this.$nextTick(() => this.focusPinInput());
                        }
                    } catch (e) {
                        alert('Terjadi gangguan koneksi sistem loket.');
                    } finally {
                        this.isSubmittingTransaction = false;
                    }
                },

                async executeWithdrawalWithPin() {
                    if (!this.canSubmitWithdrawal) return;
                    this.isSubmittingTransaction = true;

                    const payload = {
                        account_number: this.identifiedUser.account_number,
                        amount: this.amount,
                        pin: this.transactionPin,
                        notes: this.notes
                    };

                    try {
                        const response = await fetch(`{{ route('teller.transactions.withdraw.web') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });

                        const res = await response.json();

                        if (response.ok && res.success) {
                            this.showPinModal = false;
                            this.transactionPin = '';
                            this.lastTransaction = res.transaction;
                            if (res.transaction.new_drawer_balance !== undefined) {
                                this.currentDrawerBalance = res.transaction.new_drawer_balance;
                            }
                            this.showReceiptModal = true;
                        } else {
                            const errMessage = res.errors?.pin?.[0] || res.errors?.amount?.[0] || res.message || 'Penarikan gagal: PIN tidak cocok.';
                            alert(errMessage);
                            this.transactionPin = '';
                            this.$nextTick(() => this.focusPinInput());
                        }
                    } catch (e) {
                        alert('Terjadi gangguan koneksi sistem loket.');
                    } finally {
                        this.isSubmittingTransaction = false;
                    }
                },

                printReceipt() {
                    window.print();
                },

                resetTransactionForm() {
                    this.clearLookup();
                    this.amount = 50000;
                    this.notes = 'Setoran tabungan mingguan';
                    this.activationPin = '';
                    this.transactionPin = '';
                },

                toggleCameraScanner() {
                    if (this.isCameraScanning) {
                        this.stopCameraScanner();
                    } else {
                        this.startCameraScanner();
                    }
                },

                startCameraScanner() {
                    if (typeof Html5Qrcode === 'undefined') {
                        alert('Library QR Scanner sedang memuat. Silakan gunakan pencarian manual sementara.');
                        return;
                    }

                    this.isCameraScanning = true;
                    this.$nextTick(() => {
                        this.html5QrCode = new Html5Qrcode("qr-reader");
                        this.html5QrCode.start(
                            { facingMode: "environment" },
                            { fps: 10, qrbox: { width: 220, height: 220 } },
                            (decodedText) => {
                                this.stopCameraScanner();
                                this.performLookup(decodedText);
                            },
                            () => {}
                        ).catch(() => {
                            this.isCameraScanning = false;
                            alert('Kamera tidak ditemukan atau izin webcam ditolak di browser Anda.');
                        });
                    });
                },

                stopCameraScanner() {
                    if (this.html5QrCode) {
                        this.html5QrCode.stop().then(() => {
                            this.html5QrCode.clear();
                            this.isCameraScanning = false;
                        }).catch(() => {
                            this.isCameraScanning = false;
                        });
                    } else {
                        this.isCameraScanning = false;
                    }
                },

                handleFileUpload(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const html5QrCode = new Html5Qrcode("qr-reader");
                    html5QrCode.scanFile(file, true)
                        .then(decodedText => {
                            this.performLookup(decodedText);
                        })
                        .catch(() => {
                            alert('Tidak dapat mendeteksi kode QR pada file gambar ini.');
                        });
                }
            };
        }
    </script>
    @endpush
</x-teller-layout>
