<x-admin-layout title="Verifikasi Kas Harian" breadcrumb="Verifikasi Kas">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi & Penutupan Kas Harian</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekonsiliasi pencocokan saldo laci loket vs sistem dan otorisasi pemindahan fisik kas ke Brankas Utama (Akun 102).</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Dual-Control Vault Guard
            </span>
        </div>
    </div>

    <!-- Reconciliation Formula Banner -->
    <div class="mb-6 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Prinsip Rekonsiliasi Kas Otomatis Loket</div>
        <div class="flex flex-wrap items-center gap-3 text-xs font-mono font-medium text-slate-700">
            <span class="px-2.5 py-1.5 rounded-lg bg-slate-100 border border-slate-200 font-bold">Saldo Awal Laci</span>
            <span class="text-emerald-600 font-bold text-base">+</span>
            <span class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">Total Setoran</span>
            <span class="text-red-600 font-bold text-base">-</span>
            <span class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-800 border border-red-200 font-bold">Total Penarikan</span>
            <span class="text-slate-400 font-bold text-base">=</span>
            <span class="px-2.5 py-1.5 rounded-lg bg-slate-900 text-white font-bold">Saldo Sistem</span>
            <span class="text-slate-400 font-bold text-base">vs</span>
            <span class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-900 border border-blue-200 font-bold">Uang Fisik Terhitung</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 mb-6 border-b border-slate-200 pb-2">
        <a href="{{ route('admin.cash-closures.index', ['tab' => 'pending']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'pending' ? 'bg-[#059669] text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span class="material-symbols-outlined text-[18px]">pending_actions</span>
            <span>Menunggu Verifikasi</span>
            @if($pendingCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tab === 'pending' ? 'bg-white text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $pendingCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.cash-closures.index', ['tab' => 'disputes']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'disputes' ? 'bg-red-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span class="material-symbols-outlined text-[18px]">warning</span>
            <span>Selisih / Sengketa</span>
            @if($disputeCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tab === 'disputes' ? 'bg-white text-red-800' : 'bg-red-100 text-red-800' }}">
                    {{ $disputeCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.cash-closures.index', ['tab' => 'history']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'history' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span class="material-symbols-outlined text-[18px]">history</span>
            <span>Riwayat Arsip Otorisasi</span>
        </a>
    </div>

    <!-- Active Tab Content -->
    @if($tab === 'pending')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal & Ref</th>
                            <th class="py-3 px-4">Petugas Teller</th>
                            <th class="py-3 px-4 text-right">Saldo Awal</th>
                            <th class="py-3 px-4 text-right">Total Setoran</th>
                            <th class="py-3 px-4 text-right">Total Penarikan</th>
                            <th class="py-3 px-4 text-right">Saldo Sistem</th>
                            <th class="py-3 px-4 text-right">Fisik di Laci</th>
                            <th class="py-3 px-4 text-center">Status Selisih</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingClosures as $closure)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4">
                                    <p class="font-mono font-bold text-slate-900">{{ $closure->closure_date?->format('d/m/Y') }}</p>
                                    <span class="text-[10px] text-slate-400 font-mono">CLS-{{ str_pad($closure->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $closure->teller->name ?? 'Teller Loket' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono tabular-nums text-slate-600">
                                    Rp {{ number_format($closure->opening_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono tabular-nums text-emerald-700 font-semibold">
                                    +Rp {{ number_format($closure->total_deposit, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono tabular-nums text-red-700 font-semibold">
                                    -Rp {{ number_format($closure->total_withdrawal, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono tabular-nums font-bold text-slate-800">
                                    Rp {{ number_format($closure->system_ending_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono tabular-nums font-bold text-blue-900 bg-blue-50/40">
                                    Rp {{ number_format($closure->physical_ending_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($closure->difference === 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Balance (Klop)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                            Selisih Rp {{ number_format(abs($closure->difference), 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.cash-closures.show', $closure) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-xs transition">
                                        <span class="material-symbols-outlined text-[16px]">verified</span>
                                        <span>Periksa & Otorisasi</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-emerald-500 mb-1">done_all</span>
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada laporan kas loket yang menunggu verifikasi.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($tab === 'disputes')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-red-50/60 border-b border-red-200 text-red-900 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal & Ref</th>
                            <th class="py-3 px-4">Teller Loket</th>
                            <th class="py-3 px-4 text-right">Saldo Sistem</th>
                            <th class="py-3 px-4 text-right">Fisik di Laci</th>
                            <th class="py-3 px-4 text-right">Selisih Uang Fisik</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($disputeClosures as $closure)
                            <tr class="hover:bg-red-50/20 transition">
                                <td class="py-3 px-4 font-mono font-bold">{{ $closure->closure_date?->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $closure->teller->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">Rp {{ number_format($closure->system_ending_balance, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-blue-900">Rp {{ number_format($closure->physical_ending_balance, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-red-700">
                                    {{ $closure->difference > 0 ? '+' : '' }}Rp {{ number_format($closure->difference, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                        {{ ucfirst($closure->status->value) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.cash-closures.show', $closure) }}" class="text-red-700 hover:underline font-semibold">
                                        Investigasi Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl text-emerald-500 mb-1">thumb_up</span>
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada laporan kas berselisih. Pembukuan 100% klop.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- History Tab -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Petugas Teller</th>
                            <th class="py-3 px-4 text-right">Kas Fisik Ditransfer</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Disetujui Oleh</th>
                            <th class="py-3 px-4">Waktu Verifikasi</th>
                            <th class="py-3 px-4 text-right">Bukti Jurnal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($historyClosures as $closure)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold">{{ $closure->closure_date?->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $closure->teller->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                    Rp {{ number_format($closure->physical_ending_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($closure->status->value === 'approved')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Disetujui (Brankas Masuk)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $closure->approver->name ?? 'Administrator' }}</td>
                                <td class="py-3 px-4 font-mono text-slate-400">{{ $closure->approved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.cash-closures.show', $closure) }}" class="text-[#059669] hover:underline font-semibold">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat penutupan kas yang tersimpan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historyClosures->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $historyClosures->links() }}
                </div>
            @endif
        </div>
    @endif
</x-admin-layout>
