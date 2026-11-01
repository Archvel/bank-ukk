<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Bukti Transaksi - {{ $transaction->transaction_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f8fafc;
            color: #000;
            padding: 20px;
            margin: 0;
            display: flex;
            justify-content: center;
        }
        .slip {
            width: 80mm;
            background: #fff;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border: 1px dashed #cbd5e1;
            font-size: 12px;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; margin: 3px 0; }
        .btn-print {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background: #059669;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
        }
        @media print {
            body { background: white; padding: 0; }
            .slip { box-shadow: none; border: none; width: 100%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="slip">
        <div class="text-center">
            <h3 style="margin: 0; font-size: 14px;">BANK MINI SMKN 1</h3>
            <p style="margin: 2px 0; font-size: 10px;">Jl. Budi Utomo No. 7, Sawah Besar, Jakarta Pusat</p>
            <p style="margin: 2px 0; font-size: 10px;">Terminal: LOKET-01 • E-Teller POS</p>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span>No. Ref:</span>
            <span class="font-bold">{{ $transaction->transaction_number }}</span>
        </div>
        <div class="row">
            <span>Waktu:</span>
            <span>{{ $transaction->created_at?->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="row">
            <span>Teller:</span>
            <span>{{ $transaction->teller?->name ?? 'Piket Teller 1' }}</span>
        </div>
        <div class="row">
            <span>Nasabah:</span>
            <span class="font-bold">{{ $transaction->saver?->user?->name ?? $transaction->saver?->member?->student_name ?? 'Nasabah Siswa' }}</span>
        </div>
        <div class="row">
            <span>No. Rekening:</span>
            <span>{{ $transaction->saver?->account_number ?? '-' }}</span>
        </div>

        <div class="divider"></div>

        <div class="row font-bold" style="font-size: 13px;">
            <span>{{ $transaction->type->value === 'deposit' ? 'SETORAN TUNAI' : 'PENARIKAN TUNAI' }}</span>
            <span>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
        </div>
        <div class="row" style="font-size: 11px; color: #475569;">
            <span>Saldo Akhir:</span>
            <span class="font-bold">Rp {{ number_format($transaction->closing_balance, 0, ',', '.') }}</span>
        </div>

        @if($transaction->notes)
            <div class="row" style="font-size: 10px; color: #64748b; margin-top: 4px;">
                <span>Catatan:</span>
                <span>{{ $transaction->notes }}</span>
            </div>
        @endif

        <div class="divider"></div>

        <div class="text-center" style="font-size: 10px; color: #64748b; margin-top: 8px;">
            <p style="margin: 2px 0;">Struk sah sebagai bukti transaksi Bank Mini SMKN 1.</p>
            <p style="margin: 2px 0;">Simpan struk ini untuk rekonsiliasi buku tabungan.</p>
            <p style="margin: 6px 0 0 0; font-weight: bold;">*** TERIMA KASIH ***</p>
        </div>

        <button type="button" class="btn-print" onclick="window.print()">Cetak Thermal (Print)</button>
    </div>
</body>
</html>
