<?php

namespace Database\Seeders;

use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\JournalEntry;
use App\Models\Member;
use App\Models\PinResetRequest;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('Menghapus semua data nasabah dan saver lama...');

        DB::transaction(function () {
            // 1. Hapus riwayat PIN reset request milik saver
            PinResetRequest::query()->delete();

            // 2. Hapus transaksi & jurnal milik saver
            JournalEntry::query()->delete();
            Transaction::query()->delete();

            // 3. Hapus semua data saver (termasuk soft-deleted)
            Saver::withTrashed()->forceDelete();

            // 4. Hapus data member
            Member::query()->delete();

            // 5. Hapus semua user nasabah (biarkan admin & teller tetap ada)
            User::where('role', Role::Nasabah)->delete();
        });

        $this->command?->info('Data nasabah dan saver lama berhasil dibersihkan.');

        // 6. Baca file data siswa
        $filePath = database_path('data/raw_students.txt');
        if (! file_exists($filePath)) {
            $this->command?->error("File {$filePath} tidak ditemukan!");

            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $currentClass = null;
        $currentAcademicYear = '2026 - 2027';
        $currentMajor = null;
        $currentGraduationYear = '2027';

        $students = [];
        $defaultPassword = Hash::make('password123');
        $now = now();

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Deteksi baris KELAS
            if (preg_match('/KELAS\s*:\s*([A-Za-z0-9\s]+)/i', $trimmed, $m)) {
                $rawClass = trim(explode(',', $m[1])[0]);
                $currentClass = preg_replace('/\s+/', ' ', $rawClass);

                // Tentukan Jurusan
                if (preg_match('/\b(RPL|TKJ|DKV|AK|LPS|MP|BR|BD)\b/i', $currentClass, $mj)) {
                    $currentMajor = strtoupper($mj[1]);
                } else {
                    $currentMajor = '-';
                }

                // Tentukan Tahun Lulus / Angkatan berdasarkan tingkat kelas
                if (str_starts_with($currentClass, 'X ')) {
                    $currentGraduationYear = '2029';
                } elseif (str_starts_with($currentClass, 'XI ')) {
                    $currentGraduationYear = '2028';
                } elseif (str_starts_with($currentClass, 'XII ')) {
                    $currentGraduationYear = '2027';
                } else {
                    $currentGraduationYear = '2027';
                }

                continue;
            }

            // Deteksi TAHUN PELAJARAN
            if (preg_match('/TAHUN PELAJARAN\s*([0-9\s\-]+)/i', $trimmed, $m)) {
                $rawYear = trim(explode(',', $m[1])[0]);
                $currentAcademicYear = preg_replace('/\s+/', ' ', $rawYear);
                continue;
            }

            // Deteksi baris siswa: misal "1,18910,ALLIF MUKHLIS SETYAWAN,L,,,,,,,,,,,,,,,"
            // atau ada tanda * atau catatan: "10,18909,DHIENIE HAYATI,P,,,,,,,,,,,,,,,,,No NIS Terakhir"
            if (preg_match('/^(\d+)\s*,\s*(\d+)\s*,\s*([^,]+?)\s*,\s*([LP])/i', $trimmed, $matches)) {
                $absen = (int) $matches[1];
                $nis = trim($matches[2]);
                $rawName = trim($matches[3]);
                $gender = strtoupper(trim($matches[4]));

                // Bersihkan nama dari tanda asterik `*` atau backtick atau spasi berlebih
                $cleanName = trim(str_replace(['*', '`'], ['', "'"], $rawName));
                $cleanName = preg_replace('/\s+/', ' ', $cleanName);

                if (! empty($nis) && ! empty($cleanName) && $currentClass !== null) {
                    $students[] = [
                        'nis' => $nis,
                        'absen' => $absen,
                        'name' => $cleanName,
                        'gender' => $gender,
                        'class_name' => $currentClass,
                        'major' => $currentMajor,
                        'academic_year' => $currentAcademicYear,
                        'graduation_year' => $currentGraduationYear,
                    ];
                }
            }
        }

        $this->command?->info('Total siswa berhasil diparse: '.count($students).' siswa.');

        // 7. Simpan siswa ke database dalam bentuk User & Member Inactive
        $insertedCount = 0;
        $uniqueNisMap = [];

        foreach ($students as $data) {
            $nis = $data['nis'];

            // Cek duplikasi NIS
            if (isset($uniqueNisMap[$nis])) {
                $this->command?->warn("Peringatan: NIS {$nis} ({$data['name']}) terduplikasi di file, dilewati.");
                continue;
            }
            $uniqueNisMap[$nis] = true;

            $email = "{$nis}@siswa.bankmini.test";

            // Buat User Akun
            $user = User::create([
                'name' => $data['name'],
                'username' => $nis,
                'email' => $email,
                'password' => $defaultPassword,
                'role' => Role::Nasabah,
                'is_active' => true,
                'email_verified_at' => $now,
            ]);

            // Buat Member (Status: Inactive / Belum diaktifkan, Menunggu Setoran Awal)
            Member::create([
                'user_id' => $user->id,
                'student_name' => $data['name'],
                'nis' => $nis,
                'absen' => $data['absen'],
                'class_name' => $data['class_name'],
                'major' => $data['major'],
                'academic_year' => $data['academic_year'],
                'gender' => $data['gender'],
                'graduation_year' => $data['graduation_year'],
                'account_status' => MemberAccountStatus::Inactive,
            ]);

            $insertedCount++;
        }

        $this->command?->info("Sukses! Sebanyak {$insertedCount} data calon nasabah siswa berhasil dimasukkan ke sistem dengan status akun BELUM DIAKTIFKAN (Inactive Member).");
    }
}

