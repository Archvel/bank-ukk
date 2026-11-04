<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    /**
     * Display a listing of members.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Member::query()->with(['user', 'saver'])->latest();

        if ($request->filled('status')) {
            $query->where('account_status', $request->string('status'));
        }

        if ($request->filled('class')) {
            $query->where('class_name', $request->string('class'));
        }

        if ($request->filled('major')) {
            $query->where('major', $request->string('major'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('class_name', 'like', "%{$search}%")
                    ->orWhere('major', 'like', "%{$search}%")
                    ->orWhere('graduation_year', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        return MemberResource::collection($query->paginate(15));
    }

    /**
     * Store a newly created member along with their student user account.
     */
    public function store(StoreMemberRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $member = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['student_name'],
                'email' => $validated['email'],
                'username' => $validated['username'] ?? null,
                'password' => Hash::make($validated['password'] ?? 'password123'),
                'role' => Role::Nasabah,
                'phone_number' => $validated['phone_number'] ?? null,
                'is_active' => true,
            ]);

            return Member::create([
                'user_id' => $user->id,
                'student_name' => $validated['student_name'],
                'graduation_year' => $validated['graduation_year'],
                'account_status' => MemberAccountStatus::Inactive,
            ]);
        });

        return response()->json([
            'message' => 'Data calon penabung (member) berhasil ditambahkan.',
            'member' => new MemberResource($member->load('user')),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member): JsonResponse
    {
        return response()->json([
            'member' => new MemberResource($member->load(['user', 'saver'])),
        ]);
    }

    /**
     * Update the specified member and user.
     */
    public function update(UpdateMemberRequest $request, Member $member): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($member, $validated) {
            $member->update([
                'student_name' => $validated['student_name'] ?? $member->student_name,
                'graduation_year' => $validated['graduation_year'] ?? $member->graduation_year,
            ]);

            $member->user->update([
                'name' => $validated['student_name'] ?? $member->user->name,
                'email' => $validated['email'] ?? $member->user->email,
                'username' => $validated['username'] ?? $member->user->username,
                'phone_number' => $validated['phone_number'] ?? $member->user->phone_number,
            ]);
        });

        return response()->json([
            'message' => 'Data member berhasil diperbarui.',
            'member' => new MemberResource($member->load(['user', 'saver'])),
        ]);
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy(Member $member): JsonResponse
    {
        if ($member->saver && $member->saver->transactions()->exists()) {
            return response()->json([
                'message' => 'Member tidak dapat dihapus karena sudah memiliki buku rekening dan riwayat transaksi finansial.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($member) {
            if ($member->saver) {
                $member->saver->delete();
            }
            $user = $member->user;
            $member->delete();
            $user?->delete();
        });

        return response()->json([
            'message' => 'Data member berhasil dihapus.',
        ]);
    }
}
