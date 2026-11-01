<x-nasabah-layout title="Keamanan & Reset PIN">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-6" x-data="pinResetManager()">
        
        <!-- 1. Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                    Keamanan Akun &amp; Otorisasi Transaksi Loket
                </span>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-2">Pusat Keamanan &amp; Reset PIN</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola otorisasi keamanan penarikan loket dan pemulihan PIN 6-digit akun rekening siswa Anda.</p>
            </div>

            <div class="shrink-0">
                <span class="px-3.5 py-1.5 rounded-full text-xs font-mono font-bold bg-white text-slate-700 border border-slate-200/80 shadow-xs flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>No. Rekening: <strong>{{ $saver->account_number }}</strong></span>
                </span>
            </div>
        </div>

        <!-- 2. Security Status Overview Banner -->
        <div class="rounded-3xl p-6 sm:p-7 text-white bg-gradient-to-r from-slate-900 via-slate-900 to-emerald-950 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6 shadow-md relative overflow-hidden">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-950/80 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0 shadow-inner">
                    <span class="material-symbols-outlined text-3xl">shield_lock</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h4 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-emerald-300">
                            Status Otorisasi Penarikan Loket: TERLINDUNGI PIN 6-DIGIT
                        </h4>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        PIN Anda tersimpan dengan enkripsi satu arah (Bcrypt). Setiap penarikan uang tunai di loket Teller wajib memasukkan 6-digit PIN ini pada layar konfirmasi/keypad loket.
                    </p>
                </div>
            </div>

            <div class="shrink-0 bg-slate-800/80 border border-slate-700/80 px-4 py-2.5 rounded-2xl text-center text-xs text-slate-300">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Metode Enkripsi</span>
                <span class="font-mono font-bold text-white text-sm">Bcrypt Hash (256-Bit)</span>
            </div>
        </div>

        <!-- 3. Two-Column Layout (Grid 2/3 and 1/3) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Left Column: Form Reset / Lupa PIN (2/3 width) -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">lock_reset</span>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Formulir Pemulihan &amp; Pembaruan PIN</h3>
                        <p class="text-xs text-slate-400">Ikuti 2 langkah verifikasi di bawah ini sesuai standar SOP Bank Mini SMKN 1:</p>
                    </div>
                </div>

                <!-- LANGKAH 1: Dapatkan / Input Token Otorisasi -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5 uppercase tracking-wider">
                            <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px]">1</span>
                            <span>Langkah 1: Verifikasi Token Otorisasi</span>
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">Berlaku 24 Jam</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Rekening Nasabah</label>
                            <input type="text" 
                                   value="{{ $saver->account_number }}" 
                                   readonly 
                                   class="w-full text-xs font-mono font-bold bg-slate-100/80 border border-slate-200 rounded-xl py-2.5 px-3 text-slate-700 cursor-not-allowed" />
                        </div>
                        <div class="pt-5 sm:pt-0">
                            <!-- Button to request token -->
                            <form method="POST" action="{{ route('nasabah.pin.request') }}">
                                @csrf
                                <input type="hidden" name="account_number" value="{{ $saver->account_number }}" />
                                <button type="submit" 
                                        class="w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-xs flex items-center justify-center gap-2 transition active:scale-98">
                                    <span class="material-symbols-outlined text-[16px]">vpn_key</span>
                                    <span>Dapatkan Token Reset Baru</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- LANGKAH 2: Form Input PIN Baru & Konfirmasi -->
                <form method="POST" action="{{ route('nasabah.pin.confirm') }}" class="space-y-5">
                    @csrf

                    <!-- Input Token Otorisasi -->
                    <div>
                        <label for="reset_token" class="block text-xs font-bold text-slate-700 mb-1">
                            Token Reset (8 Karakter Huruf/Angka) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="reset_token"
                               name="reset_token" 
                               value="{{ old('reset_token', session('generated_token')) }}"
                               placeholder="Contoh: RST8X92K" 
                               maxlength="8"
                               required
                               class="w-full font-mono text-sm tracking-widest uppercase font-bold rounded-xl border-slate-200 bg-white focus:border-emerald-500 focus:ring-emerald-500 py-2.5 px-3.5 shadow-xs" />
                        <p class="text-[11px] text-slate-400 mt-1">
                            Masukkan token yang diperoleh dari tombol di Langkah 1 atau petugas loket teller.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5 uppercase tracking-wider">
                            <span class="w-5 h-5 rounded-full bg-[#059669] text-white flex items-center justify-center text-[10px]">2</span>
                            <span>Langkah 2: Tentukan 6-Digit PIN Baru</span>
                        </span>

                        <!-- New PIN Field -->
                        <div>
                            <label for="new_pin" class="block text-xs font-bold text-slate-700 mb-1">
                                PIN Keamanan Baru (6 Digit Angka) <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="new_pin"
                                   name="new_pin" 
                                   x-model="newPin"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   inputmode="numeric"
                                   placeholder="Contoh: 849201" 
                                   required
                                   class="w-full font-mono text-lg tracking-widest text-center font-bold rounded-xl border-slate-200 bg-white focus:border-emerald-500 focus:ring-emerald-500 py-2.5 shadow-xs" />
                        </div>

                        <!-- Confirm New PIN Field -->
                        <div>
                            <label for="new_pin_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                                Konfirmasi PIN Keamanan Baru <span class="text-red-500">*</span>
                            </label>
                            <input type="password" 
                                   id="new_pin_confirmation"
                                   name="new_pin_confirmation" 
                                   x-model="confirmPin"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   inputmode="numeric"
                                   placeholder="Ulangi 6 digit PIN baru" 
                                   required
                                   class="w-full font-mono text-lg tracking-widest text-center font-bold rounded-xl border-slate-200 bg-white focus:border-emerald-500 focus:ring-emerald-500 py-2.5 shadow-xs" />
                        </div>

                        <!-- Real-Time Validation Indicators -->
                        <div class="space-y-1.5 pt-2">
                            <div class="flex items-center gap-2 text-xs font-semibold" :class="isSixDigits ? 'text-emerald-700' : 'text-slate-400'">
                                <span class="material-symbols-outlined text-[16px]" x-text="isSixDigits ? 'check_circle' : 'radio_button_unchecked'"></span>
                                <span>Tepat 6 digit angka numerik</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-semibold" :class="isNotSequential ? 'text-emerald-700' : 'text-slate-400'">
                                <span class="material-symbols-outlined text-[16px]" x-text="isNotSequential ? 'check_circle' : 'radio_button_unchecked'"></span>
                                <span>Bukan angka berulang atau berurutan (misal: 111111 / 123456)</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-semibold" :class="isMatch ? 'text-emerald-700' : 'text-slate-400'">
                                <span class="material-symbols-outlined text-[16px]" x-text="isMatch ? 'check_circle' : 'radio_button_unchecked'"></span>
                                <span>PIN baru dan Konfirmasi cocok</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="!canSubmit"
                            class="w-full py-3.5 px-6 rounded-xl text-xs font-bold text-white shadow-md flex items-center justify-center gap-2 transition"
                            :class="canSubmit ? 'bg-[#059669] hover:bg-emerald-700 cursor-pointer active:scale-98' : 'bg-slate-300 cursor-not-allowed'">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                        <span>Simpan &amp; Perbarui PIN Keamanan</span>
                    </button>

                    <p class="text-center text-[10px] text-slate-400 pt-1">
                        Enkripsi SSL 256-Bit • Perubahan PIN akan segera berlaku untuk transaksi di seluruh loket teller sekolah.
                    </p>
                </form>
            </div>

            <!-- Right Column: Security Guidance & Recent Reset Audit Log (1/3 width) -->
            <div class="space-y-6">
                <!-- Card 1: Pedoman Keamanan PIN Siswa -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">verified_user</span>
                        </div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Pedoman Keamanan PIN Siswa</h4>
                    </div>

                    <ul class="text-xs text-slate-600 space-y-2.5 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-[16px] shrink-0 mt-0.5">check</span>
                            <span>Jangan pernah membagikan 6-digit PIN kepada teman, wali kelas, atau pihak luar.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-[16px] shrink-0 mt-0.5">check</span>
                            <span>Petugas Teller <strong>TIDAK PERNAH</strong> meminta PIN Anda. Anda menginputnya sendiri pada keypad loket.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-[16px] shrink-0 mt-0.5">check</span>
                            <span>Jika merasa PIN Anda diketahui orang lain, segera lakukan reset PIN di halaman ini.</span>
                        </li>
                    </ul>

                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80 text-[11px] text-amber-900 leading-snug">
                        <strong>Tips:</strong> Hindari menggunakan tanggal lahir atau nomor induk siswa (NISN) sebagai PIN demi keamanan saldo Anda.
                    </div>
                </div>

                <!-- Card 2: Riwayat Permohonan Reset PIN Terakhir -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">history</span>
                        </div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">Riwayat Permohonan Reset</h4>
                    </div>

                    <div class="divide-y divide-slate-100 text-xs">
                        @forelse($recentRequests as $req)
                            <div class="py-2.5 flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-mono font-bold text-slate-800">{{ $req->reset_token }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $req->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $req->status->value === 'completed' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                    {{ ucfirst($req->status->value) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-3 text-center">Belum ada riwayat permohonan reset PIN.</p>
                        @endforelse
                    </div>

                    <p class="text-[10px] text-slate-400">
                        Dicatat dalam log audit keamanan sistem Bank Mini SMKN 1 &amp; tersinkronisasi.
                    </p>
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        function pinResetManager() {
            return {
                newPin: '',
                confirmPin: '',

                get isSixDigits() {
                    return /^[0-9]{6}$/.test(this.newPin);
                },

                get isNotSequential() {
                    if (!this.isSixDigits) return false;
                    const badCombinations = ['123456', '654321', '000000', '111111', '222222', '333333', '444444', '555555', '666666', '777777', '888888', '999999'];
                    return !badCombinations.includes(this.newPin);
                },

                get isMatch() {
                    return this.isSixDigits && this.newPin === this.confirmPin;
                },

                get canSubmit() {
                    return this.isSixDigits && this.isNotSequential && this.isMatch;
                }
            };
        }
    </script>
    @endpush
</x-nasabah-layout>
