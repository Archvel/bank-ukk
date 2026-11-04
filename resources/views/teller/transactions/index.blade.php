<x-teller-layout title="Riwayat Transaksi Hari Ini">
    <div class="max-w-[1700px] mx-auto p-4 lg:p-6 space-y-6" x-data="transactionHistory()">
        
        <!-- 1. Top KPI Summary Tiles (4 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Transaksi -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Transaksi Hari Ini</span>
                    <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-slate-900 tabular-nums">
                    {{ $totalTransactionsCount }} <span class="text-xs font-normal text-slate-500">Trx</span>
                </div>
                <div class="mt-2 text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    <span>100% Tercatat di Buku Besar</span>
                </div>
            </div>

            <!-- Card 2: Uang Masuk (Setoran) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Uang Masuk (Setoran)</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">arrow_downward</span>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-emerald-700 tabular-nums">
                    Rp {{ number_format($totalDepositAmount, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Kas masuk dari loket siswa
                </div>
            </div>

            <!-- Card 3: Uang Keluar (Penarikan) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Uang Keluar (Penarikan)</span>
                    <div class="w-8 h-8 rounded-xl bg-red-50 text-red-700 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono text-red-600 tabular-nums">
                    Rp {{ number_format($totalWithdrawalAmount, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Pengambilan tunai terverifikasi PIN
                </div>
            </div>

            <!-- Card 4: Arus Kas Bersih -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Arus Kas Bersih (Net Inflow)</span>
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    </div>
                </div>
                <div class="text-2xl font-extrabold font-mono {{ $netCashFlow >= 0 ? 'text-emerald-700' : 'text-red-600' }} tabular-nums">
                    {{ $netCashFlow >= 0 ? '+' : '' }}Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Surplus / defisit kas laci hari ini
                </div>
            </div>
        </div>

        <!-- 2. Audit Security Policy Notice Banner -->
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-white flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 text-amber-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">lock_clock</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Kebijakan Integritas Keuangan &amp; Audit Immutability</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Sesuai SOP Bank Mini, transaksi loket yang telah divalidasi <strong>TERKUNCI secara permanen</strong>. Teller dilarang keras mengubah (Edit) atau menghapus (Delete) data transaksi. Seluruh koreksi jurnal harus melalui Otorisasi Supervisor Kasir.
                    </p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold bg-emerald-950 text-emerald-400 border border-emerald-800/60 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>AUDIT: LOCKED &amp; SYNC</span>
                </span>
            </div>
        </div>

        <!-- 3. Filter Toolbar & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 no-print">
            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('teller.transactions.index') }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !request()->filled('type') ? 'bg-[#0F172A] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Transaksi ({{ $totalTransactionsCount }})
                </a>
                <a href="{{ route('teller.transactions.index', ['type' => 'deposit']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('type') === 'deposit' ? 'bg-[#059669] text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    Setoran Tunai
                </a>
                <a href="{{ route('teller.transactions.index', ['type' => 'withdrawal']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ request('type') === 'withdrawal' ? 'bg-[#DC2626] text-white shadow-xs' : 'bg-red-50 text-red-800 hover:bg-red-100' }}">
                    Penarikan Tunai
                </a>
            </div>

            <!-- Search Bar & Actions -->
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('teller.transactions.index') }}" class="relative flex items-center">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}" />
                    @endif
                    <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[18px]">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Cari Slip (TXN-...) / Nama / Rekening..." 
                           class="pl-9 pr-4 py-2 text-xs rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 font-mono w-56 lg:w-64" />
                </form>

                <button type="button" 
                        onclick="window.print()" 
                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-xs flex items-center gap-1.5 transition">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Cetak Rekap [Ctrl+P]</span>
                </button>
            </div>
        </div>

        <!-- 4. Read-Only Transaction Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">No. Referensi Slip</th>
                            <th class="py-3 px-4">Rekening &amp; Nasabah</th>
                            <th class="py-3 px-4">Jenis Transaksi</th>
                            <th class="py-3 px-4 text-right">Nominal (Rp)</th>
                            <th class="py-3 px-4 text-right">Saldo Akhir</th>
                            <th class="py-3 px-4 text-center">Status Audit</th>
                            <th class="py-3 px-4 text-center no-print">Aksi Struk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Waktu -->
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $trx->created_at?->format('H:i:s') }} <span class="text-[10px] text-slate-400">WIB</span>
                                </td>

                                <!-- No. Referensi Slip -->
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    {{ $trx->transaction_number }}
                                </td>

                                <!-- Rekening & Nasabah -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">
                                        {{ $trx->saver?->user?->name ?? $trx->saver?->member?->student_name ?? 'Nasabah Siswa' }}
                                    </div>
                                    <span class="text-[11px] font-mono text-slate-500">
                                        {{ $trx->saver?->account_number ?? '-' }}
                                    </span>
                                </td>

                                <!-- Jenis Transaksi -->
                                <td class="py-3.5 px-4">
                                    @if($trx->type->value === 'deposit')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="material-symbols-outlined text-[14px]">arrow_downward</span>
                                            <span>SETORAN TUNAI</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-800 border border-red-200">
                                            <span class="material-symbols-outlined text-[14px]">arrow_upward</span>
                                            <span>PENARIKAN TUNAI</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Nominal -->
                                <td class="py-3.5 px-4 text-right font-mono font-bold tabular-nums {{ $trx->type->value === 'deposit' ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $trx->type->value === 'deposit' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>

                                <!-- Saldo Akhir -->
                                <td class="py-3.5 px-4 text-right font-mono text-slate-900 tabular-nums">
                                    Rp {{ number_format($trx->closing_balance, 0, ',', '.') }}
                                </td>

                                <!-- Status Audit -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <span class="material-symbols-outlined text-[12px] text-amber-500">lock</span>
                                        <span>Terkunci (Audit)</span>
                                    </span>
                                </td>

                                <!-- Aksi Struk (Single Action only: Cetak Ulang Struk) -->
                                <td class="py-3.5 px-4 text-center no-print">
                                    <button type="button" 
                                            @click="openReprintModal({{ json_encode([
                                                'transaction_number' => $trx->transaction_number,
                                                'date' => $trx->created_at?->format('d/m/Y H:i:s'),
                                                'teller_name' => $trx->teller?->name ?? 'Piket Teller 1',
                                                'student_name' => $trx->saver?->user?->name ?? $trx->saver?->member?->student_name ?? 'Nasabah',
                                                'account_number' => $trx->saver?->account_number ?? '-',
                                                'type' => $trx->type->value,
                                                'formatted_amount' => 'Rp ' . number_format($trx->amount, 0, ',', '.'),
                                                'formatted_closing_balance' => 'Rp ' . number_format($trx->closing_balance, 0, ',', '.'),
                                            ]) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                                        <span class="material-symbols-outlined text-[16px]">print</span>
                                        <span>Cetak Ulang</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">receipt_long</span>
                                    <p class="text-sm font-semibold text-slate-600">Belum Ada Transaksi Hari Ini</p>
                                    <p class="text-xs text-slate-400 mt-1">Transaksi yang diproses di loket Anda akan otomatis muncul di sini secara real-time.</p>
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
        </div>

        <!-- ========================================== -->
        <!-- MODAL REPRINT STRUK DUPLIKAT RESMI -->
        <!-- ========================================== -->
        <div x-show="showReprintModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-200"
                 @click.away="showReprintModal = false">
                
                <div class="text-left bg-slate-50 p-5 rounded-2xl border border-dashed border-slate-300 font-mono text-xs text-slate-800 space-y-3 relative overflow-hidden">
                    <!-- Watermark Duplikat Resmi -->
                    <div class="text-center pb-2 border-b border-dashed border-slate-300">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 mb-1">
                            SALINAN / REPRINT RESMI
                        </span>
                        <h4 class="font-bold text-sm tracking-tight text-slate-900">BANK MINI SMKN 1</h4>
                        <p class="text-[10px] text-slate-500">Jl. Budi Utomo No. 7, Jakarta Pusat</p>
                        <p class="text-[10px] text-slate-500">Loket 01 • Salinan Struk Transaksi</p>
                    </div>

                    <div class="space-y-1 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Ref:</span>
                            <span class="font-bold" x-text="selectedTrx?.transaction_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Waktu:</span>
                            <span x-text="selectedTrx?.date"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Teller:</span>
                            <span x-text="selectedTrx?.teller_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nasabah:</span>
                            <span class="font-bold" x-text="selectedTrx?.student_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Rekening:</span>
                            <span x-text="selectedTrx?.account_number"></span>
                        </div>
                    </div>

                    <div class="py-2 border-t border-b border-dashed border-slate-300 space-y-1 text-xs">
                        <div class="flex justify-between font-bold">
                            <span x-text="selectedTrx?.type === 'deposit' ? 'SETORAN TUNAI' : 'PENARIKAN TUNAI'"></span>
                            <span :class="selectedTrx?.type === 'deposit' ? 'text-emerald-700' : 'text-red-700'"
                                  x-text="selectedTrx?.formatted_amount"></span>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-600">
                            <span>Saldo Akhir:</span>
                            <span class="font-bold text-slate-900" x-text="selectedTrx?.formatted_closing_balance"></span>
                        </div>
                    </div>

                    <div class="text-center pt-1 text-[10px] text-slate-400 space-y-1">
                        <p>Pencetakan ulang (Reprint) dicatat dalam log audit mesin teller.</p>
                        <p class="font-mono text-[9px] text-slate-300">*** ARSIP RESMI ***</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2 no-print">
                    <button type="button" 
                            @click="showReprintModal = false" 
                            class="flex-1 py-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Tutup [Esc]
                    </button>
                    <button type="button" 
                            onclick="window.print()" 
                            class="flex-1 py-3 rounded-xl text-xs font-bold bg-[#059669] hover:bg-emerald-700 text-white shadow-md flex items-center justify-center gap-1.5 transition">
                        <span class="material-symbols-outlined text-[16px]">print</span>
                        <span>Cetak Salinan</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function transactionHistory() {
            return {
                showReprintModal: false,
                selectedTrx: null,

                openReprintModal(trx) {
                    this.selectedTrx = trx;
                    this.showReprintModal = true;
                }
            };
        }
    </script>
    @endpush
</x-teller-layout>
