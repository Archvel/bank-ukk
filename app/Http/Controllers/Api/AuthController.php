<?php

namespace App\Http\Controllers\Api;

use App\Enums\SaverStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\MemberResource;
use App\Http\Resources\SaverResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Authenticate a user and create a personal access token.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->string('login')->trim()->toString();
        $password = $request->string('password')->toString();

        /** @var User|null $user */
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.',
            ], Response::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('bank-mini-auth')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Authenticate specifically for the Saver mobile application.
     *
     * @throws ValidationException
     */
    public function saverLogin(LoginRequest $request): JsonResponse
    {
        $login = $request->string('login')->trim()->toString();
        $password = $request->string('password')->toString();

        /** @var User|null $user */
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $user->isNasabah()) {
            return response()->json([
                'message' => 'Akses ditolak. Layanan login ini khusus untuk aplikasi mobile nasabah / penabung.',
            ], Response::HTTP_FORBIDDEN);
        }

        $user->load(['member', 'saver']);
        $token = $user->createToken('bank-mini-saver')->plainTextToken;

        // Case 1: Member registered but initial deposit hasn't been completed at teller
        if (! $user->saver) {
            return response()->json([
                'message' => 'Akun Anda terdaftar sebagai calon siswa (member), namun rekening tabungan belum aktif. Silakan lakukan setoran awal minimal Rp10.000 di loket Teller.',
                'token' => $token,
                'role' => $user->role?->value ?? 'nasabah',
                'is_active_saver' => false,
                'account_status' => $user->member?->account_status?->value ?? 'inactive',
                'user' => new UserResource($user),
                'member' => $user->member ? new MemberResource($user->member) : null,
                'saver' => null,
            ]);
        }

        // Case 2: Saver account is blocked or closed
        if ($user->saver->status !== SaverStatus::Active) {
            return response()->json([
                'message' => 'Rekening penabung Anda sedang tidak aktif atau terblokir.',
                'token' => $token,
                'role' => $user->role?->value ?? 'nasabah',
                'is_active_saver' => false,
                'account_status' => $user->saver->status?->value,
                'user' => new UserResource($user),
                'member' => $user->member ? new MemberResource($user->member) : null,
                'saver' => new SaverResource($user->saver),
            ], Response::HTTP_FORBIDDEN);
        }

        // Case 3: Fully active saver
        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'role' => $user->role?->value ?? 'nasabah',
            'is_active_saver' => true,
            'user' => new UserResource($user),
            'member' => $user->member ? new MemberResource($user->member) : null,
            'saver' => new SaverResource($user->saver),
        ]);
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['member', 'saver']);

        return response()->json([
            'user' => new UserResource($user),
            'member' => $user->member ? [
                'id' => $user->member->id,
                'student_name' => $user->member->student_name,
                'graduation_year' => $user->member->graduation_year,
                'account_status' => $user->member->account_status?->value,
            ] : null,
            'saver' => $user->saver ? [
                'id' => $user->saver->id,
                'account_number' => $user->saver->account_number,
                'balance' => $user->saver->balance,
                'formatted_balance' => 'Rp'.number_format($user->saver->balance, 0, ',', '.'),
                'qr_code_token' => $user->saver->qr_code_token,
                'status' => $user->saver->status?->value,
            ] : null,
        ]);
    }
}
