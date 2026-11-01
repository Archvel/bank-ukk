<x-nasabah-layout title="Buku Tabungan Digital">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-6">
        
        <!-- 1. Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Buku Tabungan Digital</h2>
                <p class="text-xs text-slate-500 mt-0.5">Catatan seluruh transaksi masuk dan keluar rekening siswa secara mandiri (Read-Only &amp; Terverifikasi Audit).</p>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <span class="px-3.5 py-1.5 rounded-full text-xs font-mono font-bold bg-white text-slate-700 border border-slate-200/80 shadow-xs flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>No. Rekening: <strong>{{ $saver->account_number }}</strong></span>
                </span>
            </div>
        </div>

        <!-- 2. 3 Summary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Card 1: Total Setoran -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Uang Masuk (Setoran)</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-700 tabular-nums">
                    Rp {{ number_format($totalDeposit, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-slate-400">
                    Total akumulasi uang disetor ke loket
                </div>
            </div>

            <!-- Card 2: Total Penarikan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Uang Keluar (Penarikan)</span>
                    <div class="w-8 h-8 rounded-xl bg-red-50 text-red-700 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-red-600 tabular-nums">
                    Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-slate-400">
                    Penarikan tunai terotorisasi PIN siswa
                </div>
            </div>

            <!-- Card 3: Saldo Akhir Berjalan -->
            <div class="bg-white rounded-2xl border border-emerald-200 p-5 shadow-xs bg-gradient-to-br from-white to-emerald-50/30">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Saldo Akhir Berjalan</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-700 text-white flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900 tabular-nums">
                    Rp {{ number_format($currentBalance, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    <span>Saldo aktif terverifikasi di buku besar</span>
                </div>
            </div>
        </div>

        <!-- 3. Filter Toolbar & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs space-y-3 no-print">
            <form method="GET" action="{{ route('nasabah.mutations.index') }}" class="space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <!-- Period Filter Pills -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a href="{{ route('nasabah.mutations.index') }}" 
                           class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ (!request()->filled('period') && !request()->filled('start_date') && !request()->filled('type')) ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Semua Waktu
                        </a>
                        <a href="{{ route('nasabah.mutations.index', ['period' => 'month']) }}" 
                           class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ request('period') === 'month' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Bulan Ini
                        </a>
                        <a href="{{ route('nasabah.mutations.index', ['period' => 'week']) }}" 
                           class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ request('period') === 'week' ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            7 Hari Terakhir
                        </a>
                    </div>

                    <!-- Date Range Inputs -->
                    <div class="flex items-center gap-2 text-xs">
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                            <span class="text-slate-400">Dari:</span>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="bg-transparent border-0 p-0 text-xs focus:ring-0 text-slate-700" />
                        </div>
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                            <span class="text-slate-400">Sampai:</span>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="bg-transparent border-0 p-0 text-xs focus:ring-0 text-slate-700" />
                        </div>
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                            Terapkan
                        </button>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <!-- Type Filter Pills -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a href="{{ route('nasabah.mutations.index', request()->except('type')) }}" 
                           class="px-3 py-1 rounded-lg text-xs font-bold transition {{ !request()->filled('type') ? 'bg-[#059669] text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                            Semua Jenis
                        </a>
                        <a href="{{ route('nasabah.mutations.index', array_merge(request()->query(), ['type' => 'deposit'])) }}" 
                           class="px-3 py-1 rounded-lg text-xs font-bold transition {{ request('type') === 'deposit' ? 'bg-[#059669] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Setoran Tunai (+)
                        </a>
                        <a href="{{ route('nasabah.mutations.index', array_merge(request()->query(), ['type' => 'withdrawal'])) }}" 
                           class="px-3 py-1 rounded-lg text-xs font-bold transition {{ request('type') === 'withdrawal' ? 'bg-[#DC2626] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            Penarikan Tunai (-)
                        </a>
                    </div>

                    <!-- Search Input & Print Button -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 sm:w-64">
                            <span class="material-symbols-outlined absolute left-3 top-2 text-slate-400 text-[18px]">search</span>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Cari no. slip / keterangan..." 
                                   class="pl-9 pr-3 py-1.5 text-xs rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 w-full" />
                        </div>

                        <button type="button" 
                                onclick="window.print()" 
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center gap-1.5 transition whitespace-nowrap">
                            <span class="material-symbols-outlined text-[16px]">print</span>
                            <span>Cetak Rekap</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 4. Read-Only Passbook Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Tanggal &amp; Waktu</th>
                            <th class="py-3 px-4">No. Referensi Slip</th>
                            <th class="py-3 px-4">Keterangan / Uraian</th>
                            <th class="py-3 px-4 text-right">Mutasi Masuk (+ Debet)</th>
                            <th class="py-3 px-4 text-right">Mutasi Keluar (- Kredit)</th>
                            <th class="py-3 px-4 text-right">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $trx->created_at?->format('d/m/Y H:i') }} WIB
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    {{ $trx->transaction_number }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    {{ $trx->notes ?: ($trx->type->value === 'deposit' ? 'Setoran Tunai di Loket' : 'Penarikan Tunai di Loket') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold tabular-nums text-emerald-700">
                                    @if($trx->type->value === 'deposit')
                                        +Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold tabular-nums text-red-600">
                                    @if($trx->type->value === 'withdrawal')
                                        -Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-slate-900 font-bold tabular-nums">
                                    Rp {{ number_format($trx->closing_balance, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">receipt_long</span>
                                    <p class="text-sm font-semibold text-slate-600">Belum Ada Riwayat Transaksi</p>
                                    <p class="text-xs text-slate-400 mt-1">Data mutasi setor dan tarik tabungan Anda akan tercatat di sini secara otomatis.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="p-4 border-t border-slate-100 no-print">
                    {{ $transactions->links() }}
                </div>
            @endif

            <!-- Audit Immutability Footer Note -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified_user</span>
                <span>Buku tabungan digital ini merupakan salinan sah yang tersinkronisasi otomatis dengan Buku Besar Akuntansi Bank Mini SMKN 1 dan diawasi oleh Program Keahlian Akuntansi &amp; Keuangan Lembaga.</span>
            </div>
        </div>

    </div>
</x-nasabah-layout>
