<?php

namespace App\Actions;

use App\Enums\CashClosureStatus;
use App\Models\CashClosure;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class SetOpeningCashAction
{
    public const OPERATING_START = '07:00';

    public const OPERATING_END = '15:30';

    public const TIMEZONE = 'Asia/Jakarta';

    /**
     * Check if given time (or now) is within session operational hours (07:00 - 15:30 WIB).
     */
    public function isWithinOperatingHours(?CarbonInterface $time = null): bool
    {
        $now = $time ? Carbon::instance($time)->setTimezone(self::TIMEZONE) : now(self::TIMEZONE);
        $start = $now->copy()->setTime(7, 0, 0);
        $end = $now->copy()->setTime(15, 30, 0);

        return $now->betweenIncluded($start, $end);
    }

    /**
     * Set or update teller opening cash (kas awal) for today's session.
     *
     * @throws ValidationException
     */
    public function handle(
        User $teller,
        int $openingBalance,
        ?string $notes = null,
        bool $enforceOperatingHours = true
    ): CashClosure {
        if ($openingBalance < 0) {
            throw ValidationException::withMessages([
                'opening_balance' => 'Nominal kas awal tidak boleh bernilai negatif.',
            ]);
        }

        $now = now(self::TIMEZONE);
        $start = $now->copy()->setTime(7, 0, 0);
        $end = $now->copy()->setTime(15, 30, 0);

        if ($enforceOperatingHours && ! $now->betweenIncluded($start, $end)) {
            $currentTimeStr = $now->format('H:i');
            if ($now->lt($start)) {
                throw ValidationException::withMessages([
                    'opening_balance' => "Sesi loket belum dibuka (Waktu saat ini: {$currentTimeStr} WIB). Jam operasional sesi loket dimulai pukul 07:00 WIB hingga 15:30 WIB.",
                ]);
            }

            throw ValidationException::withMessages([
                'opening_balance' => "Sesi loket telah berakhir (Waktu saat ini: {$currentTimeStr} WIB). Jam operasional sesi loket adalah pukul 07:00 hingga 15:30 WIB. Silakan lakukan proses penutupan kas harian.",
            ]);
        }

        $today = today(self::TIMEZONE);

        $todayClosure = CashClosure::where('teller_id', $teller->id)
            ->whereDate('closure_date', $today)
            ->first();

        if ($todayClosure && in_array($todayClosure->status, [CashClosureStatus::Submitted, CashClosureStatus::Approved])) {
            throw ValidationException::withMessages([
                'opening_balance' => 'Sesi penutupan kas hari ini sudah diajukan atau disetujui. Kas awal tidak dapat diubah.',
            ]);
        }

        $trxSummary = Transaction::where('teller_id', $teller->id)
            ->whereDate('created_at', $today)
            ->selectRaw('
                SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) as total_deposit,
                SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END) as total_withdrawal
            ')
            ->first();

        $totalDeposit = (int) ($trxSummary?->total_deposit ?? 0);
        $totalWithdrawal = (int) ($trxSummary?->total_withdrawal ?? 0);
        $systemEndingBalance = $openingBalance + $totalDeposit - $totalWithdrawal;

        return CashClosure::updateOrCreate(
            [
                'teller_id' => $teller->id,
                'closure_date' => $today,
            ],
            [
                'opening_balance' => $openingBalance,
                'total_deposit' => $totalDeposit,
                'total_withdrawal' => $totalWithdrawal,
                'system_ending_balance' => $systemEndingBalance,
                'physical_ending_balance' => $todayClosure?->physical_ending_balance ?? 0,
                'difference' => $todayClosure ? ($todayClosure->physical_ending_balance - $systemEndingBalance) : 0,
                'status' => CashClosureStatus::Draft,
                'notes' => $notes ?? $todayClosure?->notes,
            ]
        );
    }

    /**
     * Get suggested opening balance based on previous approved closure.
     */
    public function getSuggestedOpeningBalance(User $teller): int
    {
        $previousApprovedClosure = CashClosure::where('teller_id', $teller->id)
            ->where('closure_date', '<', today(self::TIMEZONE))
            ->where('status', CashClosureStatus::Approved)
            ->orderByDesc('closure_date')
            ->first();

        return $previousApprovedClosure ? (int) $previousApprovedClosure->physical_ending_balance : 0;
    }
}
