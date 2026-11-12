<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DeleteMemberAction;
use App\Actions\PromoteStudentsAction;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Http\Controllers\Api\Admin\MemberController as ApiMemberController;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportMemberExcelRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\User;
use App\Services\MemberExcelImportService;
use Illuminate\Contracts\View\View;
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
    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->wantsJson()) {
            return app(ApiMemberController::class)->index($request);
        }

        $query = Member::query()->with(['user', 'saver'])->latest('id');

        if ($request->filled('status')) {
            $query->where('account_status', $request->string('status'));
        }

        if ($request->filled('year')) {
            $query->where('graduation_year', $request->string('year'));
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

        $members = $query->paginate(15)->withQueryString();

        $memberSummary = Member::selectRaw('
            COUNT(*) as total_members,
            COUNT(CASE WHEN account_status = ? THEN 1 END) as inactive_count,
            COUNT(CASE WHEN account_status = ? THEN 1 END) as active_count
        ', [MemberAccountStatus::Inactive->value, MemberAccountStatus::Active->value])->first();

        $totalMembers = (int) ($memberSummary?->total_members ?? 0);
        $inactiveCount = (int) ($memberSummary?->inactive_count ?? 0);
        $activeCount = (int) ($memberSummary?->active_count ?? 0);
        $batches = Member::distinct()->whereNotNull('graduation_year')->pluck('graduation_year')->sortDesc();
        $majors = Member::distinct()->whereNotNull('major')->pluck('major')->sort();
        $classes = Member::distinct()->whereNotNull('class_name')->pluck('class_name')->sort();

        return view('admin.members.index', compact(
            'members',
            'totalMembers',
            'inactiveCount',
            'activeCount',
            'batches',
            'majors',
            'classes'
        ));
    }

    public function store(StoreMemberRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return app(ApiMemberController::class)->store($request);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['student_name'],
                'email' => $validated['email'],
                'username' => $validated['username'] ?? null,
                'password' => Hash::make($validated['password'] ?? 'password123'),
                'role' => Role::Nasabah,
                'phone_number' => $validated['phone_number'] ?? null,
                'is_active' => true,
            ]);

            Member::create([
                'user_id' => $user->id,
                'student_name' => $validated['student_name'],
                'class_name' => $validated['class_name'] ?? null,
                'major' => $validated['major'] ?? null,
                'graduation_year' => $validated['graduation_year'],
                'account_status' => MemberAccountStatus::Inactive,
            ]);
        });

        return redirect('/admin/members')->with('success', 'Data calon penabung berhasil didaftarkan. Status menunggu setoran awal aktivasi loket.');
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return app(ApiMemberController::class)->update($request, $member);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($member, $validated) {
            $member->update([
                'student_name' => $validated['student_name'] ?? $member->student_name,
                'class_name' => array_key_exists('class_name', $validated) ? $validated['class_name'] : $member->class_name,
                'major' => array_key_exists('major', $validated) ? $validated['major'] : $member->major,
                'graduation_year' => $validated['graduation_year'] ?? $member->graduation_year,
            ]);

            if ($member->user) {
                $member->user->update([
                    'name' => $validated['student_name'] ?? $member->user->name,
                    'email' => $validated['email'] ?? $member->user->email,
                    'username' => $validated['username'] ?? $member->user->username,
                    'phone_number' => $validated['phone_number'] ?? $member->user->phone_number,
                ]);
            }
        });

        return redirect('/admin/members')->with('success', 'Data calon penabung berhasil diperbarui.');
    }

    public function destroy(Member $member, DeleteMemberAction $deleteMemberAction): RedirectResponse|JsonResponse
    {
        if (request()->wantsJson()) {
            return app(ApiMemberController::class)->destroy($member, $deleteMemberAction);
        }

        $result = $deleteMemberAction->handle($member);

        if (! $result['deleted']) {
            return back()->with('error', $result['message']);
        }

        return redirect('/admin/members')->with('success', $result['message']);
    }

    public function promote(Request $request, PromoteStudentsAction $action): RedirectResponse|JsonResponse
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

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect('/admin/members')->with('success', $result['message']);
    }

    public function promoteMember(Request $request, Member $member, PromoteStudentsAction $action): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'target_class' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['nullable', 'string', 'max:50'],
        ]);

        $oldClass = $member->class_name ?: '(Belum ada kelas)';
        $updated = $action->promoteSingle($member, $validated['target_class'] ?? null, $validated['academic_year'] ?? null);

        $msg = "Siswa {$member->student_name} berhasil dinaikkan dari kelas {$oldClass} ke {$updated->class_name}.";

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $msg,
                'member' => $updated,
            ]);
        }

        return back()->with('success', $msg);
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

    public function importExcel(ImportMemberExcelRequest $request, MemberExcelImportService $importService): RedirectResponse|JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');
        $result = $importService->import($file);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $result['imported'] > 0
                    ? "Berhasil mengimpor {$result['imported']} calon nasabah."
                    : 'Tidak ada data yang berhasil diimpor.',
                'result' => $result,
            ], $result['success'] ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($result['imported'] > 0) {
            $msg = "Berhasil mengimpor {$result['imported']} data calon nasabah.";
            if ($result['skipped'] > 0) {
                $msg .= " Sebanyak {$result['skipped']} data dilewati (duplikat atau data tidak lengkap).";
            }

            return redirect('/admin/members')
                ->with('success', $msg)
                ->with('import_errors', $result['errors']);
        }

        return redirect('/admin/members')
            ->with('error', 'Gagal mengimpor data. Tidak ada baris valid yang tersimpan.')
            ->with('import_errors', $result['errors']);
    }
}
