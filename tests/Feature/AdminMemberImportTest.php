<?php

namespace Tests\Feature;

use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class AdminMemberImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_download_import_template(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/members/template-import');

        $response->assertOk();
        $this->assertTrue(str_contains((string) $response->headers->get('content-type'), 'text/csv'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('NIS', $content);
        $this->assertStringContainsString('Nama Siswa', $content);
        $this->assertStringContainsString('Kelas', $content);
    }

    public function test_admin_can_import_members_via_csv(): void
    {
        $csvContent = "NIS,Nama Siswa,Kelas,Jurusan,Angkatan,No Absen,Jenis Kelamin,Tahun Pelajaran\n".
                      "99001,Raden Wijaya,X RPL 1,RPL,2029,1,L,2026 - 2027\n".
                      "99002,Dyah Pitaloka,X AKL 2,AKL,2029,2,P,2026 - 2027\n";

        $file = UploadedFile::fake()->createWithContent('calon_nasabah.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/admin/members/import', [
            'file' => $file,
        ]);

        $response->assertRedirect('/admin/members');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'nis' => '99001',
            'student_name' => 'Raden Wijaya',
            'class_name' => 'X RPL 1',
            'major' => 'RPL',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => '99001',
            'email' => '99001@siswa.bankmini.test',
            'role' => Role::Nasabah,
        ]);

        $this->assertDatabaseHas('members', [
            'nis' => '99002',
            'student_name' => 'Dyah Pitaloka',
            'class_name' => 'X AKL 2',
            'major' => 'AKL',
            'account_status' => MemberAccountStatus::Inactive,
        ]);
    }

    public function test_non_admin_cannot_import_members(): void
    {
        $teller = User::factory()->create([
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->createWithContent('test.csv', "NIS,Nama\n123,Test");

        $response = $this->actingAs($teller)->post('/admin/members/import', [
            'file' => $file,
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_import_via_api_json(): void
    {
        $csvContent = "NIS,Nama Siswa,Kelas\n99003,Gajah Mada,X RPL 2\n";
        $file = UploadedFile::fake()->createWithContent('data.csv', $csvContent);

        $response = $this->actingAs($this->admin, 'sanctum')->postJson(route('admin.members.import'), [
            'file' => $file,
        ]);

        $response->assertOk()
            ->assertJsonPath('result.imported', 1)
            ->assertJsonPath('result.skipped', 0);

        $this->assertDatabaseHas('members', [
            'nis' => '99003',
            'student_name' => 'Gajah Mada',
            'class_name' => 'X RPL 2',
            'major' => 'RPL',
            'graduation_year' => '2029',
        ]);
    }

    public function test_admin_can_import_members_via_real_xlsx_file(): void
    {
        $rows = [
            ['NIS', 'Nama Siswa', 'Kelas', 'Jurusan'],
            ['99004', 'Hayam Wuruk', 'XI DKV 1', 'DKV'],
        ];

        $file = $this->createFakeXlsx('siswa.xlsx', $rows);

        $response = $this->actingAs($this->admin)->post('/admin/members/import', [
            'file' => $file,
        ]);

        $response->assertRedirect('/admin/members');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'nis' => '99004',
            'student_name' => 'Hayam Wuruk',
            'class_name' => 'XI DKV 1',
            'major' => 'DKV',
            'account_status' => MemberAccountStatus::Inactive,
        ]);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    protected function createFakeXlsx(string $filename, array $rows): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive;
        $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            .'</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            .'</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/></sheets>'
            .'</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        $sharedStrings = [];
        $sheetData = '';
        foreach ($rows as $rIdx => $row) {
            $rowNum = $rIdx + 1;
            $sheetData .= "<row r=\"{$rowNum}\">";
            foreach ($row as $cIdx => $val) {
                $colLetter = chr(65 + $cIdx);
                $cellRef = "{$colLetter}{$rowNum}";
                $sIdx = count($sharedStrings);
                $sharedStrings[] = (string) $val;
                $sheetData .= "<c r=\"{$cellRef}\" t=\"s\"><v>{$sIdx}</v></c>";
            }
            $sheetData .= '</row>';
        }

        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($sharedStrings).'">';
        foreach ($sharedStrings as $s) {
            $ssXml .= '<si><t>'.htmlspecialchars($s, ENT_XML1).'</t></si>';
        }
        $ssXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $ssXml);

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetData.'</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        return new UploadedFile($tempPath, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
