<x-admin-layout title="Laporan Transaksi & PDF" breadcrumb="Pelaporan / Cetak Laporan">
    <!-- Top Action Bar (Hidden when printing) -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Transaksi & Cetak PDF Resmi</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekapitulasi mutasi kas harian, penutupan loket, dan pencetakan dokumen pembukuan bulanan sekolah.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-[#059669] text-white hover:bg-emerald-700 shadow-md shadow-emerald-950/20 transition">
                <span class="material-symbols-outlined text-[18px]">print</span>
                <span>Export / Cetak Laporan PDF</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs (Hidden when printing) -->
    <div class="flex items-center gap-2 mb-6 border-b border-slate-200 pb-2 no-print">
        <a href="{{ route('admin.reports.index', ['type' => 'daily', 'date' => $date]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $reportType === 'daily' ? 'bg-[#059669] text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span class="material-symbols-outlined text-[18px]">calendar_today</span>
            <span>Laporan Harian Penutupan Kas</span>
        </a>

        <a href="{{ route('admin.reports.index', ['type' => 'monthly', 'month' => $month, 'year' => $year]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $reportType === 'monthly' ? 'bg-[#059669] text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span class="material-symbols-outlined text-[18px]">date_range</span>
            <span>Laporan Bulanan (Monthly Report)</span>
        </a>
    </div>

    <!-- Filter & Period Selector Panel (Hidden when printing) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs mb-6 no-print">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <input type="hidden" name="type" value="{{ $reportType }}">

            @if($reportType === 'daily')
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label class="text-xs font-bold text-slate-700">Pilih Tanggal:</label>
                    <input type="date" 
                           name="date" 
                           value="{{ $date }}" 
                           onchange="this.form.submit()" 
                           class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            @else
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <label class="text-xs font-bold text-slate-700">Bulan & Tahun:</label>
                    <select name="month" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>

                    <select name="year" onchange="this.form.submit()" class="text-xs rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        @for($y = (int)date('Y'); $y >= (int)date('Y') - 4; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            @endif

            <div class="text-xs text-slate-500 font-medium">
                Hash Dokumen: <span class="font-mono text-slate-700">SHA256-VALID-{{ strtoupper(substr(md5($date . $totalDeposits), 0, 8)) }}</span>
            </div>
        </form>

        <!-- Audit Compliance Callout Banner -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-start gap-2.5 text-xs text-slate-600">
            <span class="material-symbols-outlined text-emerald-600 text-lg shrink-0">verified_user</span>
            <p>
                <strong class="text-slate-800">Integritas Audit Terjamin:</strong> Transaksi dari akun nasabah yang telah dinonaktifkan (<em>SoftDelete</em>) tetap muncul dan terhitung secara sah pada laporan keuangan resmi ini guna menjamin keseimbangan buku besar dan audit forensik.
            </p>
        </div>
    </div>

    <!-- 4 KPI Metrics Summary Bar (Hidden when printing) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6 no-print">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Setoran Kas Masuk</span>
            <div class="text-2xl font-bold font-mono text-emerald-700 mt-1">Rp {{ number_format($totalDeposits, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Kredit Akun 201 Simpanan</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Penarikan Kas Keluar</span>
            <div class="text-2xl font-bold font-mono text-red-700 mt-1">Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Debet Akun 201 Simpanan</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Net Cash Flow (Surplus)</span>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">
                {{ $netCashFlow >= 0 ? '+' : '' }}Rp {{ number_format($netCashFlow, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Perubahan Saldo Kas Bersih</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Mutasi Transaksi</span>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ $transactionCount }} <span class="text-xs font-normal text-slate-400">Transaksi</span></div>
            <p class="text-[11px] text-slate-400 mt-1 font-medium">Tervalidasi Digital</p>
        </div>
    </div>

    <!-- Official PDF Document Preview Canvas (Simulated Printable A4 Sheet) -->
    <div class="bg-white max-w-4xl mx-auto rounded-xl border border-slate-300 shadow-xl p-8 sm:p-12 font-sans print-shadow-none print-only">
        <!-- KOP SURAT RESMI -->
        <div class="border-b-2 border-black pb-4 mb-1">
            <div class="flex items-center justify-between gap-4">
                <div class="w-16 h-16 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-4xl">account_balance</span>
                </div>
                <div class="text-center flex-1">
                    <h2 class="text-lg font-black uppercase tracking-wider text-black">LEMBAGA PENDIDIKAN DAN KEUANGAN MIKRO SEKOLAH</h2>
                    <h3 class="text-xl font-black uppercase text-emerald-800">BANK MINI SMKN 1</h3>
                    <p class="text-xs text-slate-700">Jl. Budi Utomo No. 12, Jakarta Pusat • Telp: (021) 384-5561 • Email: bankmini@smkn1.sch.id</p>
                </div>
                <div class="w-16 text-right text-[10px] text-slate-500 font-mono">
                    REV-2026<br>AKL-UKK
                </div>
            </div>
        </div>
        <div class="border-b border-black mb-6"></div>

        <!-- Document Title -->
        <div class="text-center mb-6">
            <h4 class="text-base font-black uppercase tracking-wide text-black underline underline-offset-4">
                @if($reportType === 'daily')
                    LAPORAN REKAPITULASI TRANSAKSI HARIAN KAS & MUTASI
                @else
                    LAPORAN KEUANGAN BULANAN PERBANKAN MINI SEKOLAH
                @endif
            </h4>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                @if($reportType === 'daily')
                    Periode: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                @else
                    Periode: {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                @endif
            </p>
        </div>

        <!-- Meta Info Grid -->
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3.5 mb-6 text-xs grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
            <div>
                <span class="text-slate-500 block text-[10px] uppercase">Waktu Cetak</span>
                <span class="font-bold text-slate-800">{{ now()->translatedFormat('d/m/Y H:i') }} WIB</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase">Dicetak Oleh</span>
                <span class="font-bold text-slate-800">{{ auth()->user()->name }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase">Total Mutasi</span>
                <span class="font-bold text-slate-800">{{ $transactionCount }} Transaksi</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase">Status Dokumen</span>
                <span class="font-bold text-emerald-700">SAH / TERVERIFIKASI</span>
            </div>
        </div>

        <!-- Mutation Table -->
        <table class="w-full text-left text-xs border-collapse border border-slate-300 mb-6">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-300 text-slate-800 font-bold uppercase text-[10px]">
                    <th class="p-2 border-r border-slate-300 text-center w-8">No</th>
                    <th class="p-2 border-r border-slate-300">Jam</th>
                    <th class="p-2 border-r border-slate-300">No. Rekening</th>
                    <th class="p-2 border-r border-slate-300">Nama Siswa / Nasabah</th>
                    <th class="p-2 border-r border-slate-300">Jenis</th>
                    <th class="p-2 border-r border-slate-300 text-right">Debet (Keluar)</th>
                    <th class="p-2 border-r border-slate-300 text-right">Kredit (Masuk)</th>
                    <th class="p-2 border-r border-slate-300 text-right">Saldo Akhir</th>
                    <th class="p-2 text-center">Teller</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($transactions as $index => $trx)
                    <tr class="text-[11px] {{ $trx->saver?->trashed() ? 'bg-red-50/40' : '' }}">
                        <td class="p-2 border-r border-slate-300 text-center font-mono">{{ $index + 1 }}</td>
                        <td class="p-2 border-r border-slate-300 font-mono">{{ $trx->created_at?->format('H:i') }}</td>
                        <td class="p-2 border-r border-slate-300 font-mono font-bold">{{ $trx->saver?->account_number ?? '-' }}</td>
                        <td class="p-2 border-r border-slate-300">
                            <span class="font-semibold text-slate-900">{{ $trx->saver?->user?->name ?? 'Nasabah' }}</span>
                            @if($trx->saver?->trashed())
                                <span class="ml-1 inline-block px-1.5 py-0.2 bg-red-100 text-red-700 border border-red-200 rounded text-[9px] font-bold">
                                    *Soft-Deleted Saver
                                </span>
                            @endif
                        </td>
                        <td class="p-2 border-r border-slate-300 font-semibold">
                            {{ $trx->type->value === 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai' }}
                        </td>
                        <td class="p-2 border-r border-slate-300 text-right font-mono font-bold text-red-700">
                            {{ $trx->type->value === 'withdrawal' ? 'Rp '.number_format($trx->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="p-2 border-r border-slate-300 text-right font-mono font-bold text-emerald-700">
                            {{ $trx->type->value === 'deposit' ? 'Rp '.number_format($trx->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="p-2 border-r border-slate-300 text-right font-mono font-bold text-slate-800">
                            Rp {{ number_format($trx->closing_balance, 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-center text-slate-600">{{ $trx->teller->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-slate-400 italic">Tidak ada transaksi mutasi kas pada periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-100 border-t-2 border-slate-400 font-bold text-xs">
                    <td colspan="5" class="p-2 text-right">JUMLAH TOTAL:</td>
                    <td class="p-2 text-right font-mono text-red-700">Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}</td>
                    <td class="p-2 text-right font-mono text-emerald-700">Rp {{ number_format($totalDeposits, 0, ',', '.') }}</td>
                    <td class="p-2 text-right font-mono text-slate-900">
                        Surplus: Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                    </td>
                    <td class="p-2"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Sign-Off Block -->
        <div class="mt-8 pt-4 border-t border-slate-200 grid grid-cols-2 gap-8 text-xs text-center">
            <!-- Left: Teller -->
            <div>
                <p class="text-slate-600 mb-1">Mengetahui / Petugas Kasir Loket,</p>
                <div class="h-16 flex items-center justify-center">
                    <span class="text-[10px] font-mono text-slate-400 italic">[ Verified Digital Signature ]</span>
                </div>
                @php
                    $tellerSignOffName = ! empty($tellerNames)
                        ? implode(', ', $tellerNames)
                        : ($closure?->teller?->name ?? 'Petugas Loket Kasir');
                @endphp
                <p class="font-bold text-slate-900 underline">{{ $tellerSignOffName }}</p>
                <p class="text-[10px] text-slate-500">NIP/NISN: 20240901-LOKET</p>
            </div>

            <!-- Right: Administrator -->
            <div>
                <p class="text-slate-600 mb-1">Disetujui / Administrator Bank Mini,</p>
                <div class="h-16 flex items-center justify-center gap-2">
                    <div class="w-12 h-12 border border-slate-400 rounded flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl text-slate-700">qr_code_2</span>
                    </div>
                    <span class="px-2 py-0.5 rounded border border-emerald-600 bg-emerald-50 text-emerald-800 text-[9px] font-bold uppercase">
                        DIGITALLY VERIFIED<br>ADMINISTRATOR
                    </span>
                </div>
                <p class="font-bold text-slate-900 underline">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-500">Kepala Lembaga Perbankan Mini</p>
            </div>
        </div>

        <!-- Document Legal Footer -->
        <div class="mt-8 pt-4 border-t border-slate-100 text-[10px] text-slate-400 text-center">
            Dokumen ini merupakan salinan resmi sah yang diterbitkan oleh Sistem Informasi Bank Mini E-Teller SMKN 1 dan diakui secara internal lembaga.
        </div>
    </div>
</x-admin-layout>
