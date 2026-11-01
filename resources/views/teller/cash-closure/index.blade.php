<x-teller-layout title="Penutupan Kas Harian" :drawer-balance="$preview['system_ending_balance'] ?? 0">
    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6" x-data="cashClosure({{ json_encode($preview) }})">
        
        @if($existingClosure)
            <!-- Info Jika Laporan Sudah Diajukan Hari Ini -->
            <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-950 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-amber-600 text-[22px]">pending_actions</span>
                        <h4 class="font-bold text-sm text-amber-900">Laporan Penutupan Kas Telah Diajukan Hari Ini</h4>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-200 text-amber-900 uppercase">
                        {{ $existingClosure->status->value }}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-3 pt-1 text-xs font-mono">
                    <div>
                        <span class="text-slate-500 block text-[10px]">Saldo Sistem</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($existingClosure->system_ending_balance, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Uang Fisik</span>
                        <span class="font-bold text-emerald-700">Rp {{ number_format($existingClosure->physical_ending_balance, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Selisih</span>
                        <span class="font-bold {{ $existingClosure->difference === 0 ? 'text-emerald-700' : 'text-red-600' }}">
                            Rp {{ number_format($existingClosure->difference, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Layout 2 Kolom Simpel -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- ========================================== -->
            <!-- KOLOM KIRI (6 Cols): PENGHITUNGAN UANG FISIK -->
            <!-- ========================================== -->
            <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Penghitungan Uang Fisik</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Hitung lembar uang kertas dan koin di laci kasir</p>
                    </div>
                    <button type="button" @click="resetDenominations()" class="text-xs font-medium text-slate-400 hover:text-slate-700 transition">
                        Reset
                    </button>
                </div>

                <!-- Input Pecahan Uang (Bersih Tanpa Badge Warna-warni) -->
                <div class="space-y-2">
                    <template x-for="(item, index) in denominations" :key="index">
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 hover:bg-white hover:border-slate-300 transition">
                            <!-- Label Pecahan (Teks Monospace Bersih) -->
                            <div class="w-28 font-mono text-xs font-semibold text-slate-800" x-text="item.nominalText"></div>

                            <!-- Simbol & Input Jumlah -->
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400 text-xs select-none">×</span>
                                <input type="number" 
                                       min="0" 
                                       x-model.number="item.count" 
                                       placeholder="0"
                                       class="w-16 text-center py-1.5 px-2 text-xs font-mono font-bold rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-white" />
                                <span class="text-[11px] text-slate-400 w-10 text-left" x-text="item.unit"></span>
                            </div>

                            <!-- Subtotal Nilai -->
                            <div class="w-28 text-right font-mono font-semibold text-xs text-slate-900 tabular-nums" 
                                 x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(item.count * item.value))">
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Quick Helper Button -->
                <div class="pt-1 flex items-center justify-end text-xs">
                    <button type="button" 
                            @click="autofillMatchingSystem()"
                            class="text-xs text-emerald-700 hover:text-emerald-800 font-medium transition">
                        Sesuaikan dengan saldo sistem
                    </button>
                </div>

                <!-- Baris Total Uang Fisik Laci di Bawah -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 block">Total Uang Fisik Laci</span>
                        <span class="text-[11px] text-slate-400">Akumulasi seluruh lembar</span>
                    </div>
                    <div class="text-xl font-extrabold font-mono text-[#059669] tabular-nums" 
                         x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(totalPhysicalCash))">
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- KOLOM KANAN (6 Cols): RINGKASAN REKONSILIASI -->
            <!-- ========================================== -->
            <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Ringkasan Rekonsiliasi Kas</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pencocokan saldo sistem dengan fisik laci</p>
                </div>

                <!-- Card Ringkasan Angka Vertikal yang Rapi -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 font-mono text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-sans">Saldo Awal</span>
                        <span class="font-bold text-slate-800 tabular-nums">
                            Rp {{ number_format($preview['opening_balance'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-sans">Total Setoran</span>
                        <span class="font-bold text-emerald-700 tabular-nums">
                            +Rp {{ number_format($preview['total_deposit'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-sans">Total Penarikan</span>
                        <span class="font-bold text-red-600 tabular-nums">
                            -Rp {{ number_format($preview['total_withdrawal'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm">
                        <span class="text-slate-700 font-sans font-semibold">Saldo Akhir Sistem</span>
                        <span class="font-extrabold text-slate-900 tabular-nums">
                            Rp {{ number_format($preview['system_ending_balance'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-700 font-sans font-semibold">Uang Fisik Laci</span>
                        <span class="font-extrabold text-slate-900 tabular-nums" 
                              x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(totalPhysicalCash))">
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm">
                        <span class="text-slate-700 font-sans font-semibold">Selisih Kas</span>
                        <span class="font-extrabold tabular-nums"
                              :class="difference === 0 ? 'text-emerald-700' : 'text-red-600'"
                              x-text="(difference > 0 ? '+' : '') + 'Rp ' + (new Intl.NumberFormat('id-ID').format(difference))">
                        </span>
                    </div>
                </div>

                <!-- Cukup Tampilkan 1 Badge Status (Seimbang / Selisih) -->
                <div>
                    <template x-if="difference === 0">
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-2.5 text-xs font-medium">
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
                            <span>Seimbang / Balance (Fisik uang cocok 100% dengan saldo sistem)</span>
                        </div>
                    </template>

                    <template x-if="difference !== 0">
                        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-900 flex items-center gap-2.5 text-xs font-medium">
                            <span class="material-symbols-outlined text-red-600 text-[20px]">error</span>
                            <span>Selisih Kas Terdeteksi (Uang fisik wajib disesuaikan hingga seimbang)</span>
                        </div>
                    </template>
                </div>

                <!-- Form Penutupan Kas -->
                <form method="POST" action="{{ route('teller.cash-closure.store') }}" class="space-y-4 pt-1">
                    @csrf
                    <input type="hidden" name="physical_ending_balance" :value="totalPhysicalCash" />

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penutupan Kas (Opsional)</label>
                        <textarea name="notes" 
                                  rows="2" 
                                  placeholder="Tuliskan catatan kondisi fisik uang jika ada..."
                                  class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 p-3"></textarea>
                    </div>

                    <!-- Tombol Utama: Simpan & Cetak Draf Laporan Harian -->
                    <div>
                        <button type="submit" 
                                :disabled="difference !== 0 || isSubmitting"
                                :class="difference === 0 ? 'bg-[#059669] hover:bg-emerald-700 text-white shadow-xs' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="w-full py-3.5 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2">
                            <span>Simpan &amp; Cetak Draf Laporan Harian</span>
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
                    { nominalText: 'Rp 100.000', value: 100000, count: 0, unit: 'lembar' },
                    { nominalText: 'Rp 50.000', value: 50000, count: 0, unit: 'lembar' },
                    { nominalText: 'Rp 20.000', value: 20000, count: 0, unit: 'lembar' },
                    { nominalText: 'Rp 10.000', value: 10000, count: 0, unit: 'lembar' },
                    { nominalText: 'Rp 5.000', value: 5000, count: 0, unit: 'lembar' },
                    { nominalText: 'Rp 2.000', value: 2000, count: 0, unit: 'lembar' },
                    { nominalText: 'Rp 1.000', value: 1000, count: 0, unit: 'keping' }
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
