<?php

namespace App\Services;

use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use ZipArchive;

class MemberExcelImportService
{
    /**
     * Import members from an uploaded Excel (.xlsx) or CSV file.
     *
     * @return array{
     *     success: bool,
     *     total_rows: int,
     *     imported: int,
     *     skipped: int,
     *     errors: array<int, string>
     * }
     */
    public function import(UploadedFile|string $file): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $originalName = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($filePath);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $rows = [];
        if ($extension === 'xlsx') {
            $rows = $this->parseXlsx($filePath);
        } elseif (in_array($extension, ['csv', 'txt'], true)) {
            $rows = $this->parseCsv($filePath);
        } else {
            return [
                'success' => false,
                'total_rows' => 0,
                'imported' => 0,
                'skipped' => 0,
                'errors' => ["Format berkas .{$extension} tidak didukung. Simpan berkas sebagai .xlsx atau .csv."],
            ];
        }

        if (empty($rows)) {
            return [
                'success' => false,
                'total_rows' => 0,
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['File kosong atau format data tidak dapat dibaca.'],
            ];
        }

        // Find header row and map columns
        $headerInfo = $this->detectHeaderRow($rows);
        if ($headerInfo === null) {
            return [
                'success' => false,
                'total_rows' => count($rows),
                'imported' => 0,
                'skipped' => count($rows),
                'errors' => ['Kolom header tidak dikenali. Pastikan file memiliki minimal kolom "NIS" dan "Nama".'],
            ];
        }

        $headerRowIndex = $headerInfo['row_index'];
        $columnMap = $headerInfo['column_map'];

