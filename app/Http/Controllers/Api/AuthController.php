<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
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
