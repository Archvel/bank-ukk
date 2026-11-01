<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ResetPinAction;
use App\Enums\SaverStatus;
use App\Http\Controllers\Controller;
use App\Models\Saver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaverController extends Controller
{
    public function index(Request $request): View
    {
        $query = Saver::withTrashed()->with(['user', 'member']);

        if ($request->filled('status')) {
            if ($request->string('status')->toString() === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $request->string('status'))->whereNull('deleted_at');
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('student_name', 'like', "%{$search}%")
                            ->orWhere('graduation_year', 'like', "%{$search}%");
                    });
            });
        }

        $savers = $query->latest('id')->paginate(12)->withQueryString();

        $summary = Saver::withTrashed()
            ->selectRaw('
                COUNT(CASE WHEN deleted_at IS NULL THEN 1 END) as total_savers,
                SUM(CASE WHEN deleted_at IS NULL AND status = ? THEN balance ELSE 0 END) as total_savings,
                COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as trashed_count
            ', [SaverStatus::Active->value])
            ->first();

        $totalSavers = (int) ($summary?->total_savers ?? 0);
        $totalSavings = (int) ($summary?->total_savings ?? 0);
        $trashedCount = (int) ($summary?->trashed_count ?? 0);

        return view('admin.savers.index', compact(
            'savers',
            'totalSavers',
            'totalSavings',
            'trashedCount'
        ));
    }

    public function show(int $id): View
    {
        $saver = Saver::withTrashed()
            ->with(['user', 'member', 'transactions' => fn ($q) => $q->latest('id')->take(20)])
            ->findOrFail($id);

        return view('admin.savers.show', compact('saver'));
    }

    public function resetPin(Request $request, int $id, ResetPinAction $action): RedirectResponse
    {
        $request->validate([
            'new_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ], [
            'new_pin.required' => 'PIN baru harus diisi.',
            'new_pin.size' => 'PIN harus terdiri dari tepat 6 digit angka.',
            'new_pin.regex' => 'PIN hanya boleh berisi angka.',
        ]);

        $saver = Saver::withTrashed()->findOrFail($id);

        $action->handle(
            $saver,
            $request->string('new_pin')->toString(),
            $request->user()
        );

        return back()->with('success', "PIN keamanan 6-digit untuk rekening {$saver->account_number} ({$saver->user->name}) berhasil direset.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $saver = Saver::findOrFail($id);
        $accountNumber = $saver->account_number;
        $studentName = $saver->user->name ?? 'Nasabah';

        // Soft delete the saver
        $saver->delete();

        return back()->with('success', "Rekening {$accountNumber} atas nama {$studentName} berhasil di-nonaktifkan (SoftDelete). Data transaksi tetap aman dan tersimpan untuk audit.");
    }

    public function restore(int $id): RedirectResponse
    {
        $saver = Saver::onlyTrashed()->findOrFail($id);
        $saver->restore();

        return back()->with('success', "Rekening {$saver->account_number} berhasil dipulihkan kembali ke status aktif.");
    }
}