        $totalRows = 0;
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $defaultPassword = Hash::make('password123');
        $seenNisInFile = [];

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip completely empty rows
            $hasContent = false;
            foreach ($row as $cell) {
                if (trim((string) $cell) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (! $hasContent) {
                continue;
            }

            $totalRows++;
            $rowNum = $i + 1;

            $nis = isset($columnMap['nis']) && isset($row[$columnMap['nis']])
                ? trim((string) $row[$columnMap['nis']])
                : '';
            $name = isset($columnMap['name']) && isset($row[$columnMap['name']])
                ? trim((string) $row[$columnMap['name']])
                : '';
            $className = isset($columnMap['class_name']) && isset($row[$columnMap['class_name']])
                ? trim((string) $row[$columnMap['class_name']])
                : null;
            $major = isset($columnMap['major']) && isset($row[$columnMap['major']])
                ? trim((string) $row[$columnMap['major']])
                : null;
            $graduationYear = isset($columnMap['graduation_year']) && isset($row[$columnMap['graduation_year']])
                ? trim((string) $row[$columnMap['graduation_year']])
                : null;
            $absen = isset($columnMap['absen']) && isset($row[$columnMap['absen']])
                ? trim((string) $row[$columnMap['absen']])
                : null;
            $gender = isset($columnMap['gender']) && isset($row[$columnMap['gender']])
                ? strtoupper(trim((string) $row[$columnMap['gender']]))
                : null;
            $academicYear = isset($columnMap['academic_year']) && isset($row[$columnMap['academic_year']])
                ? trim((string) $row[$columnMap['academic_year']])
                : null;
            $email = isset($columnMap['email']) && isset($row[$columnMap['email']])
                ? trim((string) $row[$columnMap['email']])
                : '';
            $phone = isset($columnMap['phone']) && isset($row[$columnMap['phone']])
                ? trim((string) $row[$columnMap['phone']])
                : null;
            $rawPassword = isset($columnMap['password']) && isset($row[$columnMap['password']])
                ? trim((string) $row[$columnMap['password']])
                : null;

            // Clean name from symbols
            $name = trim(str_replace(['*', '`'], ['', "'"], $name));
            $name = preg_replace('/\s+/', ' ', $name) ?? $name;

            // Basic validation
            if ($name === '') {
                $skipped++;
                $errors[] = "Baris {$rowNum}: Nama siswa kosong.";

                continue;
            }

            if ($nis === '') {
                $skipped++;
                $errors[] = "Baris {$rowNum} ({$name}): NIS tidak boleh kosong.";

                continue;
            }

            // Check duplicate in file
            if (isset($seenNisInFile[$nis])) {
                $skipped++;
                $errors[] = "Baris {$rowNum} ({$name}): NIS {$nis} duplikat di dalam file.";

                continue;
            }
            $seenNisInFile[$nis] = true;

            // Check if NIS already exists in members or users
            $existingMember = Member::where('nis', $nis)->exists();
            $existingUserByUsername = User::where('username', $nis)->exists();
            if ($existingMember || $existingUserByUsername) {
                $skipped++;
                $errors[] = "Baris {$rowNum} ({$name}): NIS {$nis} sudah terdaftar di sistem.";

                continue;
            }

            // Determine/Infer Major if missing
            if (empty($major) && ! empty($className)) {
                if (preg_match('/\b(RPL|TKJ|DKV|AK|AKL|LPS|MP|BR|BD)\b/i', $className, $mj)) {
                    $major = strtoupper($mj[1]);
                }
            }

            // Determine/Infer Graduation Year & Academic Year dynamically using school calendar (Jul-Jun)
            $academicStartYear = $this->resolveAcademicStartYear();

            if (empty($graduationYear)) {
                $graduationYear = $this->inferGraduationYear($className, $academicStartYear);
            }

            if (empty($academicYear)) {
                $academicYear = "{$academicStartYear} - ".($academicStartYear + 1);
            }

            // Absen integer cast
            $absenInt = is_numeric($absen) ? (int) $absen : null;

            // Gender standard
            if ($gender !== 'L' && $gender !== 'P') {
                $gender = null;
            }

            // Email generation if empty
            if ($email === '') {
                $email = "{$nis}@siswa.bankmini.test";
            }

            // Check if email already used
            if (User::where('email', $email)->exists()) {
                $email = "{$nis}_".time().'@siswa.bankmini.test';
            }

            $passwordHash = ! empty($rawPassword) ? Hash::make($rawPassword) : $defaultPassword;

            try {
                DB::transaction(function () use (
                    $name,
                    $nis,
                    $email,
                    $passwordHash,
                    $phone,
                    $absenInt,
                    $className,
                    $major,
                    $academicYear,
                    $gender,
                    $graduationYear
                ) {
                    $user = User::create([
                        'name' => $name,
                        'username' => $nis,
                        'email' => $email,
                        'password' => $passwordHash,
                        'role' => Role::Nasabah,
                        'phone_number' => $phone,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    Member::create([
                        'user_id' => $user->id,
                        'nis' => $nis,
                        'absen' => $absenInt,
                        'student_name' => $name,
                        'class_name' => $className,
                        'major' => $major,
                        'academic_year' => $academicYear,
                        'gender' => $gender,
                        'graduation_year' => $graduationYear,
                        'account_status' => MemberAccountStatus::Inactive,
                    ]);
                });

                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Baris {$rowNum} ({$name}): Gagal menyimpan (".$e->getMessage().')';
            }
        }

        return [
            'success' => $imported > 0 || empty($errors),
            'total_rows' => $totalRows,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Parse .xlsx file into 2D array of string values.
     *
     * @return array<int, array<int, string>>
     */
    protected function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Read shared strings
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $sharedXmlContent = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXmlContent !== false) {
                $xml = simplexml_load_string($sharedXmlContent);
                if ($xml !== false && isset($xml->si)) {
                    foreach ($xml->si as $si) {
                        if (isset($si->t)) {
                            $sharedStrings[] = (string) $si->t;
                        } elseif (isset($si->r)) {
                            $text = '';
                            foreach ($si->r as $r) {
                                $text .= (string) ($r->t ?? '');
                            }
                            $sharedStrings[] = $text;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }
            }
        }

        // 2. Locate first sheet
        $sheetPath = 'xl/worksheets/sheet1.xml';
        if ($zip->locateName($sheetPath) === false) {
            // Search any sheet
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (is_string($name) && str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                    $sheetPath = $name;
                    break;
                }
            }
        }

        $sheetXmlContent = $zip->getFromName($sheetPath);
        $zip->close();

        if ($sheetXmlContent === false) {
            return [];
        }

        $sheetXml = simplexml_load_string($sheetXmlContent);
        if ($sheetXml === false || ! isset($sheetXml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheetXml->sheetData->row as $rowElem) {
            $rowNum = (int) ($rowElem['r'] ?? (count($rows) + 1));
            $rowCells = [];

            foreach ($rowElem->c as $cellElem) {
                $cellRef = (string) $cellElem['r'];
                $colIndex = $this->cellRefToColumnIndex($cellRef);

                $val = '';
                $type = (string) ($cellElem['t'] ?? '');

                if ($type === 's') {
                    $idx = (int) ($cellElem->v ?? 0);
                    $val = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr' && isset($cellElem->is->t)) {
                    $val = (string) $cellElem->is->t;
                } elseif (isset($cellElem->v)) {
                    $val = (string) $cellElem->v;
                }

                $rowCells[$colIndex] = trim($val);
            }

            if (! empty($rowCells)) {
                // Fill missing column keys with empty string to maintain column indexes
                $maxCol = max(array_keys($rowCells));
                $normalizedRow = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $normalizedRow[$c] = $rowCells[$c] ?? '';
                }
                $rows[$rowNum - 1] = $normalizedRow;
            }
        }

        ksort($rows);

        return array_values($rows);
    }

    /**
     * Convert cell reference like "A1", "BC12" to 0-based column index.
     */
    protected function cellRefToColumnIndex(string $cellRef): int
    {
        if (preg_match('/^([A-Z]+)\d+$/i', $cellRef, $matches)) {
            $colLetters = strtoupper($matches[1]);
            $len = strlen($colLetters);
            $index = 0;
            for ($i = 0; $i < $len; $i++) {
                $index = $index * 26 + (ord($colLetters[$i]) - 64);
            }

            return $index - 1;
        }

        return 0;
    }

    /**
     * Parse CSV file into 2D array of strings.
     *
     * @return array<int, array<int, string>>
     */
    protected function parseCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        // Remove UTF-8 BOM if present
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // Detect delimiter: check first line
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            return [];
        }
        fwrite($stream, $content);
        rewind($stream);

        $rows = [];
        while (($row = fgetcsv($stream, 0, $delimiter)) !== false) {
            $trimmedRow = array_map(fn ($val) => trim((string) $val), $row);
            $rows[] = $trimmedRow;
        }

        fclose($stream);

        return $rows;
    }

    /**
     * Detect which row contains header columns (e.g. NIS, Nama, Kelas).
     *
     * @param  array<int, array<int, string>>  $rows
     * @return array{row_index: int, column_map: array<string, int>}|null
     */
    protected function detectHeaderRow(array $rows): ?array
    {
        $maxCheck = min(10, count($rows));

        for ($i = 0; $i < $maxCheck; $i++) {
            $row = $rows[$i];
            $map = [];

            foreach ($row as $colIdx => $colName) {
                $clean = strtolower(trim((string) $colName));
                $clean = str_replace([' ', '_', '-'], '', $clean);

                if (in_array($clean, ['nis', 'nisn', 'noinduk', 'nomorinduk', 'idsiswa'])) {
                    $map['nis'] = $colIdx;
                } elseif (in_array($clean, ['nama', 'namasiswa', 'namalengkap', 'studentname', 'name'])) {
                    $map['name'] = $colIdx;
                } elseif (in_array($clean, ['kelas', 'class', 'classname', 'rombel'])) {
                    $map['class_name'] = $colIdx;
                } elseif (in_array($clean, ['jurusan', 'major', 'programkeahlian', 'kompetensikeahlian'])) {
                    $map['major'] = $colIdx;
                } elseif (in_array($clean, ['angkatan', 'tahunlulus', 'thnlulus', 'graduationyear', 'tahunangkatan'])) {
                    $map['graduation_year'] = $colIdx;
                } elseif (in_array($clean, ['absen', 'noabsen', 'nomorabsen', 'presensi'])) {
                    $map['absen'] = $colIdx;
                } elseif (in_array($clean, ['jeniskelamin', 'gender', 'jk', 'lp', 'kelamin'])) {
                    $map['gender'] = $colIdx;
                } elseif (in_array($clean, ['tahunpelajaran', 'tahunajaran', 'academicyear', 'tapel'])) {
                    $map['academic_year'] = $colIdx;
                } elseif (in_array($clean, ['email', 'surel', 'emailsiswa'])) {
                    $map['email'] = $colIdx;
                } elseif (in_array($clean, ['notelepon', 'nohp', 'telepon', 'phone', 'phonenumber', 'nowa', 'whatsapp'])) {
                    $map['phone'] = $colIdx;
                } elseif (in_array($clean, ['password', 'katasandi', 'pin'])) {
                    $map['password'] = $colIdx;
                }
            }

            // A row is considered a header if it contains at least 'nis' or 'name'
            if (isset($map['name']) && isset($map['nis'])) {
                return [
                    'row_index' => $i,
                    'column_map' => $map,
                ];
            }
        }

        return null;
    }

    /**
     * Generate template CSV content with UTF-8 BOM for Microsoft Excel.
     */
    public function generateTemplateCsv(): string
    {
        $headers = [
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Angkatan',
            'No Absen',
            'Jenis Kelamin (L/P)',
            'Tahun Pelajaran',
            'No WhatsApp / Telepon',
            'Email',
        ];

        $sampleRows = [
            [
                '19001',
                'Ahmad Fadillah',
                'X RPL 1',
                'RPL',
                '2029',
                '1',
                'L',
                '2026 - 2027',
                '081234567891',
                '',
            ],
            [
                '19002',
                'Siti Nurhaliza',
                'X AKL 2',
                'AKL',
                '2029',
                '2',
                'P',
                '2026 - 2027',
                '081234567892',
                '',
            ],
            [
                '19003',
                'Budi Santoso',
                'XI TKJ 1',
                'TKJ',
                '2028',
                '5',
                'L',
                '2026 - 2027',
                '081234567893',
                '',
            ],
        ];

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $stream = fopen('php://temp', 'r+');
        if ($stream !== false) {
            fputcsv($stream, $headers);
            foreach ($sampleRows as $row) {
                fputcsv($stream, $row);
            }
            rewind($stream);
            $output .= stream_get_contents($stream);
            fclose($stream);
        }

        return $output;
    }

    /**
     * Resolve the starting calendar year of the active school academic year (Jul-Jun).
     */
    protected function resolveAcademicStartYear(): int
    {
        $now = now();

        return $now->month >= 7 ? $now->year : ($now->year - 1);
    }

    /**
     * Infer expected graduation year dynamically from class level and academic year.
     */
    protected function inferGraduationYear(?string $className, int $academicStartYear): string
    {
        if (! empty($className)) {
            $trimmed = trim($className);

            if (preg_match('/^(XII|12)\b/i', $trimmed)) {
                return (string) ($academicStartYear + 1);
            }

            if (preg_match('/^(XI|11)\b/i', $trimmed)) {
                return (string) ($academicStartYear + 2);
            }

            if (preg_match('/^(X|10)\b/i', $trimmed)) {
                return (string) ($academicStartYear + 3);
            }
        }

        return (string) ($academicStartYear + 3);
    }
}
