<?php

namespace App\Http\Controllers\Nasabah;

use App\Actions\GetTellerSessionStatusAction;
use App\Actions\WithdrawAction;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Nasabah / Student Dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user() ?: Auth::user();

        /** @var Saver|null $saver */
        $saver = Saver::with(['user', 'member'])
            ->where('user_id', $user->id)
            ->first();

        /** @var Member|null $member */
        $member = Member::where('user_id', $user->id)->first();

        $balance = $saver ? (int) $saver->balance : 0;
        $minMaintainingBalance = WithdrawAction::MINIMUM_MAINTAINING_BALANCE;
        $withdrawableBalance = max(0, $balance - $minMaintainingBalance);

        $recentMutations = $saver
            ? Transaction::where('saver_id', $saver->id)->latest('id')->take(5)->get()
            : collect();

        $qrCodeToken = $saver ? ($saver->qr_code_token ?: 'QR-'.$saver->account_number) : null;
        $tellerSessionStatus = app(GetTellerSessionStatusAction::class)->handle();

        return view('nasabah.dashboard', compact(
            'saver',
            'member',
            'balance',
            'minMaintainingBalance',
            'withdrawableBalance',
            'recentMutations',
            'qrCodeToken',
            'tellerSessionStatus'
        ));
    }
}
