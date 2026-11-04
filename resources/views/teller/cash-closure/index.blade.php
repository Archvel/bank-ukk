<x-teller-layout title="Penutupan Kas Harian" :drawer-balance="$preview['system_ending_balance'] ?? 0">
    <div class="max-w-[1700px] mx-auto p-4 lg:p-6 space-y-6" x-data="cashClosure({{ json_encode($preview) }})">
        
        <!-- Header Info Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-200">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Penutupan Kas Harian &amp; Rekonsiliasi Fisik</h2>
                <p class="text-xs text-slate-500 mt-0.5">Penghitungan fisik uang laci teller loket 01 untuk penyerahan kas ke Brankas Utama (Akun 102).</p>
            </div>
            <div class="flex items-center gap-2 font-mono text-xs text-slate-500">
                <span>Tanggal Sesi: <strong class="text-slate-800">{{ now()->format('d/m/Y') }}</strong></span>
                <span>•</span>
                <span>Piket: <strong class="text-slate-800">{{ auth()->user()->name }}</strong></span>
            </div>
        </div>

        @if($existingClosure)
            <!-- Banner If Closure Already Submitted -->
            <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">pending_actions</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-amber-900">Laporan Penutupan Kas Telah Diajukan</h4>
                        <p class="text-xs text-amber-800 mt-0.5">
                            Status saat ini: <span class="font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-amber-200 text-amber-900">{{ $existingClosure->status->value }}</span>. 
                            Menunggu verifikasi fisik &amp; otorisasi pemindahan ke Brankas Utama oleh Administrator.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs font-mono">
                    <div class="p-2.5 rounded-xl bg-white/80 border border-amber-200">
                        <span class="text-slate-400 text-[10px] block">Saldo Sistem</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($existingClosure->system_ending_balance, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/80 border border-amber-200">
                        <span class="text-slate-400 text-[10px] block">Uang Fisik Diserahkan</span>
                        <span class="font-bold text-emerald-700">Rp {{ number_format($existingClosure->physical_ending_balance, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/80 border border-amber-200">
                        <span class="text-slate-400 text-[10px] block">Selisih Kas</span>
                        <span class="font-bold {{ $existingClosure->difference === 0 ? 'text-emerald-700' : 'text-red-600' }}">
                            Rp {{ number_format($existingClosure->difference, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/80 border border-amber-200">
                        <span class="text-slate-400 text-[10px] block">Otorisator Brankas</span>
                        <span class="font-bold text-slate-800">{{ $existingClosure->approver?->name ?? 'Belum Di-ACC' }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main 2-Column Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ========================================== -->
            <!-- KOLOM KIRI (6 Cols): KALKULATOR UANG FISIK LACI -->
            <!-- ========================================== -->
            <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">payments</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Kalkulator Penghitungan Uang Fisik Laci</h3>
                            <p class="text-[11px] text-slate-500">Hitung jumlah lembar uang kertas &amp; koin di laci kasir loket</p>
                        </div>
                    </div>
                    <button type="button" @click="resetDenominations()" class="text-xs font-semibold text-slate-500 hover:text-red-600 flex items-center gap-1 transition">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        <span>Reset</span>
                    </button>
                </div>

                <!-- Denomination Input Matrix -->
                <div class="space-y-2.5 font-mono text-xs">
                    <template x-for="(item, index) in denominations" :key="index">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 hover:bg-white hover:border-emerald-300 transition">
                            <div class="w-36 flex items-center gap-2">
                                <span class="px-2 py-1 rounded-lg text-[10px] font-bold text-white shrink-0"
                                      :class="item.badgeClass"
                                      x-text="item.label">
                                </span>
                                <span class="font-bold text-slate-800" x-text="item.nominalText"></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-slate-400 font-sans text-xs">×</span>
                                <input type="number" 
                                       min="0" 
                                       x-model.number="item.count" 
                                       placeholder="0"
                                       class="w-20 text-center py-1.5 px-2 text-xs font-bold rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white" />
                                <span class="text-slate-400 text-[10px]" x-text="item.unit"></span>
                            </div>

                            <div class="w-32 text-right font-bold text-slate-900 tabular-nums" 
                                 x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(item.count * item.value))">
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Quick Autofill Button for testing -->
                <div class="pt-1 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Bantuan Cepat:</span>
                    <button type="button" 
                            @click="autofillMatchingSystem()"
                            class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition">
                        Isi Otomatis Pas Sesuai Saldo Sistem (Balance)
                    </button>
                </div>

                <!-- Highlighted Total Box in Left Column -->
                <div class="p-4 rounded-xl bg-[#0F172A] text-white flex items-center justify-between shadow-md">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Uang Fisik Di Laci Kasir</span>
                        <span class="text-xs text-slate-400">Penjumlahan otomatis dari seluruh pecahan</span>
                    </div>
                    <div class="text-2xl font-extrabold font-mono text-[#34D399] tabular-nums" 
                         x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(totalPhysicalCash))">
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- KOLOM KANAN (6 Cols): REKONSILIASI & PENGAJUAN PENUTUPAN KAS -->
            <!-- ========================================== -->
            <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                
                <!-- Section 1: System Ledger Formula Breakdown -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-slate-600">account_tree</span>
                            <h3 class="text-sm font-bold text-slate-900">Audit Buku Besar Sistem (Real-Time Ledger)</h3>
                        </div>
                        <span class="text-[10px] font-mono font-bold text-slate-400">RUMUS KASIR AKUN 101</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[10px] block">1. Saldo Awal Sesi</span>
                                @if(!($existingClosure && in_array($existingClosure->status, [\App\Enums\CashClosureStatus::Submitted, \App\Enums\CashClosureStatus::Approved])))
                                <button type="button" 
                                        @click="$dispatch('open-kas-awal-modal')"
                                        class="text-[10px] text-emerald-600 hover:text-emerald-800 font-sans font-bold flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-[13px]">edit</span>
                                    <span>Ubah Kas Awal</span>
                                </button>
                                @endif
                            </div>
                            <span class="font-bold text-slate-900 text-sm tabular-nums">
                                Rp {{ number_format($preview['opening_balance'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="text-emerald-700 text-[10px] block">2. (+) Setoran Tunai Masuk</span>
                            <span class="font-bold text-emerald-800 text-sm tabular-nums">
                                +Rp {{ number_format($preview['total_deposit'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-red-50 border border-red-200">
                            <span class="text-red-700 text-[10px] block">3. (-) Penarikan Tunai Keluar</span>
                            <span class="font-bold text-red-800 text-sm tabular-nums">
                                -Rp {{ number_format($preview['total_withdrawal'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-100 border border-slate-300">
                            <span class="text-slate-600 text-[10px] block">4. (=) Saldo Kas Akhir Sistem</span>
                            <span class="font-extrabold text-slate-900 text-sm tabular-nums">
                                Rp {{ number_format($preview['system_ending_balance'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Direct Comparison & Equilibrium Indicator -->
                <div class="space-y-3 pt-2">
                    <div class="grid grid-cols-3 gap-2 text-center text-xs font-mono p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div>
                            <span class="text-slate-400 text-[10px] block">Saldo Sistem</span>
                            <span class="font-bold text-slate-800 tabular-nums">
                                Rp {{ number_format($preview['system_ending_balance'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Uang Fisik Laci</span>
                            <span class="font-bold text-slate-800 tabular-nums" 
                                  x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(totalPhysicalCash))">
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Selisih (Diff)</span>
                            <span class="font-extrabold tabular-nums"
                                  :class="difference === 0 ? 'text-emerald-700' : 'text-red-600'"
                                  x-text="(difference > 0 ? '+' : '') + 'Rp ' + (new Intl.NumberFormat('id-ID').format(difference))">
                            </span>
                        </div>
                    </div>

                    <!-- Dynamic State Banner: Balance vs Discrepancy -->
                    <template x-if="difference === 0">
                        <div class="p-4 rounded-xl bg-emerald-50 border-2 border-emerald-500 text-emerald-950 flex items-center gap-3">
                            <span class="material-symbols-outlined text-3xl text-emerald-600 shrink-0">verified</span>
                            <div>
                                <h4 class="font-extrabold text-xs uppercase tracking-wider text-emerald-900">STATUS KAS: BALANCE (SEIMBANG &amp; SESUAI)</h4>
                                <p class="text-[11px] text-emerald-800 mt-0.5 leading-relaxed">
                                    Jumlah uang fisik di laci kasir cocok 100% dengan total transaksi sistem. Rekonsiliasi siap diajukan ke Brankas Utama.
                                </p>
                            </div>
                        </div>
                    </template>

                    <template x-if="difference !== 0">
                        <div class="p-4 rounded-xl bg-red-50 border-2 border-red-500 text-red-950 flex items-center gap-3">
                            <span class="material-symbols-outlined text-3xl text-red-600 shrink-0">error</span>
                            <div>
                                <h4 class="font-extrabold text-xs uppercase tracking-wider text-red-900">SELISIH KAS TERDETEKSI (MODUL CETAK &amp; SUBMIT TERKUNCI)</h4>
                                <p class="text-[11px] text-red-800 mt-0.5 leading-relaxed">
                                    Terdapat selisih kas sebesar <strong x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(Math.abs(difference)))"></strong>. Sesuai SOP, uang fisik wajib dihitung ulang hingga seimbang (selisih Rp 0).
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Section 3: Teller Closure Form -->
                <form method="POST" action="{{ route('teller.cash-closure.store') }}" class="space-y-4 pt-1">
                    @csrf
                    <input type="hidden" name="physical_ending_balance" :value="totalPhysicalCash" />

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Verifikasi Kasir / Teller Piket (Wajib diisi)</label>
                        <textarea name="notes" 
                                  rows="3" 
                                  required
                                  placeholder="Tuliskan catatan kondisi fisik uang (misal: Semua uang telah disortir dan diikat sesuai pecahan untuk diserahkan ke Brankas Utama)..."
                                  class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Dual Control Security Disclaimer -->
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 text-[11px] flex items-start gap-2">
                        <span class="material-symbols-outlined text-[16px] text-amber-600 mt-0.5 shrink-0">gavel</span>
                        <span>
                            <strong>Aturan Dual Control:</strong> Petugas Teller tidak dapat melakukan ACC penutupan kasnya sendiri. Otorisasi pemindahan fisik uang ke Brankas Utama (Akun 102) wajib disetujui oleh Administrator.
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2 pt-2">
                        <!-- Submit Button (Locked if difference != 0) -->
                        <button type="submit" 
                                :disabled="difference !== 0 || isSubmitting"
                                :class="difference === 0 ? 'bg-[#059669] hover:bg-emerald-700' : 'bg-slate-300 cursor-not-allowed opacity-60'"
                                class="w-full py-3.5 rounded-xl text-sm font-bold text-white shadow-md transition flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                            <span>Simpan Penutupan Kas &amp; Ajukan ke Brankas Utama [F12]</span>
                        </button>

                        <!-- Print Draft Button (Locked if difference != 0) -->
                        <button type="button" 
                                :disabled="difference !== 0"
                                onclick="window.print()"
                                :class="difference === 0 ? 'bg-white hover:bg-slate-50 text-slate-700 border-slate-300' : 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed'"
                                class="w-full py-2.5 rounded-xl text-xs font-bold border shadow-xs transition flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">print</span>
                            <span>Cetak Draf Berita Acara Kas Harian [Ctrl+P]</span>
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        function cashClosure(previewData) {
            return {
                systemEndingBalance: previewData.system_ending_balance || 0,
                isSubmitting: false,
                
                denominations: [
                    { label: '100k', nominalText: 'Rp 100.000', value: 100000, count: 0, unit: 'lembar', badgeClass: 'bg-red-600' },
                    { label: '50k', nominalText: 'Rp 50.000', value: 50000, count: 0, unit: 'lembar', badgeClass: 'bg-blue-600' },
                    { label: '20k', nominalText: 'Rp 20.000', value: 20000, count: 0, unit: 'lembar', badgeClass: 'bg-emerald-600' },
                    { label: '10k', nominalText: 'Rp 10.000', value: 10000, count: 0, unit: 'lembar', badgeClass: 'bg-purple-600' },
                    { label: '5k', nominalText: 'Rp 5.000', value: 5000, count: 0, unit: 'lembar', badgeClass: 'bg-amber-600' },
                    { label: '2k', nominalText: 'Rp 2.000', value: 2000, count: 0, unit: 'lembar', badgeClass: 'bg-slate-500' },
                    { label: '1k', nominalText: 'Rp 1.000', value: 1000, count: 0, unit: 'keping', badgeClass: 'bg-slate-600' }
                ],

                get totalPhysicalCash() {
                    return this.denominations.reduce((sum, item) => sum + ((item.count || 0) * item.value), 0);
                },

                get difference() {
                    return this.totalPhysicalCash - this.systemEndingBalance;
                },

                resetDenominations() {
                    this.denominations.forEach(item => item.count = 0);
                },

                autofillMatchingSystem() {
                    let remaining = this.systemEndingBalance;
                    this.denominations.forEach(item => {
                        const count = Math.floor(remaining / item.value);
                        item.count = count;
                        remaining -= (count * item.value);
                    });
                }
            };
        }
    </script>
    @endpush
</x-teller-layout>
