<x-admin-layout title="Verifikasi Penutupan Kas" breadcrumb="Verifikasi Kas / Detail Otorisasi">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cash-closures.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Otorisasi Penutupan Kas: Loket {{ $closure->teller->name ?? 'Teller' }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">Tanggal Operasional: {{ $closure->closure_date?->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
        <div>
            @if($closure->status->value === 'submitted')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                    Menunggu Otorisasi Administrator
                </span>
            @elseif($closure->status->value === 'approved')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Disetujui & Kas Masuk Brankas
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                    Laporan Ditolak
                </span>
            @endif
        </div>
    </div>

    <!-- Dual-Key Verification Summary Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Breakdown Values (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 mb-4">Rincian Rekonsiliasi Kas Teller</h2>

            <div class="space-y-3 font-mono text-sm">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-600">Saldo Awal Kas Laci Loket</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($closure->opening_balance, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/60 border border-emerald-100">
                    <span class="text-emerald-800 font-semibold">(+) Total Setoran Tunai Masuk Hari Ini</span>
                    <span class="font-bold text-emerald-700">+Rp {{ number_format($closure->total_deposit, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-red-50/60 border border-red-100">
                    <span class="text-red-800 font-semibold">(-) Total Penarikan Tunai Keluar Hari Ini</span>
                    <span class="font-bold text-red-700">-Rp {{ number_format($closure->total_withdrawal, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-900 text-white">
                    <span class="font-bold">(=) Saldo Akhir Berdasarkan Sistem Buku Besar</span>
                    <span class="font-bold text-base text-emerald-400">Rp {{ number_format($closure->system_ending_balance, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between p-3.5 rounded-xl bg-blue-50 border border-blue-200">
                    <div>
                        <p class="font-bold text-blue-900">Uang Fisik Dihitung & Diserahkan Teller</p>
                        <p class="text-[11px] text-blue-600 font-sans">Uang tunai siap dipindahkan ke Brankas Utama</p>
                    </div>
                    <span class="font-bold text-lg text-blue-900">Rp {{ number_format($closure->physical_ending_balance, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl {{ $closure->difference === 0 ? 'bg-emerald-100/70 border border-emerald-300 text-emerald-900' : 'bg-red-100/70 border border-red-300 text-red-900' }}">
                    <span class="font-bold">Status Selisih (Difference)</span>
                    <span class="font-bold text-base">
                        @if($closure->difference === 0)
                            Rp 0 (BALANCE / KLOP SEIMBANG)
                        @else
                            {{ $closure->difference > 0 ? '+' : '' }}Rp {{ number_format($closure->difference, 0, ',', '.') }}
                        @endif
                    </span>
                </div>
            </div>

            @if($closure->notes)
                <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                    <span class="font-bold text-slate-700">Catatan dari Teller:</span>
                    <p class="text-slate-600 mt-1 italic">{{ $closure->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Approval Action Panel (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 mb-2">Dual-Control Otorisasi</h2>
                <p class="text-xs text-slate-500 mb-4">Persetujuan Administrator akan otomatis memicu jurnal akuntansi:</p>

                <!-- Accounting Journal Impact Notice -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 mb-6 space-y-2 text-xs font-mono">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Dampak Pembukuan:</div>
                    <div class="flex items-center justify-between text-emerald-700">
                        <span>Debet: 102 - Kas Brankas Utama</span>
                        <span class="font-bold">Rp {{ number_format($closure->physical_ending_balance, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-700">
                        <span>Kredit: 101 - Kas Teller Loket</span>
                        <span class="font-bold">Rp {{ number_format($closure->physical_ending_balance, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($closure->status->value === 'submitted')
                    <!-- Action Form: Approve or Reject -->
                    <form method="POST" action="{{ route('admin.cash-closures.process', $closure) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Verifikasi (Opsional)</label>
                            <textarea name="reason" rows="2" placeholder="Tuliskan catatan verifikasi fisik uang brankas..." class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>

                        <div class="space-y-2 pt-2">
                            @php
                                $formattedPhysical = number_format($closure->physical_ending_balance, 0, ',', '.');
                            @endphp
                            <button type="submit" 
                                    name="approve" 
                                    value="1" 
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-xs font-bold bg-[#059669] hover:bg-emerald-700 text-white shadow-md shadow-emerald-950/20 transition"
                                    onclick="return confirm('Konfirmasi persetujuan: Kas fisik sebesar Rp {{ $formattedPhysical }} akan dicatat masuk ke Brankas Utama?')">
                                <span class="material-symbols-outlined text-[18px]">verified</span>
                                <span>Setujui & Pindahkan ke Brankas</span>
                            </button>

                            <button type="submit" 
                                    name="approve" 
                                    value="0" 
                                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold bg-white border border-red-200 text-red-600 hover:bg-red-50 transition"
                                    onclick="return confirm('Apakah Anda yakin ingin menolak laporan kas ini?')">
                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                <span>Tolak Laporan Penutupan</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">
                        <p class="font-bold text-slate-900">Laporan Telah Diproses</p>
                        <p>Oleh: <span class="font-semibold">{{ $closure->approver->name ?? 'Administrator' }}</span></p>
                        <p>Waktu: <span class="font-mono">{{ $closure->approved_at?->format('d/m/Y H:i:s') ?? '-' }}</span></p>
                        @if($closure->notes)
                            <p class="mt-2 text-slate-500 italic">"{{ $closure->notes }}"</p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-100 mt-6 text-center">
                <span class="text-[11px] text-slate-400">ID Sesi Kasir Loket #CLS-{{ str_pad($closure->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>

    <!-- Related Journal Entries if already generated -->
    @if($closure->journalEntries->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-sm text-slate-900">
                Baris Jurnal Otomatis yang Terbit
            </div>
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold uppercase">
                        <th class="py-2.5 px-4">No. Jurnal</th>
                        <th class="py-2.5 px-4">Kode Akun</th>
                        <th class="py-2.5 px-4">Nama Akun</th>
                        <th class="py-2.5 px-4">Tipe</th>
                        <th class="py-2.5 px-4 text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($closure->journalEntries as $entry)
                        <tr>
                            <td class="py-2.5 px-4 font-mono font-bold">{{ $entry->entry_number }}</td>
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-700">{{ $entry->account_code }}</td>
                            <td class="py-2.5 px-4 font-semibold text-slate-800">{{ $entry->account->name ?? 'Akun' }}</td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $entry->type->value === 'debit' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ strtoupper($entry->type->value) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold tabular-nums">
                                Rp {{ number_format($entry->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
