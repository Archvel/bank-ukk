<?php

namespace App\Actions;

use App\Models\Member;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PromoteStudentsAction
{
    /**
     * Determine the next class name based on the current class name.
     * E.g. "X BD" -> "XI BD", "XI BD" -> "XII BD", "XII BD" -> "Alumni BD"
     */
    public function getNextClassName(?string $currentClass, bool $graduateGradeTwelve = true): ?string
    {
        if (empty($currentClass)) {
            return $currentClass;
        }

        $trimmed = trim($currentClass);

        // Case 1: X -> XI (e.g., "X BD" -> "XI BD", "X RPL 1" -> "XI RPL 1")
        if (preg_match('/^X\s+(.+)$/i', $trimmed, $m)) {
            return 'XI '.$m[1];
        }
        if (strcasecmp($trimmed, 'X') === 0 || $trimmed === '10') {
            return 'XI';
        }

        // Case 2: XI -> XII (e.g., "XI BD" -> "XII BD", "XI RPL 1" -> "XII RPL 1")
        if (preg_match('/^XI\s+(.+)$/i', $trimmed, $m)) {
            return 'XII '.$m[1];
        }
        if (strcasecmp($trimmed, 'XI') === 0 || $trimmed === '11') {
            return 'XII';
        }

        // Case 3: XII -> Alumni (e.g., "XII BD" -> "Alumni BD", "XII RPL 1" -> "Alumni RPL 1")
        if (preg_match('/^XII\s+(.+)$/i', $trimmed, $m)) {
            return $graduateGradeTwelve ? ('Alumni '.$m[1]) : $trimmed;
        }
        if (strcasecmp($trimmed, 'XII') === 0 || $trimmed === '12') {
            return $graduateGradeTwelve ? 'Alumni' : $trimmed;
        }

        // Case 4: Format angka Arab: "10 RPL 1" -> "11 RPL 1", "11 RPL 1" -> "12 RPL 1", "12 RPL 1" -> "Alumni RPL 1"
        if (preg_match('/^10\s+(.+)$/i', $trimmed, $m)) {
            return '11 '.$m[1];
        }
        if (preg_match('/^11\s+(.+)$/i', $trimmed, $m)) {
            return '12 '.$m[1];
        }
        if (preg_match('/^12\s+(.+)$/i', $trimmed, $m)) {
            return $graduateGradeTwelve ? ('Alumni '.$m[1]) : $trimmed;
        }

        return $trimmed;
    }

    /**
     * Promote a single member.
     */
    public function promoteSingle(Member $member, ?string $targetClass = null, ?string $academicYear = null): Member
    {
        $target = $targetClass ?: $this->getNextClassName($member->class_name);

        $payload = ['class_name' => $target];
        if (! empty($academicYear)) {
            $payload['academic_year'] = $academicYear;
        }

        $member->update($payload);

        return $member;
    }

    /**
     * Promote members in bulk.
     *
     * @param array{
     *     mode?: string,
     *     source_level?: string,
     *     source_class?: string,
     *     target_class?: string,
     *     academic_year?: string,
     *     graduate_grade_twelve?: bool
     * } $options
     * @return array{promoted_count: int, graduated_count: int, total_affected: int, message: string}
     */
    public function handle(array $options = []): array
    {
        $mode = $options['mode'] ?? 'all';
        $academicYear = ! empty($options['academic_year']) ? trim($options['academic_year']) : null;
        $graduateGradeTwelve = (bool) ($options['graduate_grade_twelve'] ?? true);

        return DB::transaction(function () use ($mode, $options, $academicYear, $graduateGradeTwelve) {
            $promotedCount = 0;
            $graduatedCount = 0;

            if ($mode === 'class') {
                $sourceClass = $options['source_class'] ?? null;
                if (empty($sourceClass)) {
                    throw new InvalidArgumentException('Kelas asal wajib dipilih.');
                }

                $targetClass = ! empty($options['target_class'])
                    ? trim($options['target_class'])
                    : $this->getNextClassName($sourceClass, $graduateGradeTwelve);

                $query = Member::where('class_name', $sourceClass);
                $count = $query->count();

                $updateData = ['class_name' => $targetClass];
                if ($academicYear) {
                    $updateData['academic_year'] = $academicYear;
                }

                $query->update($updateData);

                if (str_starts_with($targetClass, 'Alumni')) {
                    $graduatedCount = $count;
                } else {
                    $promotedCount = $count;
                }

                $message = "Berhasil menaikkan {$count} siswa dari kelas {$sourceClass} ke {$targetClass}.";
            } elseif ($mode === 'level') {
                $level = strtoupper(trim((string) ($options['source_level'] ?? '')));
                if (! in_array($level, ['X', 'XI', 'XII'], true)) {
                    throw new InvalidArgumentException('Tingkat kelas asal tidak valid (pilih X, XI, atau XII).');
                }

                $members = Member::where(function ($q) use ($level) {
                    $q->where('class_name', 'like', "{$level} %")
                        ->orWhere('class_name', '=', $level);
                })->get();

                foreach ($members as $m) {
                    $newClass = $this->getNextClassName($m->class_name, $graduateGradeTwelve);
                    $update = ['class_name' => $newClass];
                    if ($academicYear) {
                        $update['academic_year'] = $academicYear;
                    }
                    $m->update($update);

                    if (str_starts_with($newClass, 'Alumni')) {
                        $graduatedCount++;
                    } else {
                        $promotedCount++;
                    }
                }

                $message = "Berhasil memproses kenaikan kelas untuk {$members->count()} siswa tingkat {$level}.";
            } else {
                // Mode 'all': Naikkan seluruh siswa bertingkat (XII -> Alumni, XI -> XII, X -> XI)
                // Urutan eksekusi harus dari XII dulu, baru XI, baru X untuk menghindari promosi ganda

                // 1. Proses Kelas XII -> Alumni
                $grade12 = Member::where(function ($q) {
                    $q->where('class_name', 'like', 'XII %')
                        ->orWhere('class_name', '=', 'XII')
                        ->orWhere('class_name', 'like', '12 %')
                        ->orWhere('class_name', '=', '12');
                })->get();

                foreach ($grade12 as $m) {
                    $newClass = $this->getNextClassName($m->class_name, $graduateGradeTwelve);
                    $update = ['class_name' => $newClass];
                    if ($academicYear) {
                        $update['academic_year'] = $academicYear;
                    }
                    $m->update($update);
                    $graduatedCount++;
                }

                // 2. Proses Kelas XI -> XII
                $grade11 = Member::where(function ($q) {
                    $q->where('class_name', 'like', 'XI %')
                        ->orWhere('class_name', '=', 'XI')
                        ->orWhere('class_name', 'like', '11 %')
                        ->orWhere('class_name', '=', '11');
                })->get();

                foreach ($grade11 as $m) {
                    $newClass = $this->getNextClassName($m->class_name, $graduateGradeTwelve);
                    $update = ['class_name' => $newClass];
                    if ($academicYear) {
                        $update['academic_year'] = $academicYear;
                    }
                    $m->update($update);
                    $promotedCount++;
                }

                // 3. Proses Kelas X -> XI
                $grade10 = Member::where(function ($q) {
                    $q->where('class_name', 'like', 'X %')
                        ->orWhere('class_name', '=', 'X')
                        ->orWhere('class_name', 'like', '10 %')
                        ->orWhere('class_name', '=', '10');
                })->get();

                foreach ($grade10 as $m) {
                    $newClass = $this->getNextClassName($m->class_name, $graduateGradeTwelve);
                    $update = ['class_name' => $newClass];
                    if ($academicYear) {
                        $update['academic_year'] = $academicYear;
                    }
                    $m->update($update);
                    $promotedCount++;
                }

                $message = "Kenaikan kelas massal selesai: {$promotedCount} siswa naik kelas (X ke XI, XI ke XII), dan {$graduatedCount} siswa kelas XII dinyatakan lulus/alumni.";
            }

            return [
                'promoted_count' => $promotedCount,
                'graduated_count' => $graduatedCount,
                'total_affected' => $promotedCount + $graduatedCount,
                'message' => $message,
            ];
        });
    }
}
