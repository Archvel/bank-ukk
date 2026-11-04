<!-- Kas Awal Modal (Modal Input Kas Awal Sesi Teller) -->
<div x-show="openKasAwalModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto no-print" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true"
     x-data="kasAwalModalHandler({
        openingBalance: {{ $openingBalance ?? 0 }},
        suggestedBalance: {{ $suggestedOpeningBalance ?? 0 }},
        hasSession: {{ ($hasOpenedSession ?? false) ? 'true' : 'false' }},
        isLocked: {{ ($isClosureSubmitted ?? false) ? 'true' : 'false' }},
        existingNotes: '{{ addslashes($todayClosure?->notes ?? '') }}'
     })">

    <!-- Backdrop Blur Overlay -->
    <div x-show="openKasAwalModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"
         @click="if(!isSubmitting) openKasAwalModal = false">
    </div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="openKasAwalModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">

            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-400 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">savings</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-tight leading-tight">Input Kas Awal Sesi Teller</h3>
                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">Sesi: {{ now('Asia/Jakarta')->translatedFormat('d F Y') }} • Jam Operasional: 07:00 - 15:30 WIB</p>
                    </div>
                </div>
                <button type="button" 
                        @click="openKasAwalModal = false"
                        :disabled="isSubmitting"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/60 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="submitKasAwal()" method="POST" action="{{ route('teller.opening-cash.store') }}" class="p-6 space-y-4">
                @csrf

                <!-- Status Banner Inside Modal -->
                @if($isClosureSubmitted ?? false)
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-amber-600 text-[20px] shrink-0 mt-0.5">lock</span>
                        <div>
                            <strong class="font-bold block">Sesi Hari Ini Sudah Ditutup</strong>
                            <span>Laporan penutupan kas hari ini sudah diajukan ke Brankas Utama. Kas awal tidak dapat diubah lagi.</span>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px] shrink-0 mt-0.5">info</span>
                        <div class="leading-relaxed">
                            <span class="font-semibold text-slate-900">Uang Kas Awal (Modal Laci)</span> adalah nominal uang tunai yang disiapkan di laci kasir saat loket mulai buka untuk melayani penarikan tunai dan uang kembalian.
                        </div>
                    </div>
                @endif

                <!-- Suggested Balance from Yesterday's Close -->
                <div x-show="suggestedBalance > 0 && !hasSession && !isLocked" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] text-emerald-700 uppercase font-bold block">Saran dari Sesi Kemarin</span>
                        <span class="font-mono font-bold" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(suggestedBalance))"></span>
                    </div>
                    <button type="button" 
                            @click="openingAmount = suggestedBalance"
                            class="px-3 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                        Gunakan Nilai Ini
                    </button>
                </div>

                <!-- Input Nominal Field -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-800">Nominal Kas Awal (Rp)</label>
                        <span class="font-mono text-xs font-extrabold text-emerald-700 tabular-nums" 
                              x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(openingAmount || 0))">
                        </span>
                    </div>

                    <div class="relative rounded-xl shadow-xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <span class="text-xs font-bold font-mono text-slate-400">Rp</span>
                        </div>
                        <input type="number" 
                               name="opening_balance" 
                               min="0"
                               step="1000"
                               required
                               :disabled="isLocked || isSubmitting"
                               x-model.number="openingAmount"
                               placeholder="Contoh: 500000" 
                               class="block w-full rounded-xl border-slate-300 pl-10 pr-4 py-2.5 text-sm font-mono font-bold text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-100 disabled:cursor-not-allowed" />
                    </div>
                </div>

                <!-- Quick Preset Nominal Buttons -->
                <div class="space-y-1.5" x-show="!isLocked">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Pilihan Cepat Nominal Modal</span>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5">
                        <button type="button" 
                                @click="openingAmount = 0" 
                                class="py-1.5 px-2 rounded-lg text-[11px] font-bold border transition text-center"
                                :class="openingAmount === 0 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            Rp 0
                        </button>
                        <button type="button" 
                                @click="openingAmount = 100000" 
                                class="py-1.5 px-2 rounded-lg text-[11px] font-bold border transition text-center"
                                :class="openingAmount === 100000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            100k
                        </button>
                        <button type="button" 
                                @click="openingAmount = 250000" 
                                class="py-1.5 px-2 rounded-lg text-[11px] font-bold border transition text-center"
                                :class="openingAmount === 250000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            250k
                        </button>
                        <button type="button" 
                                @click="openingAmount = 500000" 
                                class="py-1.5 px-2 rounded-lg text-[11px] font-bold border transition text-center"
                                :class="openingAmount === 500000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            500k
                        </button>
                        <button type="button" 
                                @click="openingAmount = 1000000" 
                                class="py-1.5 px-2 rounded-lg text-[11px] font-bold border transition text-center"
                                :class="openingAmount === 1000000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            1 Jt
                        </button>
                        <button type="button" 
                                @click="openingAmount = 2000000" 
                                class="py-1.5 px-2 rounded-lg text-[11px] font-bold border transition text-center"
                                :class="openingAmount === 2000000 ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'">
                            2 Jt
                        </button>
                    </div>
                </div>

                <!-- Notes / Keterangan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Keterangan (Opsional)</label>
                    <textarea name="notes" 
                              rows="2" 
                              :disabled="isLocked || isSubmitting"
                              x-model="notes"
                              placeholder="Misal: Modal pecahan uang kertas 5k, 10k, 20k dari Brankas Utama..."
                              class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-100 disabled:cursor-not-allowed"></textarea>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="openKasAwalModal = false"
                            :disabled="isSubmitting"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>

                    <button type="submit" 
                            x-show="!isLocked"
                            :disabled="isSubmitting || openingAmount < 0"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-2 disabled:opacity-50">
                        <span x-show="!isSubmitting" class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span x-show="isSubmitting" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                        <span x-text="hasSession ? 'Perbarui Kas Awal Sesi' : 'Simpan &amp; Mulai Sesi Loket'"></span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    function kasAwalModalHandler(config) {
        return {
            openingAmount: config.openingBalance || 0,
            suggestedBalance: config.suggestedBalance || 0,
            hasSession: config.hasSession || false,
            isLocked: config.isLocked || false,
            notes: config.existingNotes || '',
            isSubmitting: false,

            async submitKasAwal() {
                if (this.isLocked || this.openingAmount < 0) return;
                this.isSubmitting = true;

                try {
                    const response = await fetch("{{ route('teller.opening-cash.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            opening_balance: this.openingAmount,
                            notes: this.notes
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.hasSession = true;
                        
                        // Update global alpine data on body
                        if (data.new_drawer_balance !== undefined) {
                            window.dispatchEvent(new CustomEvent('drawer-updated', { detail: data.new_drawer_balance }));
                        }
                        window.dispatchEvent(new CustomEvent('opening-cash-updated', { 
                            detail: {
                                openingBalance: this.openingAmount,
                                drawerBalance: data.new_drawer_balance
                            } 
                        }));

                        // Close modal
                        this.openKasAwalModal = false;

                        // Reload page smoothly to refresh all system previews
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal menyimpan kas awal.');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan koneksi server saat menyimpan kas awal.');
                } finally {
                    this.isSubmitting = false;
                }
            }
        };
    }
</script>
