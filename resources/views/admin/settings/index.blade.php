<x-admin-layout title="Konfigurasi Sistem" breadcrumb="Sistem / Konfigurasi">
    <div x-data="{
        cameraActive: false,
        stream: null,
        cameraError: null,
        autoSubmit: true,
        beepSound: true,
        async startCamera() {
            this.cameraError = null;
            if (this.cameraActive) {
                this.stopCamera();
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                this.stream = stream;
                this.$refs.videoPreview.srcObject = stream;
                this.cameraActive = true;
            } catch (err) {
                this.cameraError = 'Tidak dapat mengakses kamera: ' + (err.message || 'Izin kamera ditolak.');
                this.cameraActive = false;
            }
        },
        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.cameraActive = false;
        }
    }">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Konfigurasi Parameter Sistem</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pengaturan batasan keuangan, integrasi perangkat keras scanner loket, dan hak akses keamanan RBAC.</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-md shadow-emerald-950/20 transition">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Simpan Seluruh Pengaturan</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- System Health Ribbon -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Versi Core Sistem</span>
                <p class="text-sm font-bold text-slate-900 mt-0.5">Laravel 12 (Breeze + Alpine)</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Engine Database</span>
                <p class="text-sm font-bold text-emerald-600 mt-0.5">● MySQL/MariaDB Terkoneksi</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hardware Scanner QR</span>
                <p class="text-sm font-bold text-blue-600 mt-0.5">● WebCam USB Siap</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-3.5 shadow-xs">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kernel RBAC Guard</span>
                <p class="text-sm font-bold text-emerald-600 mt-0.5">● role:admin & teller Aktif</p>
            </div>
        </div>

        <!-- 3 Main Configuration Cards Grid -->
        <div class="space-y-6">
            <!-- Card 1: Parameter Keuangan & Rekening Siswa -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Card 1: Parameter Keuangan & Rekening Siswa</h2>
                        <p class="text-xs text-slate-500">Batasan saldo wajib, ketentuan setoran awal loket, dan aturan penomoran buku tabungan siswa.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <!-- Field 1: Batas Saldo Mengendap Minimum (Locked) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="font-bold text-slate-700">Batas Saldo Mengendap Minimum (Locked)</label>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                Wajib Sistem (Terkunci)
                            </span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 font-bold font-mono text-slate-500">Rp</span>
                            <input type="text" 
                                   value="10.000" 
                                   readonly 
                                   class="w-full pl-10 pr-4 py-2 text-xs font-mono font-bold rounded-xl border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Saldo proteksi wajib agar buku tabungan tidak defisit atau ditutup sepihak.</p>
                    </div>

                    <!-- Field 2: Batas Setoran Awal Aktivasi Member -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="font-bold text-slate-700">Setoran Awal Minimal Pembukaan Rekening</label>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Syarat Penerbitan Saver
                            </span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 font-bold font-mono text-slate-500">Rp</span>
                            <input type="text" 
                                   value="10.000" 
                                   class="w-full pl-10 pr-4 py-2 text-xs font-mono font-bold rounded-xl border-slate-200 bg-slate-50 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Nominal setoran fisik pertama yang otomatis menerbitkan nomor rekening Saver oleh teller.</p>
                    </div>

                    <!-- Field 3: Format Penomoran Rekening Otomatis -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Algoritma Penomoran Rekening Otomatis</label>
                        <input type="text" 
                               value="BM{TahunAjaran}{NomorUrutSiswa}" 
                               readonly 
                               class="w-full px-3.5 py-2 text-xs font-mono font-bold rounded-xl border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed">
                        <p class="text-[11px] text-emerald-700 mt-1 font-mono font-semibold">Contoh hasil generate: BM20260001, BM20260002</p>
                    </div>

                    <!-- Field 4: Limit Penarikan Harian Loket -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Batas Penarikan Harian Siswa Tanpa Otorisasi Tambahan</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 font-bold font-mono text-slate-500">Rp</span>
                            <input type="text" 
                                   value="500.000" 
                                   class="w-full pl-10 pr-4 py-2 text-xs font-mono font-bold rounded-xl border-slate-200 bg-slate-50 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Penarikan di atas limit ini mewajibkan konfirmasi ganda di loket kasir.</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Integrasi Perangkat Keras (QR Code Scanner / Webcam) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Card 2: Integrasi Perangkat Keras (QR Code Scanner / Webcam)</h2>
                        <p class="text-xs text-slate-500">Pengaturan modul kamera loket kasir untuk autentikasi cepat kartu tabungan siswa.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <!-- Status & Device Info -->
                    <div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-700">Status Perangkat Kamera Loket:</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                    <span>Terhubung / Siap Memindai</span>
                                </span>
                            </div>
                            <p class="text-slate-500">Driver: <strong class="text-slate-700">Standard USB Video Class (Webcam / Scanner)</strong></p>
                            <p class="text-slate-500">Resolusi Default: <strong class="text-slate-700">1080p @ 30 FPS Auto-Focus</strong></p>
                        </div>

                        <!-- Toggle Switches -->
                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                <div>
                                    <span class="font-bold text-slate-800 block">Auto-Submit Setelah QR Terdeteksi</span>
                                    <span class="text-[11px] text-slate-400">Langsung cari nasabah tanpa perlu menekan tombol Enter di loket</span>
                                </div>
                                <input type="checkbox" x-model="autoSubmit" class="rounded text-emerald-600 focus:ring-emerald-500">
                            </label>

                            <label class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                <div>
                                    <span class="font-bold text-slate-800 block">Bunyi Beep Konfirmasi Pindai</span>
                                    <span class="text-[11px] text-slate-400">Putar notifikasi audio saat kartu pelajar berhasil terbaca</span>
                                </div>
                                <input type="checkbox" x-model="beepSound" class="rounded text-emerald-600 focus:ring-emerald-500">
                            </label>
                        </div>

                        <div class="mt-4">
                            <button type="button" 
                                    @click="startCamera()"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition">
                                <span class="material-symbols-outlined text-[18px]" x-text="cameraActive ? 'videocam_off' : 'videocam'"></span>
                                <span x-text="cameraActive ? 'Matikan Kamera Preview' : 'Tes Kamera / Scanner Sekarang'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Interactive Camera Viewfinder Preview -->
                    <div>
                        <div class="relative w-full h-56 rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden flex items-center justify-center">
                            <!-- Live Video Stream -->
                            <video x-ref="videoPreview" autoplay playsinline muted class="w-full h-full object-cover" x-show="cameraActive"></video>

                            <!-- Placeholder when inactive -->
                            <div x-show="!cameraActive" class="text-center p-4">
                                <span class="material-symbols-outlined text-4xl text-slate-600 mb-2">photo_camera</span>
                                <p class="text-slate-400 text-xs font-semibold">Preview Kamera Standby</p>
                                <p class="text-slate-600 text-[10px] mt-1">Klik tombol 'Tes Kamera' untuk memeriksa tangkapan lensa pemindai loket.</p>
                            </div>

                            <!-- Scanner Targeting Frame & Laser Overlay -->
                            <div x-show="cameraActive" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                <div class="w-40 h-40 border-2 border-emerald-400 rounded-2xl relative shadow-lg shadow-emerald-500/20">
                                    <div class="absolute inset-x-0 top-0 h-0.5 bg-emerald-400 animate-pulse"></div>
                                    <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-[9px] font-bold text-emerald-400 bg-slate-900/80 px-2 py-0.5 rounded">
                                        ARAHKAN QR KARTU
                                    </span>
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div x-show="cameraError" class="absolute inset-x-4 bottom-4 p-2.5 rounded-lg bg-red-900/90 text-white text-[11px] text-center" x-text="cameraError"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Keamanan & Hak Akses (RBAC Guard) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">shield</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Card 3: Keamanan & Hak Akses (RBAC Guard)</h2>
                        <p class="text-xs text-slate-500">Kebijakan proteksi sesi, enkripsi PIN nasabah, dan penguncian hak akses role.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                    <!-- Policy 1: PIN 6-Digit Protection -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">Proteksi PIN 6-Digit</span>
                        <div class="text-[11px] text-slate-600 space-y-1">
                            <p>Enkripsi: <strong class="text-slate-800">One-Way Bcrypt (Cost 12)</strong></p>
                            <p>Toleransi Salah PIN: <strong class="text-amber-700">Maksimal 3x</strong></p>
                            <p>Tindakan: <span class="text-red-700 font-semibold">Kunci sementara & tiket reset PIN Administrator</span></p>
                        </div>
                    </div>

                    <!-- Policy 2: Session Lifetime & Idle Timeout -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">Durasi Sesi & Idle Timeout</span>
                        <div class="text-[11px] text-slate-600 space-y-1">
                            <p>Sesi Loket Teller: <strong class="text-slate-800">15 Menit Idle Logout</strong></p>
                            <p>Proteksi CSRF: <strong class="text-emerald-700">Aktif di Seluruh Form POST</strong></p>
                            <p>Cookie Guard: <strong class="text-slate-800">HttpOnly + SameSite Lax</strong></p>
                        </div>
                    </div>

                    <!-- Policy 3: RBAC Middleware Kernel -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="font-bold text-slate-900 block mb-1">Kernel Middleware RBAC</span>
                        <div class="text-[11px] text-slate-600 space-y-1">
                            <p><span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span> <strong class="text-slate-800">role:admin</strong> - Otorisasi Penuh</p>
                            <p><span class="inline-block w-2 h-2 rounded-full bg-blue-500 mr-1"></span> <strong class="text-slate-800">role:teller</strong> - Khusus Loket Kasir</p>
                            <p><span class="inline-block w-2 h-2 rounded-full bg-purple-500 mr-1"></span> <strong class="text-slate-800">role:nasabah</strong> - Cek Saldo & Mutasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
