<x-admin-layout title="Dashboard Administrator" breadcrumb="Dashboard">
    <!-- Top Greeting Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p class="text-sm text-slate-500 mt-0.5">Ringkasan kesehatan kas, likuiditas brankas, dan aktivitas operasional loket perbankan mini sekolah.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cash-closures.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs transition">
                <span class="material-symbols-outlined text-[18px] text-amber-500">verified_user</span>
                <span>Verifikasi Kas</span>
                @if($pendingClosures->count() > 0)
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800">{{ $pendingClosures->count() }}</span>
                @endif
            </a>
            <a href="{{ route('admin.reports.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-sm shadow-emerald-900/20 transition">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    <!-- 4 Summary Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Card 1: Total Simpanan Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Simpanan Siswa</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                </div>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tabular-nums">
                Rp {{ number_format($totalSavings, 0, ',', '.') }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                <span class="inline-flex items-center text-emerald-600 font-semibold">
                    <span class="material-symbols-outlined text-[14px]">trending_up</span> Liabilitas 201
                </span>
                <span>• Terhitung aman</span>
            </div>
        </div>

        <!-- Card 2: Kas Fisik di Brankas Utama (Akun 102) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kas Brankas Utama</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">savings</span>
                </div>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tabular-nums">
                Rp {{ number_format($vaultBalance, 0, ',', '.') }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                <span class="inline-flex items-center text-blue-600 font-semibold">
                    <span class="material-symbols-outlined text-[14px]">security</span> Aset 102
                </span>
                <span>• Brankas Terkunci</span>
            </div>
        </div>

        <!-- Card 3: Perputaran Kas Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Arus Kas Hari Ini</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">sync_alt</span>
                </div>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tabular-nums">
                Rp {{ number_format($todayTurnover, 0, ',', '.') }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-500 font-medium">
                <span class="text-emerald-600 font-bold">Masuk: Rp {{ number_format($todayDeposits, 0, ',', '.') }}</span>
                <span>|</span>
                <span class="text-red-600 font-bold">Keluar: Rp {{ number_format($todayWithdrawals, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Card 4: Rekening Siswa Aktif -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rekening Siswa Aktif</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">groups</span>
                </div>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tabular-nums">
                {{ number_format($activeSaversCount, 0, ',', '.') }} <span class="text-sm font-normal text-slate-500">Siswa</span>
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                <span class="inline-flex items-center text-purple-600 font-semibold">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span> Status Aktif
                </span>
                <span>• Terintegrasi Loket</span>
            </div>
        </div>
    </div>

    <!-- Main Content Section: Chart & Pending Cash Closures -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Operational Cash Flow Chart (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Analitik Arus Kas Operasional 7 Hari</h2>
                    <p class="text-xs text-slate-500">Perbandingan riil uang masuk (Setoran) vs uang keluar (Penarikan) di loket teller</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#059669]"></span>
                        <span class="text-slate-700">Uang Masuk (Setoran)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#DC2626]"></span>
                        <span class="text-slate-700">Uang Keluar (Penarikan)</span>
                    </div>
                </div>
            </div>
            <div class="relative h-72 w-full">
                <canvas id="cashflowChart"
                    data-labels="{{ json_encode($chartLabels) }}"
                    data-deposits="{{ json_encode($chartDeposits) }}"
                    data-withdrawals="{{ json_encode($chartWithdrawals) }}"></canvas>
            </div>
        </div>

        <!-- Pending Cash Closures Panel (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900">Verifikasi Kas Loket</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        {{ $pendingClosures->count() }} Menunggu ACC
                    </span>
                </div>
                <p class="text-xs text-slate-500 mb-4">Laporan penutupan kas harian teller loket yang memerlukan otorisasi pemindahan fisik ke Brankas Utama.</p>

                @if($pendingClosures->isEmpty())
                    <div class="py-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 p-4">
                        <span class="material-symbols-outlined text-4xl text-emerald-500 mb-2">task_alt</span>
                        <p class="text-sm font-semibold text-slate-700">Semua Kas Terverifikasi</p>
                        <p class="text-xs text-slate-400 mt-1">Tidak ada laporan kas loket yang tertunda saat ini.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($pendingClosures as $closure)
                            <div class="p-3.5 rounded-xl border border-amber-200/80 bg-amber-50/40 hover:bg-amber-50 transition">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-slate-900">{{ $closure->teller->name ?? 'Teller' }}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">{{ $closure->closure_date?->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-sm font-bold font-mono text-emerald-700 tabular-nums">
                                    Rp {{ number_format($closure->physical_ending_balance, 0, ',', '.') }}
                                </div>
                                <div class="mt-2 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">
                                        Selisih: <span class="font-bold {{ $closure->difference === 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($closure->difference, 0, ',', '.') }}</span>
                                    </span>
                                    <a href="{{ route('admin.cash-closures.show', $closure) }}" 
                                       class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#059669] text-white hover:bg-emerald-700 transition">
                                        Periksa & ACC
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 mt-4">
                <a href="{{ route('admin.cash-closures.index') }}" class="text-xs font-semibold text-[#059669] hover:underline flex items-center justify-center gap-1">
                    <span>Buka Seluruh Riwayat Penutupan Kas</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Grids: Recent Transactions & Recent Savers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Mutasi Transaksi Loket Terakhir</h2>
                    <p class="text-xs text-slate-500">Log transaksi nasabah di loket teller hari ini</p>
                </div>
                <a href="{{ route('admin.audit-journals.index') }}" class="text-xs font-semibold text-[#059669] hover:underline">
                    Lihat Jurnal
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Nasabah</th>
                            <th class="py-2.5 px-3">Jenis</th>
                            <th class="py-2.5 px-3 text-right">Nominal</th>
                            <th class="py-2.5 px-3">Teller</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentTransactions as $trx)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-3 text-slate-500 font-mono">{{ $trx->created_at?->format('H:i') }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800 truncate max-w-[140px]">{{ $trx->saver?->user?->name ?? 'Nasabah' }}</td>
                                <td class="py-2.5 px-3">
                                    @if($trx->type->value === 'deposit')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Setoran</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Penarikan</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold tabular-nums {{ $trx->type->value === 'deposit' ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $trx->type->value === 'deposit' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $trx->teller->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada transaksi mutasi tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Savers (Master Rekening) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Rekening Siswa Baru Aktif</h2>
                    <p class="text-xs text-slate-500">Daftar buku tabungan yang baru diaktivasi di loket</p>
                </div>
                <a href="{{ route('admin.savers.index') }}" class="text-xs font-semibold text-[#059669] hover:underline">
                    Kelola Savers
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
                            <th class="py-2.5 px-3">No. Rekening</th>
                            <th class="py-2.5 px-3">Nama Siswa</th>
                            <th class="py-2.5 px-3">Angkatan</th>
                            <th class="py-2.5 px-3 text-right">Saldo Saat Ini</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSavers as $saver)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-3 font-mono font-semibold text-emerald-700">{{ $saver->account_number }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800 truncate max-w-[140px]">{{ $saver->user->name ?? $saver->member?->student_name }}</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $saver->member?->graduation_year ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 tabular-nums">
                                    Rp {{ number_format($saver->balance, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada akun saver terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script Initialization (Scoped strictly to Dashboard) -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('cashflowChart');
            if (ctx) {
                const chartLabels = JSON.parse(ctx.dataset.labels || '[]');
                const chartDeposits = JSON.parse(ctx.dataset.deposits || '[]');
                const chartWithdrawals = JSON.parse(ctx.dataset.withdrawals || '[]');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Uang Masuk (Setoran)',
                                data: chartDeposits,
                                borderColor: '#059669',
                                backgroundColor: 'rgba(5, 150, 105, 0.08)',
                                tension: 0.35,
                                fill: true,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#059669',
                                pointRadius: 4,
                            },
                            {
                                label: 'Uang Keluar (Penarikan)',
                                data: chartWithdrawals,
                                borderColor: '#DC2626',
                                backgroundColor: 'rgba(220, 38, 38, 0.04)',
                                tension: 0.35,
                                fill: true,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#DC2626',
                                pointRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.y !== null) {
                                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(226, 232, 240, 0.6)' },
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + (value >= 1000000 ? (value/1000000).toFixed(1) + 'M' : (value/1000).toFixed(0) + 'k');
                                    },
                                    font: { family: 'Figtree', size: 11 }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Figtree', size: 11 } }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-admin-layout>
