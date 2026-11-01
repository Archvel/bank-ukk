<x-teller-layout title="Riwayat Transaksi Hari Ini">
    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6" x-data="transactionHistory()">
        
        <!-- Baris Atas: 3 Card Metrik Simpel -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Card 1: Total Transaksi -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Transaksi</span>
                <div class="text-2xl font-extrabold font-mono text-slate-900 tabular-nums mt-1">
                    {{ $totalTransactionsCount }} <span class="text-xs font-normal text-slate-500">Transaksi</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Sesi loket hari ini</p>
            </div>

            <!-- Card 2: Total Setoran Masuk -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Setoran Masuk</span>
                <div class="text-2xl font-extrabold font-mono text-emerald-600 tabular-nums mt-1">
                    Rp {{ number_format($totalDepositAmount, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Kas masuk dari penabung</p>
            </div>

            <!-- Card 3: Total Penarikan Keluar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Penarikan Keluar</span>
                <div class="text-2xl font-extrabold font-mono text-red-600 tabular-nums mt-1">
                    Rp {{ number_format($totalWithdrawalAmount, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Penarikan tunai terverifikasi</p>
            </div>
        </div>

        <!-- Baris Tengah: Filter Tab Sederhana & Input Pencarian -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
            <!-- Filter Tabs Sederhana -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl overflow-x-auto">
                <a href="{{ route('teller.transactions.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ !request()->filled('type') ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('teller.transactions.index', ['type' => 'deposit']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('type') === 'deposit' ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Setoran
                </a>
                <a href="{{ route('teller.transactions.index', ['type' => 'withdrawal']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('type') === 'withdrawal' ? 'bg-[#059669] text-white shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                    Penarikan
                </a>
            </div>

            <!-- Input Pencarian di Sebelah Kanan -->
            <form method="GET" action="{{ route('teller.transactions.index') }}" class="relative flex items-center">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}" />
                @endif
                <span class="material-symbols-outlined absolute left-3.5 text-slate-400 text-[18px]">search</span>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Cari No. Referensi / Nama Nasabah..." 
                       class="pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:border-emerald-500 focus:ring-emerald-500 w-full sm:w-72" />
            </form>
        </div>

        <!-- Area Utama: Tabel 100% Full-Width -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-5">Waktu</th>
                            <th class="py-3 px-5">No. Referensi</th>
                            <th class="py-3 px-5">Nasabah</th>
                            <th class="py-3 px-5">Jenis</th>
                            <th class="py-3 px-5 text-right">Nominal</th>
                            <th class="py-3 px-5 text-center">Status</th>
                            <th class="py-3 px-5 text-right no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Waktu -->
                                <td class="py-3.5 px-5 font-mono text-slate-600">
                                    {{ $trx->created_at?->format('H:i:s') }}
                                </td>

                                <!-- No. Referensi -->
                                <td class="py-3.5 px-5 font-mono font-medium text-slate-900">
                                    {{ $trx->transaction_number }}
                                </td>

                                <!-- Nasabah -->
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-900">
                                        {{ $trx->saver?->user?->name ?? $trx->saver?->member?->student_name ?? 'Nasabah' }}
                                    </div>
                                    <span class="text-[11px] font-mono text-slate-400">
                                        {{ $trx->saver?->account_number ?? '-' }}
                                    </span>
                                </td>

                                <!-- Jenis -->
                                <td class="py-3.5 px-5">
                                    @if($trx->type->value === 'deposit')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span>Setoran</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-800 border border-red-200">
                                            <span>Penarikan</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Nominal -->
                                <td class="py-3.5 px-5 text-right font-mono font-bold tabular-nums {{ $trx->type->value === 'deposit' ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $trx->type->value === 'deposit' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-600">
                                        <span>Terkunci</span>
                                    </span>
                                </td>

                                <!-- Aksi: Cetak Struk (Membuka Modal Pop-up) -->
                                <td class="py-3.5 px-5 text-right no-print">
                                    <button type="button" 
                                            @click="openReprintModal({{ json_encode([
                                                'transaction_number' => $trx->transaction_number,
                                                'date' => $trx->created_at?->format('d/m/Y H:i:s'),
                                                'teller_name' => $trx->teller?->name ?? 'Petugas Loket',
                                                'student_name' => $trx->saver?->user?->name ?? $trx->saver?->member?->student_name ?? 'Nasabah',
                                                'account_number' => $trx->saver?->account_number ?? '-',
                                                'type' => $trx->type->value,
                                                'formatted_amount' => 'Rp ' . number_format($trx->amount, 0, ',', '.'),
                                                'formatted_closing_balance' => 'Rp ' . number_format($trx->closing_balance, 0, ',', '.'),
                                            ]) }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                                        <span class="material-symbols-outlined text-[15px]">print</span>
                                        <span>Cetak Struk</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-2">
                                        <span class="material-symbols-outlined text-xl">receipt_long</span>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-700">Belum Ada Transaksi Hari Ini</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Transaksi yang diproses di loket akan tercatat di sini.</p>
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
        <!-- MODAL POP-UP: CETAK ULANG STRUK (OVERLAY) -->
        <!-- ========================================== -->
        <div x-show="showReprintModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-200"
                 @click.away="showReprintModal = false">
                
                <div class="text-left bg-slate-50 p-5 rounded-2xl border border-dashed border-slate-300 font-mono text-xs text-slate-800 space-y-3">
                    <div class="text-center pb-2 border-b border-dashed border-slate-300">
                        <h4 class="font-bold text-sm tracking-tight text-slate-900">BANK MINI SEKOLAH</h4>
                        <p class="text-[10px] text-slate-500">Salinan Struk Transaksi</p>
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
                            <span class="text-slate-500">Petugas:</span>
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

                    <div class="text-center pt-1 text-[10px] text-slate-400">
                        <p>Simpan tanda terima ini sebagai arsip transaksi.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2 no-print">
                    <button type="button" 
                            @click="showReprintModal = false" 
                            class="flex-1 py-2.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Tutup
                    </button>
                    <button type="button" 
                            onclick="window.print()" 
                            class="flex-1 py-2.5 rounded-xl text-xs font-semibold bg-[#059669] hover:bg-emerald-700 text-white shadow-xs flex items-center justify-center gap-1.5 transition">
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
