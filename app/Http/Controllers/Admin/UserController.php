<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Api\Admin\UserController as ApiUserController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->wantsJson()) {
            return app(ApiUserController::class)->index($request);
        }

        $query = User::query()->latest('id');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(12)->withQueryString();

        $userSummary = User::selectRaw('
            COUNT(CASE WHEN role = ? THEN 1 END) as admin_count,
            COUNT(CASE WHEN role = ? THEN 1 END) as teller_count,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_count
        ', [Role::Admin->value, Role::Teller->value])->first();

        $adminCount = (int) ($userSummary?->admin_count ?? 0);
        $tellerCount = (int) ($userSummary?->teller_count ?? 0);
        $activeCount = (int) ($userSummary?->active_count ?? 0);

        return view('admin.users.index', compact(
            'users',
            'adminCount',
            'tellerCount',
            'activeCount'
        ));
    }

    public function store(StoreUserRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return app(ApiUserController::class)->store($request);
        }

        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect('/admin/users')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return app(ApiUserController::class)->update($request, $user);
        }

        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect('/admin/users')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return app(ApiUserController::class)->destroy($request, $user);
        }

        if ($user->id === $request->user()?->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->tellerTransactions()->exists() || ($user->saver && $user->saver->transactions()->exists())) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena memiliki riwayat audit finansial transaksi.');
        }

        $user->delete();

        return redirect('/admin/users')->with('success', 'Pengguna berhasil dihapus dari sistem.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()?->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Akun pengguna {$user->name} berhasil {$statusText}.");
    }
}
