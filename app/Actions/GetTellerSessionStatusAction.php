<?php

namespace App\Actions;

use App\Enums\CashClosureStatus;
use App\Models\CashClosure;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class GetTellerSessionStatusAction
{
    public function __construct(
        protected SetOpeningCashAction $setOpeningCashAction
    ) {}

    /**
     * Get real-time counter and teller session operational status for nasabah.
     *
     * @return array{
     *     is_open: bool,
     *     has_active_session: bool,
     *     status_key: string,
     *     status_title: string,
     *     status_badge: string,
     *     badge_class: string,
     *     message: string,
     *     active_tellers: array<int, string>,
     *     active_teller_count: int,
     *     operating_hours: string,
     *     operating_start: string,
     *     operating_end: string,
     *     is_within_operating_hours: bool,
     *     current_time: string
     * }
     */
    public function handle(?CarbonInterface $time = null): array
    {
        $timezone = SetOpeningCashAction::TIMEZONE;
        $now = $time ? Carbon::instance($time)->setTimezone($timezone) : now($timezone);
        $today = $now->copy()->startOfDay();

        $isWithinOperatingHours = $this->setOpeningCashAction->isWithinOperatingHours($now);

        // Active sessions: cash closures initiated for today with draft status (teller has input kas awal)
        $activeClosures = CashClosure::with('teller')
            ->whereDate('closure_date', $today)
            ->where('status', CashClosureStatus::Draft)
            ->get();

        // Closed sessions: cash closures submitted or approved today
        $closedClosures = CashClosure::with('teller')
            ->whereDate('closure_date', $today)
            ->whereIn('status', [CashClosureStatus::Submitted, CashClosureStatus::Approved])
            ->get();

        $activeTellers = $activeClosures->map(fn ($c) => $c->teller?->name)->filter()->values()->all();
        $activeTellerCount = count($activeTellers);
        $hasActiveSession = $activeTellerCount > 0;

        if ($hasActiveSession) {
            $tellerNames = implode(', ', $activeTellers);
            $isOpen = true;
            $statusKey = 'active';
            $statusTitle = 'Loket Teller Buka (Sesi Aktif)';
            $statusBadge = 'LOKET BUKA';
            $badgeClass = 'bg-emerald-500/20 text-emerald-700 border-emerald-500/30';
            $message = "Loket Bank Mini sedang aktif melayani transaksi siswa. Petugas piket: {$tellerNames}.";
        } elseif ($closedClosures->isNotEmpty()) {
            $isOpen = false;
            $statusKey = 'closed';
            $statusTitle = 'Sesi Loket Telah Selesai / Ditutup';
            $statusBadge = 'LOKET TUTUP';
            $badgeClass = 'bg-slate-200 text-slate-700 border-slate-300';
            $message = 'Layanan loket hari ini telah berakhir dan sedang dalam proses rekonsiliasi kas harian.';
        } else {
            $isOpen = false;
            $statusKey = 'not_started';

            if ($isWithinOperatingHours) {
                $statusTitle = 'Loket Belum Memulai Sesi';
                $statusBadge = 'BELUM BUKA';
                $badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                $message = 'Petugas loket belum menginput kas awal atau memulai sesi pelayanan hari ini. Mohon menunggu beberapa saat.';
            } else {
                $statusTitle = 'Di Luar Jam Operasional Loket';
                $statusBadge = 'LOKET TUTUP';
                $badgeClass = 'bg-slate-100 text-slate-600 border-slate-300';
                $message = 'Loket Bank Mini SMKN 1 beroperasi pada hari kerja pukul 07:00 hingga 15:30 WIB.';
            }
        }

        return [
            'is_open' => $isOpen,
            'has_active_session' => $hasActiveSession,
            'status_key' => $statusKey,
            'status_title' => $statusTitle,
            'status_badge' => $statusBadge,
            'badge_class' => $badgeClass,
            'message' => $message,
            'active_tellers' => $activeTellers,
            'active_teller_count' => $activeTellerCount,
            'operating_hours' => sprintf('%s - %s WIB', SetOpeningCashAction::OPERATING_START, SetOpeningCashAction::OPERATING_END),
            'operating_start' => SetOpeningCashAction::OPERATING_START,
            'operating_end' => SetOpeningCashAction::OPERATING_END,
            'is_within_operating_hours' => $isWithinOperatingHours,
            'current_time' => $now->format('H:i').' WIB',
        ];
    }
}
