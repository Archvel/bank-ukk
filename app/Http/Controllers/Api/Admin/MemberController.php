<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\DeleteMemberAction;
use App\Actions\PromoteStudentsAction;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportMemberExcelRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\User;
use App\Services\MemberExcelImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                'class_name' => $validated['class_name'] ?? null,
                'major' => $validated['major'] ?? null,
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
                'class_name' => array_key_exists('class_name', $validated) ? $validated['class_name'] : $member->class_name,
                'major' => array_key_exists('major', $validated) ? $validated['major'] : $member->major,
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
    public function destroy(Member $member, DeleteMemberAction $deleteMemberAction): JsonResponse
    {
        $result = $deleteMemberAction->handle($member);

        if (! $result['deleted']) {
            return response()->json([
                'message' => $result['message'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => 'Data member berhasil dihapus.',
        ]);
    }

    public function downloadImportTemplate(MemberExcelImportService $importService): StreamedResponse
    {
        $csv = $importService->generateTemplateCsv();

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'template_import_calon_nasabah.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_calon_nasabah.csv"',
        ]);
    }

    public function importExcel(ImportMemberExcelRequest $request, MemberExcelImportService $importService): JsonResponse|RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $result = $importService->import($file);

        if (! $request->wantsJson()) {
            return redirect('/admin/members')->with('success', "Berhasil mengimpor {$result['imported']} calon nasabah.");
        }

        return response()->json([
            'message' => $result['imported'] > 0
                ? "Berhasil mengimpor {$result['imported']} calon nasabah."
                : 'Tidak ada data yang berhasil diimpor.',
            'result' => $result,
        ], $result['success'] ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function promote(Request $request, PromoteStudentsAction $action): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:all,level,class'],
            'source_level' => ['nullable', 'string', 'in:X,XI,XII'],
            'source_class' => ['nullable', 'string', 'max:50'],
            'target_class' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['nullable', 'string', 'max:50'],
            'graduate_grade_twelve' => ['nullable', 'boolean'],
        ]);

        $result = $action->handle($validated);

        return response()->json($result);
    }

    public function promoteMember(Request $request, Member $member, PromoteStudentsAction $action): JsonResponse
    {
        $validated = $request->validate([
            'target_class' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['nullable', 'string', 'max:50'],
        ]);

        $oldClass = $member->class_name ?: '(Belum ada kelas)';
        $updated = $action->promoteSingle($member, $validated['target_class'] ?? null, $validated['academic_year'] ?? null);

        return response()->json([
            'message' => "Siswa {$member->student_name} berhasil dinaikkan dari kelas {$oldClass} ke {$updated->class_name}.",
            'member' => new MemberResource($updated->load(['user', 'saver'])),
        ]);
    }
}
