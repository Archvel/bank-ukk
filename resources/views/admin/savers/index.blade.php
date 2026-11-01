<x-admin-layout title="Master Savers / Rekening" breadcrumb="Rekening Siswa">
    <div x-data="{ 
        resetPinModalOpen: false, 
        currentSaver: { id: null, account_number: '', name: '' },
        openResetPin(saver) {
            this.currentSaver = {
                id: saver.id,
                account_number: saver.account_number,
                name: saver.user ? saver.user.name : (saver.member ? saver.member.student_name : 'Nasabah')
            };
            this.resetPinModalOpen = true;
        }
    }">
        <!-- Page Header (TANPA Tombol Tambah Manual) -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Master Savers / Buku Tabungan Siswa</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pengawasan likuiditas rekening siswa, pengamanan PIN 6-digit, dan audit penonaktifan rekening (SoftDelete).</p>
            </div>
            <!-- Status Badge: Auto-Enrollment Active -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-600 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Auto-Enrollment: Loket Teller</span>
            </div>
        </div>

        <!-- Banner Info Kebijakan Sistem: Tanpa Pembuatan Manual -->
        <div class="mb-6 p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 shadow-xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                <span class="material-symbols-outlined text-2xl">policy</span>
            </div>
            <div class="text-xs text-slate-700 leading-relaxed">
                <p class="font-bold text-blue-900 text-sm mb-0.5">Kebijakan Integritas Pembukaan Rekening:</p>
                <p>Sesuai SOP Perbankan Mini Sekolah, <strong>tidak tersedia tombol pembuatan rekening secara manual di menu Administrator</strong>. Seluruh nomor rekening dan buku tabungan Saver diterbitkan secara otomatis dan terotentikasi saat calon penabung melakukan setoran fisik pertama di loket kasir Teller. Administrator berwenang melakukan audit buku kas, penanganan lupa PIN, serta pemblokiran/penonaktifan (*SoftDelete*).</p>
            </div>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Rekening Terbit</span>
                        <div class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ number_format($totalSavers, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Akumulasi Dana Tabungan</span>
                        <div class="text-2xl font-bold font-mono text-emerald-700 mt-1">Rp {{ number_format($totalSavings, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">payments</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rekening Nonaktif (SoftDelete)</span>
                        <div class="text-2xl font-bold font-mono text-red-600 mt-1">{{ $trashedCount }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">archive</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6">
            <form method="GET" action="{{ route('admin.savers.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Status Aktif</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="dormant" {{ request('status') === 'dormant' ? 'selected' : '' }}>Dormant (Tidak Aktif)</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Terblokir</option>
                        <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>Di-SoftDelete (Arsip Nonaktif)</option>
                    </select>

                    @if(request()->filled('status') || request()->filled('search'))
                        <a href="{{ route('admin.savers.index') }}" class="text-xs text-slate-500 hover:text-red-600 font-medium flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">close</span> Reset
                        </a>
                    @endif
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-80">
                    <span class="material-symbols-outlined text-slate-400 absolute left-3 top-2.5 text-[18px]">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari No. Rekening, Nama Siswa..." 
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border-slate-200 bg-slate-50 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </form>
        </div>

        <!-- Savers Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">No. Rekening & Token</th>
                            <th class="py-3 px-4">Nama Siswa / Nasabah</th>
                            <th class="py-3 px-4">Angkatan</th>
                            <th class="py-3 px-4 text-right">Saldo Tabungan (Rp)</th>
                            <th class="py-3 px-4 text-center">Status Rekening</th>
                            <th class="py-3 px-4">Tgl Terbit</th>
                            <th class="py-3 px-4 text-right">Aksi Administrator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($savers as $saver)
                            <tr class="hover:bg-slate-50/70 transition {{ $saver->trashed() ? 'bg-red-50/30' : '' }}">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-mono font-bold text-xs shrink-0">
                                            <span class="material-symbols-outlined text-base">credit_card</span>
                                        </div>
                                        <div>
                                            <p class="font-mono font-bold text-slate-900 text-sm tracking-tight">{{ $saver->account_number }}</p>
                                            <span class="inline-block text-[10px] text-slate-400 font-mono">{{ $saver->qr_code_token ?? 'QR-NONE' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-900 text-sm">{{ $saver->user->name ?? $saver->member?->student_name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $saver->user->email ?? '-' }}</p>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-[11px] font-semibold">
                                        {{ $saver->member?->graduation_year ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 text-sm tabular-nums">
                                    Rp {{ number_format($saver->balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($saver->trashed())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Soft-Deleted (Arsip)
                                        </span>
                                    @elseif($saver->status->value === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Aktif
                                        </span>
                                    @elseif($saver->status->value === 'blocked')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                            Terblokir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            {{ ucfirst($saver->status->value) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                    {{ $saver->opened_at?->format('d/m/Y') ?? $saver->created_at?->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Mutasi / Detail Button -->
                                        <a href="{{ route('admin.savers.show', $saver->id) }}" 
                                           class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                           title="Lihat Mutasi & Detail Rekening">
                                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                        </a>

                                        @if(! $saver->trashed())
                                            <!-- Reset PIN Button (#F59E0B Amber) -->
                                            <button type="button" 
                                                    @click="openResetPin({{ json_encode($saver) }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-xs transition"
                                                    title="Reset PIN 6-Digit Nasabah">
                                                <span class="material-symbols-outlined text-[16px]">lock_reset</span>
                                                <span>Reset PIN</span>
                                            </button>

                                            <!-- SoftDelete Button (#DC2626 Red) -->
                                            <form method="POST" action="{{ route('admin.savers.destroy', $saver->id) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-700 hover:bg-red-50 transition"
                                                        title="Nonaktifkan Rekening (SoftDelete)"
                                                        onclick="return confirm('Apakah Anda yakin ingin menonaktifkan rekening {{ $saver->account_number }}? Rekening akan di-SoftDelete dan riwayat transaksi tetap tersimpan utuh untuk laporan audit.')">
                                                    <span class="material-symbols-outlined text-[18px] text-red-600">delete</span>
                                                </button>
                                            </form>
                                        @else
                                            <!-- Restore Button -->
                                            <form method="POST" action="{{ route('admin.savers.restore', $saver->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition"
                                                        title="Pulihkan Rekening">
                                                    <span class="material-symbols-outlined text-[16px]">restore_from_trash</span>
                                                    <span>Pulihkan</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data rekening saver yang sesuai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($savers->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $savers->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Reset PIN 6-Digit (Warna Amber #F59E0B) -->
        <div x-show="resetPinModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
             x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200"
                 @click.outside="resetPinModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">lock_reset</span>
                        </div>
                        <h3 class="font-bold text-base text-slate-900">Reset PIN Keamanan 6-Digit</h3>
                    </div>
                    <button type="button" @click="resetPinModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="mb-4 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                    <p class="font-bold text-slate-800" x-text="'No. Rekening: ' + currentSaver.account_number"></p>
                    <p class="text-slate-500" x-text="'Nama Nasabah: ' + currentSaver.name"></p>
                </div>

                <form :action="'{{ url('admin/savers') }}/' + currentSaver.id + '/reset-pin'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Masukkan PIN 6-Digit Baru *</label>
                        <input type="password" 
                               name="new_pin" 
                               required 
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               placeholder="Contoh: 123456" 
                               class="w-full text-center tracking-widest text-lg font-mono font-bold rounded-xl border-slate-200 bg-slate-50 focus:border-amber-500 focus:ring-amber-500">
                        <p class="text-[11px] text-slate-500 mt-1">Harus tepat 6 angka numerik. Siswa akan diminta mengganti PIN saat transaksi berikutnya.</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="resetPinModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-sm transition">
                            Terapkan Reset PIN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
