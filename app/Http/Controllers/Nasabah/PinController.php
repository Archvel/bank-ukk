<?php

namespace App\Http\Controllers\Nasabah;

use App\Actions\ResetPinAction;
use App\Enums\PinResetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmPinResetRequest;
use App\Models\PinResetRequest;
use App\Models\Saver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PinController extends Controller
{
    /**
     * Display the Security & PIN Reset Center.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user() ?: Auth::user();

        /** @var Saver|null $saver */
        $saver = Saver::where('user_id', $user->id)->first();

        if (! $saver) {
            return redirect()->route('nasabah.dashboard')->with('error', 'Rekening tabungan belum aktif. Lakukan setoran awal minimal Rp10.000 di Loket Teller untuk mengaktifkan rekening.');
        }

        $recentRequests = PinResetRequest::where('saver_id', $saver->id)
            ->latest('id')
            ->take(5)
            ->get();

        return view('nasabah.pin.index', compact(
            'saver',
            'recentRequests'
        ));
    }

    /**
     * Generate a new 8-character PIN reset token for this saver account.
     */
    public function requestToken(Request $request): RedirectResponse
    {
        $user = $request->user() ?: Auth::user();

        /** @var Saver $saver */
        $saver = Saver::where('user_id', $user->id)->firstOrFail();

        $token = strtoupper(Str::random(8));

        PinResetRequest::create([
            'saver_id' => $saver->id,
            'reset_token' => $token,
            'status' => PinResetStatus::Pending,
            'expires_at' => now()->addHours(24),
        ]);

        return redirect()->route('nasabah.pin.index')
            ->with('generated_token', $token)
            ->with('success', "Permohonan reset PIN berhasil dibuat! Token otorisasi Anda: {$token} (berlaku 24 jam). Silakan masukkan token tersebut pada form di bawah ini.");
    }

    /**
     * Confirm PIN reset with a valid token and new 6-digit PIN.
     */
    public function confirmReset(ConfirmPinResetRequest $request, ResetPinAction $action): RedirectResponse
    {
        $user = $request->user() ?: Auth::user();
        $token = $request->string('reset_token')->trim()->toString();
        $newPin = $request->string('new_pin')->toString();

        /** @var PinResetRequest|null $pinReset */
        $pinReset = PinResetRequest::where('reset_token', $token)
            ->where('status', PinResetStatus::Pending)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pinReset) {
            return redirect()->route('nasabah.pin.index')
                ->withInput()
                ->with('error', 'Token reset PIN tidak valid atau sudah kedaluwarsa. Silakan ajukan token baru.');
        }

        // Verify saver owns this request
        /** @var Saver $saver */
        $saver = Saver::where('user_id', $user->id)->firstOrFail();
        if ($pinReset->saver_id !== $saver->id) {
            return redirect()->route('nasabah.pin.index')
                ->withInput()
                ->with('error', 'Token ini tidak cocok dengan rekening Anda.');
        }

        $action->handle(
            saver: $saver,
            newPin: $newPin,
            verifier: $user,
            pinResetRequest: $pinReset
        );

        return redirect()->route('nasabah.pin.index')
            ->with('success', 'PIN keamanan 6-digit berhasil diperbarui! Silakan gunakan PIN baru ini untuk otorisasi penarikan uang di loket Teller.');
    }
}
