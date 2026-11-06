<?php

namespace Database\Seeders;

use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Baca file data siswa
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

        // 2. Simpan siswa ke database secara aman (Non-Destructive FirstOrCreate)
        $insertedCount = 0;
        $uniqueNisMap = [];

        foreach ($students as $data) {
            $nis = $data['nis'];

            // Cek duplikasi NIS di file
            if (isset($uniqueNisMap[$nis])) {
                $this->command?->warn("Peringatan: NIS {$nis} ({$data['name']}) terduplikasi di file, dilewati.");
                continue;
            }
            $uniqueNisMap[$nis] = true;

            $email = "{$nis}@siswa.bankmini.test";

            // Buat User Akun hanya jika belum ada (tidak menimpa akun lama)
            $user = User::firstOrCreate(
                ['username' => $nis],
                [
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => $defaultPassword,
                    'role' => Role::Nasabah,
                    'is_active' => true,
                    'email_verified_at' => $now,
                ]
            );

            // Buat Member hanya jika belum ada (tidak mereset status member yang sudah aktif atau memiliki transaksi)
            $existingMember = Member::where('user_id', $user->id)
                ->orWhere('nis', $nis)
                ->first();

            if (! $existingMember) {
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
        }

        $this->command?->info("Sukses! Sebanyak {$insertedCount} data calon nasabah baru berhasil ditambahkan (data nasabah yang sudah aktif dan riwayat transaksi tetap terjaga utuh).");
    }
}

