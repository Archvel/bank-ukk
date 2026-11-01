<x-admin-layout title="Audit Jurnal Akuntansi" breadcrumb="Akuntansi / Audit Jurnal">
    <!-- Page Header (Read-Only Mode) -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Audit Jurnal Akuntansi (Double-Entry)</h1>
            <p class="text-sm text-slate-500 mt-0.5">Pengawasan penuh buku besar keuangan dan validasi keseimbangan debit vs kredit secara real-time.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                <span class="w-2 h-2 rounded-full bg-[#22C55E] animate-pulse"></span>
                <span>Audit Read-Only Mode</span>
            </span>
        </div>
    </div>

    <!-- 4 Balance Indicator Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Card 1: Total Debet -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Debet Buku Besar</span>
            <div class="text-2xl font-bold font-mono text-emerald-700 tabular-nums mt-1">
                Rp {{ number_format($totalDebit, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Aset Kas Teller (101) & Brankas (102)</p>
        </div>

        <!-- Card 2: Total Kredit -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Kredit Buku Besar</span>
            <div class="text-2xl font-bold font-mono text-emerald-700 tabular-nums mt-1">
                Rp {{ number_format($totalCredit, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Liabilitas Simpanan Siswa (201)</p>
        </div>

        <!-- Card 3: Hero Balance Status Card -->
        <div class="rounded-2xl border p-5 shadow-xs {{ $isBalanced ? 'bg-[#F0FDF4] border-emerald-300' : 'bg-red-50 border-red-300' }}">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold uppercase tracking-wider {{ $isBalanced ? 'text-emerald-800' : 'text-red-800' }}">
                    Keseimbangan Buku
                </span>
                <span class="material-symbols-outlined {{ $isBalanced ? 'text-[#22C55E]' : 'text-red-600' }} text-xl">
                    {{ $isBalanced ? 'check_circle' : 'error' }}
                </span>
            </div>
            @if($isBalanced)
                <div class="text-xl font-black text-[#22C55E] tracking-tight">
                    BALANCE (SEIMBANG)
                </div>
                <p class="text-[11px] text-emerald-700 mt-1 font-medium">Selisih: Rp 0 (0.00%) • Sempurna</p>
            @else
                <div class="text-xl font-black text-red-600 tracking-tight">
                    TIDAK SEIMBANG
                </div>
                <p class="text-[11px] text-red-700 mt-1 font-medium">Selisih: Rp {{ number_format($difference, 0, ',', '.') }}</p>
            @endif
        </div>

        <!-- Card 4: Total Baris Jurnal -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Baris Terjurnal</span>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">
                {{ number_format($totalEntriesCount, 0, ',', '.') }} <span class="text-sm font-normal text-slate-400">Baris</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Double-Entry Berpasangan</p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6">
        <form method="GET" action="{{ route('admin.audit-journals.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <!-- Date Filter -->
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}" 
                       onchange="this.form.submit()" 
                       class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">

                <!-- Account Filter -->
                <select name="account_code" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Akun Buku Besar</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->code }}" {{ request('account_code') === $acc->code ? 'selected' : '' }}>
                            {{ $acc->code }} - {{ $acc->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Type Filter -->
                <select name="type" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Posisi Debet & Kredit</option>
                    <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Hanya Debet</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Hanya Kredit</option>
                </select>

                @if(request()->filled('date') || request()->filled('account_code') || request()->filled('type') || request()->filled('search'))
                    <a href="{{ route('admin.audit-journals.index') }}" class="text-xs text-slate-500 hover:text-red-600 font-medium flex items-center gap-1">
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
                       placeholder="Cari Ref TRX, No Jurnal, Uraian..." 
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border-slate-200 bg-slate-50 placeholder-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            </div>
        </form>
    </div>

    <!-- Journal Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Waktu & Tanggal</th>
                        <th class="py-3.5 px-4">No. Jurnal</th>
                        <th class="py-3.5 px-4">Ref Transaksi</th>
                        <th class="py-3.5 px-4">Kode & Nama Akun</th>
                        <th class="py-3.5 px-4 text-right">Debet (Rp)</th>
                        <th class="py-3.5 px-4 text-right">Kredit (Rp)</th>
                        <th class="py-3.5 px-4">Uraian Transaksi</th>
                        <th class="py-3.5 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($entries as $entry)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono text-slate-500">
                                {{ $entry->entry_date?->format('d/m/Y') }}
                                <span class="text-[10px] text-slate-400 block">{{ $entry->created_at?->format('H:i:s') }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $entry->entry_number }}
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold">
                                @if($entry->transaction)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                        {{ $entry->transaction->transaction_number }}
                                    </span>
                                @elseif($entry->cashClosure)
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                                        CLS-{{ str_pad($entry->cashClosure->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold px-1.5 py-0.5 rounded text-[11px] bg-slate-100 text-slate-800">
                                        {{ $entry->account_code }}
                                    </span>
                                    <span class="font-semibold text-slate-800 truncate max-w-[180px]">{{ $entry->account->name ?? 'Akun' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold tabular-nums text-emerald-700">
                                {{ $entry->type->value === 'debit' ? 'Rp '.number_format($entry->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold tabular-nums text-slate-800">
                                {{ $entry->type->value === 'credit' ? 'Rp '.number_format($entry->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate">
                                {{ $entry->description }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $entry->transaction?->teller?->name ?? $entry->cashClosure?->teller?->name ?? 'Administrator' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada baris jurnal yang sesuai dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Table Footer Reconciliation Summary -->
                <tfoot class="bg-slate-50 border-t-2 border-slate-300 font-semibold text-xs">
                    <tr>
                        <td colspan="4" class="py-3.5 px-4 text-slate-700 font-bold">
                            Total Ringkasan Halaman Ini:
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-700">
                            Rp {{ number_format($entries->where('type.value', 'debit')->sum('amount'), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800">
                            Rp {{ number_format($entries->where('type.value', 'credit')->sum('amount'), 0, ',', '.') }}
                        </td>
                        <td colspan="2" class="py-3.5 px-4 text-right">
                            @if($isBalanced)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-[#F0FDF4] text-[#22C55E] border border-emerald-300">
                                    <span class="material-symbols-outlined text-[16px]">verified</span>
                                    <span>SEIMBANG (0 KESALAHAN)</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    <span>SELISIH: Rp {{ number_format($difference, 0, ',', '.') }}</span>
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($entries->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
