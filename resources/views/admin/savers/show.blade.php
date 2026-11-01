<x-admin-layout title="Detail Rekening Saver" breadcrumb="Rekening Siswa / Detail">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.savers.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Rekening {{ $saver->account_number }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">Buku tabungan resmi milik {{ $saver->user->name ?? $saver->member?->student_name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($saver->trashed())
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                    Akun Di-SoftDelete (Arsip)
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Status Rekening: Aktif
                </span>
            @endif
        </div>
    </div>

    <!-- Saver Info Header Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Account Card (Fintech Style) -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-3xl p-6 shadow-xl relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -right-6 -bottom-6 w-36 h-36 rounded-full bg-emerald-500/10 blur-xl pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs uppercase font-mono tracking-widest text-slate-400">PASBOOK DIGITAL</span>
                    <span class="material-symbols-outlined text-emerald-400 text-2xl">account_balance</span>
                </div>
                <div class="font-mono text-2xl font-bold tracking-widest text-emerald-400 mb-1">
                    {{ $saver->account_number }}
                </div>
                <div class="text-xs text-slate-400 font-mono">
                    Token: {{ $saver->qr_code_token ?? 'QR-TOKEN' }}
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-800 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">Pemilik Rekening</p>
                    <p class="text-sm font-bold text-white">{{ $saver->user->name ?? $saver->member?->student_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">Saldo Tersimpan</p>
                    <p class="text-base font-bold font-mono text-white">Rp {{ number_format($saver->balance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Meta Details Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-semibold text-[10px]">Nama Siswa (Member)</span>
                <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $saver->member?->student_name ?? '-' }}</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold text-[10px]">Tahun Angkatan</span>
                <p class="font-bold text-slate-800 text-sm mt-0.5">Tahun {{ $saver->member?->graduation_year ?? '-' }}</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold text-[10px]">Email Siswa</span>
                <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $saver->user?->email ?? '-' }}</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold text-[10px]">No. Telepon / WA</span>
                <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $saver->user?->phone_number ?? '-' }}</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold text-[10px]">Tanggal Pembukaan Rekening</span>
                <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $saver->opened_at?->translatedFormat('d F Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-semibold text-[10px]">Batas Minimum Saldo Mengendap</span>
                <p class="font-bold text-emerald-700 text-sm mt-0.5 font-mono">Rp 10.000 (Terkunci)</p>
            </div>
        </div>
    </div>

    <!-- Mutasi Transaksi Saver -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-base text-slate-900">Riwayat Mutasi Tabungan</h2>
            <span class="text-xs text-slate-500 font-medium">Menampilkan 20 transaksi terbaru</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">No. Bukti Transaksi</th>
                        <th class="py-3 px-4">Jenis Mutasi</th>
                        <th class="py-3 px-4 text-right">Debet (Keluar)</th>
                        <th class="py-3 px-4 text-right">Kredit (Masuk)</th>
                        <th class="py-3 px-4 text-right">Saldo Akhir</th>
                        <th class="py-3 px-4">Teller</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($saver->transactions as $trx)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 text-slate-500 font-mono">{{ $trx->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-700">{{ $trx->transaction_number }}</td>
                            <td class="py-3 px-4">
                                @if($trx->type->value === 'deposit')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Setoran</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Penarikan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-red-700 font-bold tabular-nums">
                                {{ $trx->type->value === 'withdrawal' ? 'Rp '.number_format($trx->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-700 font-bold tabular-nums">
                                {{ $trx->type->value === 'deposit' ? 'Rp '.number_format($trx->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-900 font-bold tabular-nums">
                                Rp {{ number_format($trx->closing_balance, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $trx->teller->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada mutasi transaksi pada rekening ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
