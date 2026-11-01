<x-teller-layout title="Meja Layanan Loket" :drawer-balance="$drawerBalance">
    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6" x-data="posCounter()">
        
        <!-- Grid Meja Layanan Loket (Maksimal 2 Card Bersih, Padding Lega) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- ========================================== -->
            <!-- KOLOM KIRI (35% Lebar): IDENTIFIKASI NASABAH -->
            <!-- ========================================== -->
            <div class="lg:col-span-5 space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                    
                    <!-- Segmented Tab Sederhana: Penabung (Savers) | Calon Penabung (Members) -->
                    <div class="flex items-center p-1 bg-slate-100 rounded-xl">
                        <button type="button" 
                                @click="lookupMode = 'saver'; if(identifiedUser && identifiedUser.type !== 'saver') clearLookup();"
                                :class="lookupMode === 'saver' ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="flex-1 py-2 rounded-lg text-xs transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">credit_card</span>
                            <span>Penabung (Savers)</span>
                        </button>
                        <button type="button" 
                                @click="lookupMode = 'member'; if(identifiedUser && identifiedUser.type !== 'member') clearLookup();"
                                :class="lookupMode === 'member' ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="flex-1 py-2 rounded-lg text-xs transition flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">person_add</span>
                            <span>Calon Penabung (Members)</span>
                        </button>
                    </div>

                    <!-- Satu Baris Input Pencarian Berdampingan dengan Tombol Ikon Scan QR -->
                    <div class="flex items-center gap-2">
                        <form @submit.prevent="performLookup(searchQuery)" class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3.5 text-slate-400 text-[18px]">search</span>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   placeholder="Cari No. Rekening / Nama..."
                                   class="w-full pl-10 pr-16 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500" />
                            <button type="submit" 
                                    :disabled="isLoadingLookup || !searchQuery.trim()"
                                    class="absolute right-1.5 top-1.5 bottom-1.5 px-3 rounded-lg text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 transition flex items-center justify-center">
                                <span x-show="!isLoadingLookup">Cari</span>
                                <span x-show="isLoadingLookup" class="material-symbols-outlined text-[14px] animate-spin">progress_activity</span>
                            </button>
                        </form>

                        <!-- Tombol Ikon Scan QR (Membuka Kamera di Modal Pop-up) -->
                        <button type="button" 
                                @click="openScanModal()"
                                title="Buka Kamera Scan QR"
                                class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700 text-slate-700 transition flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
                        </button>
                    </div>

                    <!-- Card Profil Nasabah yang Ringkas -->
                    <div>
                        <!-- State 1: Belum Ada Nasabah -->
                        <template x-if="!identifiedUser">
                            <div class="py-12 px-4 rounded-xl border border-dashed border-slate-200 text-center text-slate-400 space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                                    <span class="material-symbols-outlined text-2xl">account_circle</span>
                                </div>
                                <p class="text-xs font-medium text-slate-600">Belum Ada Nasabah Dipilih</p>
                                <p class="text-[11px] text-slate-400">Cari nomor rekening / nama siswa atau klik tombol Scan QR di atas.</p>
                            </div>
                        </template>

                        <!-- State 2: Penabung Aktif (Savers) -->
                        <template x-if="identifiedUser && identifiedUser.type === 'saver'">
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-base font-bold text-slate-900" x-text="identifiedUser.student_name"></h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            <span x-text="identifiedUser.account_number"></span> • Kelas <span x-text="identifiedUser.graduation_year"></span>
                                        </p>
                                    </div>
                                    <button type="button" @click="clearLookup()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-200/60" title="Ganti Nasabah">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>

                                <div class="pt-3 border-t border-slate-200/60">
                                    <span class="text-[11px] font-medium text-slate-500 uppercase tracking-wider block">Saldo Saat Ini</span>
                                    <div class="text-3xl font-extrabold font-mono text-slate-900 tabular-nums mt-0.5" x-text="identifiedUser.formatted_balance"></div>
                                    <p class="text-[11px] text-slate-500 mt-1.5">
                                        Maksimal ditarik: <strong class="text-slate-800" x-text="identifiedUser.formatted_max_withdrawable"></strong> (Min. saldo mengendap Rp10.000)
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- State 3: Calon Penabung (Members) -->
                        <template x-if="identifiedUser && identifiedUser.type === 'member'">
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-base font-bold text-slate-900" x-text="identifiedUser.student_name"></h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Calon Penabung</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">Tahun Lulus: <span x-text="identifiedUser.graduation_year"></span></p>
                                    </div>
                                    <button type="button" @click="clearLookup()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-200/60" title="Ganti Nasabah">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>
                                <p class="text-xs text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200">
                                    Aktivasi rekening baru melalui setoran awal minimal <strong>Rp 10.000</strong> dan pembuatan 6-digit PIN pada form di samping.
                                </p>
                            </div>
                        </template>
                    </div>

                </div>
            </div>

            <!-- ========================================== -->
            <!-- KOLOM KANAN (65% Lebar): FORM TRANSAKSI -->
            <!-- ========================================== -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-6">
                    
                    <!-- Switch Tab Bersih: Setoran Tunai & Penarikan Tunai -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                        <button type="button"
                                @click="transactionType = 'deposit'"
                                :class="transactionType === 'deposit' ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="py-2.5 rounded-lg text-xs flex items-center justify-center gap-2 transition">
                            <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                            <span>Setoran Tunai</span>
                        </button>

                        <button type="button"
                                @click="if(!isMemberMode) transactionType = 'withdrawal'"
                                :disabled="isMemberMode"
                                :class="[
                                    transactionType === 'withdrawal' ? 'bg-red-600 text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium',
                                    isMemberMode ? 'opacity-40 cursor-not-allowed' : ''
                                ]"
                                class="py-2.5 rounded-lg text-xs flex items-center justify-center gap-2 transition">
                            <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                            <span>Penarikan Tunai</span>
                        </button>
                    </div>

                    <!-- Input Nominal Rupiah Bersih -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-700">
                            Nominal Transaksi (Rp)
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-xl font-bold font-mono text-slate-400 select-none">Rp</span>
                            <input type="number" 
                                   x-model.number="amount"
                                   min="1000"
                                   step="1000"
                                   placeholder="0"
                                   class="w-full pl-14 pr-4 py-3.5 text-2xl font-extrabold font-mono text-slate-900 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 tabular-nums" />
                        </div>

                        <!-- 4 Tombol Nominal Cepat (+10.000, +20.000, +50.000, +100.000) -->
                        <div class="grid grid-cols-4 gap-2 pt-1">
                            <button type="button" @click="addAmount(10000)" class="py-2 rounded-xl text-xs font-medium bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition text-center">+10.000</button>
                            <button type="button" @click="addAmount(20000)" class="py-2 rounded-xl text-xs font-medium bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition text-center">+20.000</button>
                            <button type="button" @click="addAmount(50000)" class="py-2 rounded-xl text-xs font-medium bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition text-center">+50.000</button>
                            <button type="button" @click="addAmount(100000)" class="py-2 rounded-xl text-xs font-medium bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200 transition text-center">+100.000</button>
                        </div>
                    </div>

                    <!-- Satu Baris Ringkas: Estimasi Saldo Akhir -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Estimasi Saldo Akhir:</span>
                        <span class="font-mono font-bold text-sm tabular-nums"
                              :class="isBalanceValid ? 'text-slate-900' : 'text-red-600'"
                              x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(estimatedBalance))">
                        </span>
                    </div>

                    <!-- Peringatan Ringkas jika Saldo Mengendap Kurang -->
                    <div x-show="!isBalanceValid" class="p-3 rounded-xl bg-red-50 text-red-700 text-xs flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">info</span>
                        <span x-text="balanceValidationMessage"></span>
                    </div>

                    <!-- PIN Pembuatan Khusus Calon Penabung (Aktivasi) -->
                    <div x-show="isMemberMode" class="p-4 rounded-xl bg-amber-50/80 border border-amber-200 space-y-2">
                        <label class="block text-xs font-semibold text-amber-950">Buat PIN Keamanan 6-Digit Siswa</label>
                        <input type="password" 
                               maxlength="6" 
                               pattern="[0-9]*" 
                               inputmode="numeric"
                               x-model="activationPin"
                               placeholder="Masukkan 6 angka PIN" 
                               class="w-full text-sm font-mono tracking-widest text-center rounded-xl border border-amber-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white" />
                    </div>

                    <!-- Input Catatan Transaksi (Opsional) -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Catatan Transaksi (Opsional)</label>
                        <input type="text" 
                               x-model="notes" 
                               placeholder="Tuliskan keterangan jika ada..."
                               class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3.5" />
                    </div>

                    <!-- Satu Checkbox Singkat -->
                    <div class="pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs text-slate-700">
                            <input type="checkbox" 
                                   x-model="isSopVerified"
                                   class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 w-4 h-4" />
                            <span>Uang fisik dan slip transaksi sudah sesuai</span>
                        </label>
                    </div>

                    <!-- Satu Tombol Utama Penuh: Simpan Transaksi (#059669) -->
                    <div>
                        <!-- Mode Setoran -->
                        <button type="button" 
                                x-show="transactionType === 'deposit'"
                                @click="executeDeposit()"
                                :disabled="!canSubmitDeposit"
                                class="w-full py-3.5 rounded-xl text-sm font-bold bg-[#059669] hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white shadow-xs transition flex items-center justify-center gap-2">
                            <span>Simpan Transaksi</span>
                        </button>

                        <!-- Mode Penarikan (Membuka Modal PIN Pop-Up) -->
                        <button type="button" 
                                x-show="transactionType === 'withdrawal'"
                                @click="openPinModal()"
                                :disabled="!canSubmitWithdrawal"
                                class="w-full py-3.5 rounded-xl text-sm font-bold bg-[#059669] hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white shadow-xs transition flex items-center justify-center gap-2">
                            <span>Simpan Transaksi</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL POP-UP 1: KAMERA SCAN QR (OVERLAY) -->
        <!-- ========================================== -->
        <div x-show="showScanModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm no-print">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 border border-slate-200 text-center"
                 @click.away="closeScanModal()">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Scan QR Nasabah</h3>
                    <button type="button" @click="closeScanModal()" class="text-slate-400 hover:text-slate-700 p-1">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <!-- Viewfinder Box di Modal -->
                <div class="relative w-full aspect-square bg-slate-900 rounded-2xl overflow-hidden flex items-center justify-center">
                    <div id="qr-reader" class="w-full h-full" x-show="isCameraScanning"></div>
                    <div class="flex flex-col items-center justify-center p-4 text-center text-slate-400" x-show="!isCameraScanning">
                        <span class="material-symbols-outlined text-4xl mb-2 text-slate-500">videocam_off</span>
                        <p class="text-xs">Kamera belum aktif</p>
                    </div>
                </div>

                <!-- Tombol Kontrol Scanner -->
                <div class="flex items-center gap-2 pt-1">
                    <button type="button" 
                            @click="toggleCameraScanner()"
                            :class="isCameraScanning ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="flex-1 py-2.5 rounded-xl text-xs font-semibold text-white transition flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]" x-text="isCameraScanning ? 'stop' : 'videocam'"></span>
                        <span x-text="isCameraScanning ? 'Hentikan Kamera' : 'Mulai Kamera'"></span>
                    </button>

                    <label class="cursor-pointer py-2.5 px-3 rounded-xl text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 flex items-center gap-1.5 transition">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span>
                        <span>Upload</span>
                        <input type="file" accept="image/*" class="hidden" @change="handleFileUpload($event)" />
                    </label>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL POP-UP 2: OTORISASI PIN 6-DIGIT (OVERLAY) -->
        <!-- ========================================== -->
        <div x-show="showPinModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm no-print">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-200"
                 @click.away="showPinModal = false">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined text-2xl">lock</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Otorisasi PIN Nasabah</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Masukkan 6-digit PIN nasabah untuk penarikan sebesar 
                        <strong class="text-slate-800 font-mono" x-text="'Rp ' + (new Intl.NumberFormat('id-ID').format(amount || 0))"></strong>
                    </p>
                </div>

                <!-- 6 Masked PIN Indicator Dots -->
                <div class="flex items-center justify-center gap-2 py-2">
                    <template x-for="i in 6" :key="i">
                        <div class="w-3.5 h-3.5 rounded-full border-2 transition-all duration-150"
                             :class="withdrawalPin.length >= i ? 'bg-slate-900 border-slate-900 scale-110' : 'bg-slate-100 border-slate-300'">
                        </div>
                    </template>
                </div>

                <!-- Numpad 0-9 & Control Buttons -->
                <div class="grid grid-cols-3 gap-2 pt-2">
                    <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                        <button type="button" @click="pressPinDigit(n)" 
                                class="py-3 text-lg font-bold font-mono rounded-xl bg-slate-50 hover:bg-slate-200 text-slate-800 transition active:scale-95">
                            <span x-text="n"></span>
                        </button>
                    </template>
                    <button type="button" @click="clearPinDigit()" 
                            class="py-3 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Hapus
                    </button>
                    <button type="button" @click="pressPinDigit(0)" 
                                class="py-3 text-lg font-bold font-mono rounded-xl bg-slate-50 hover:bg-slate-200 text-slate-800 transition active:scale-95">
                        0
                    </button>
                    <button type="button" @click="deletePinDigit()" 
                            class="py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                        <span class="material-symbols-outlined text-[18px]">backspace</span>
                    </button>
                </div>

                <!-- Action Button in PIN Modal -->
                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="showPinModal = false" class="w-1/3 py-2.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">
                        Batal
                    </button>
                    <button type="button" 
                            @click="executeWithdrawal()"
                            :disabled="withdrawalPin.length !== 6 || isSubmittingWithdrawal"
                            class="w-2/3 py-2.5 rounded-xl text-xs font-semibold bg-[#059669] hover:bg-emerald-700 disabled:opacity-40 text-white shadow-xs flex items-center justify-center gap-1.5">
                        <span x-show="!isSubmittingWithdrawal">Verifikasi &amp; Simpan</span>
                        <span x-show="isSubmittingWithdrawal" class="material-symbols-outlined text-[14px] animate-spin">progress_activity</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL POP-UP 3: CETAK STRUK BUKTI TRANSAKSI (OVERLAY) -->
        <!-- ========================================== -->
        <div x-show="showReceiptModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-200"
                 @click.away="showReceiptModal = false">
                
                <!-- Printable Thermal Receipt Container -->
                <div id="printable-receipt" class="thermal-slip text-left bg-slate-50 p-5 rounded-2xl border border-dashed border-slate-300 font-mono text-xs text-slate-800 space-y-3">
                    <div class="text-center pb-2 border-b border-dashed border-slate-300">
                        <h4 class="font-bold text-sm tracking-tight text-slate-900">BANK MINI SEKOLAH</h4>
                        <p class="text-[10px] text-slate-500">Struk Transaksi Loket</p>
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
                            <span class="text-slate-500">Petugas:</span>
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

                    <div class="text-center pt-1 text-[10px] text-slate-400">
                        <p>Simpan tanda terima ini sebagai bukti transaksi yang sah.</p>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center gap-2 pt-2 no-print">
                    <button type="button" 
                            @click="showReceiptModal = false; resetTransactionForm();" 
                            class="flex-1 py-2.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Selesai
                    </button>
                    <button type="button" 
                            @click="printReceipt()" 
                            class="flex-1 py-2.5 rounded-xl text-xs font-semibold bg-[#059669] hover:bg-emerald-700 text-white shadow-xs flex items-center justify-center gap-1.5 transition">
                        <span class="material-symbols-outlined text-[16px]">print</span>
                        <span>Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <!-- HTML5 QR Code Scanner Library for Browser Webcam -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function posCounter() {
            return {
                lookupMode: 'saver',
                searchQuery: '',
                isLoadingLookup: false,
                identifiedUser: null,
                
                transactionType: 'deposit',
                amount: 50000,
                notes: '',
                activationPin: '',
                isSopVerified: true,
                
                // PIN Modal
                showPinModal: false,
                withdrawalPin: '',
                isSubmittingWithdrawal: false,

                // Receipt Modal
                showReceiptModal: false,
                lastTransaction: null,

                // Scan Modal
                showScanModal: false,
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
                            return (this.amount || 0) >= 10000;
                        }
                        return (this.amount || 0) > 0;
                    } else {
                        return this.estimatedBalance >= 10000;
                    }
                },

                get balanceValidationMessage() {
                    if (this.transactionType === 'deposit' && this.isMemberMode && (this.amount || 0) < 10000) {
                        return 'Setoran awal untuk aktivasi rekening baru minimal Rp 10.000.';
                    }
                    if (this.transactionType === 'withdrawal' && this.estimatedBalance < 10000) {
                        return 'Penarikan melebihi batas. Saldo minimal mengendap wajib Rp 10.000.';
                    }
                    return '';
                },

                get canSubmitDeposit() {
                    if (!this.identifiedUser || !this.isSopVerified || !this.isBalanceValid || !this.amount) return false;
                    if (this.isMemberMode) {
                        return this.activationPin.length === 6 && /^\d+$/.test(this.activationPin);
                    }
                    return true;
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
                                this.amount = Math.max(this.amount || 0, 10000);
                            }
                        } else {
                            alert(res.message || 'Nasabah tidak ditemukan.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan saat mencari nasabah.');
                    } finally {
                        this.isLoadingLookup = false;
                    }
                },

                async executeDeposit() {
                    if (!this.canSubmitDeposit) return;

                    const payload = {
                        amount: this.amount,
                        notes: this.notes
                    };

                    if (this.isMemberMode) {
                        payload.member_id = this.identifiedUser.id;
                        payload.pin = this.activationPin;
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
                            this.lastTransaction = res.transaction;
                            if (res.transaction.new_drawer_balance !== undefined) {
                                this.currentDrawerBalance = res.transaction.new_drawer_balance;
                            }
                            this.showReceiptModal = true;
                        } else {
                            alert(res.message || 'Setoran gagal diproses.');
                        }
                    } catch (e) {
                        alert('Terjadi gangguan koneksi sistem.');
                    }
                },

                openPinModal() {
                    if (!this.canSubmitWithdrawal) return;
                    this.withdrawalPin = '';
                    this.showPinModal = true;
                },

                pressPinDigit(n) {
                    if (this.withdrawalPin.length < 6) {
                        this.withdrawalPin += n.toString();
                    }
                },

                deletePinDigit() {
                    this.withdrawalPin = this.withdrawalPin.slice(0, -1);
                },

                clearPinDigit() {
                    this.withdrawalPin = '';
                },

                async executeWithdrawal() {
                    if (this.withdrawalPin.length !== 6) return;
                    this.isSubmittingWithdrawal = true;

                    const payload = {
                        account_number: this.identifiedUser.account_number,
                        amount: this.amount,
                        pin: this.withdrawalPin,
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
                            this.lastTransaction = res.transaction;
                            if (res.transaction.new_drawer_balance !== undefined) {
                                this.currentDrawerBalance = res.transaction.new_drawer_balance;
                            }
                            this.showReceiptModal = true;
                        } else {
                            alert(res.message || 'Penarikan gagal: PIN tidak cocok.');
                        }
                    } catch (e) {
                        alert('Terjadi gangguan koneksi sistem.');
                    } finally {
                        this.isSubmittingWithdrawal = false;
                    }
                },

                printReceipt() {
                    window.print();
                },

                resetTransactionForm() {
                    this.clearLookup();
                    this.amount = 50000;
                    this.notes = '';
                    this.activationPin = '';
                    this.withdrawalPin = '';
                },

                openScanModal() {
                    this.showScanModal = true;
                    this.startCameraScanner();
                },

                closeScanModal() {
                    this.stopCameraScanner();
                    this.showScanModal = false;
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
                        return;
                    }

                    this.isCameraScanning = true;
                    this.$nextTick(() => {
                        this.html5QrCode = new Html5Qrcode("qr-reader");
                        this.html5QrCode.start(
                            { facingMode: "environment" },
                            { fps: 10, qrbox: { width: 220, height: 220 } },
                            (decodedText) => {
                                this.closeScanModal();
                                this.performLookup(decodedText);
                            },
                            () => {}
                        ).catch(() => {
                            this.isCameraScanning = false;
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
                            this.closeScanModal();
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
