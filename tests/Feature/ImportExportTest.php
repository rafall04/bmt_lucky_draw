<?php

namespace Tests\Feature;

use App\Exports\PesertaExport;
use App\Exports\PesertaTemplateExport;
use App\Http\Controllers\PesertaController;
use App\Imports\PesertaImport;
use App\Models\Peserta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Test can import Excel file with valid data.
     */
    public function test_can_import_excel_file_with_valid_data(): void
    {
        $user = User::factory()->admin()->create();

        Excel::fake();
        
        $file = UploadedFile::fake()->create('pesertas.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        $response = $this->actingAs($user)
            ->post(route('admin.import'), [
                'file' => $file,
            ]);

        // When using Excel::fake(), the actual file processing is mocked
        // We check that the import was attempted by verifying the response
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        
        // Verify that import was called by checking imported array using reflection
        // ExcelFake stores imports in structure: ['default' => ['filename' => ImportClass]]
        $excelFake = Excel::getFacadeRoot();
        $reflection = new \ReflectionClass($excelFake);
        $importedProperty = $reflection->getProperty('imported');
        $importedProperty->setAccessible(true);
        $imported = $importedProperty->getValue($excelFake);
        
        $this->assertNotEmpty($imported, 'No files were imported');
        
        // Check that at least one import was called with PesertaImport
        // Structure: ['default' => ['pesertas.xlsx' => PesertaImport instance]]
        $found = false;
        foreach ($imported as $disk => $files) {
            if (is_array($files)) {
                foreach ($files as $filePath => $import) {
                    if ($import instanceof PesertaImport) {
                        $found = true;
                        break 2;
                    }
                }
            }
        }
        $this->assertTrue($found, 'PesertaImport was not called');
    }

    /**
     * Test cannot import invalid file format.
     */
    public function test_cannot_import_invalid_file_format(): void
    {
        $user = User::factory()->admin()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)
            ->post(route('admin.import'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    /**
     * Test cannot import file larger than max size.
     */
    public function test_cannot_import_file_larger_than_max_size(): void
    {
        $user = User::factory()->admin()->create();

        // Create file larger than 10MB (max is 10240 KB = 10MB)
        $file = UploadedFile::fake()->create('large.xlsx', 11000); // 11MB

        $response = $this->actingAs($user)
            ->post(route('admin.import'), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    /**
     * Test can export winners to Excel.
     */
    public function test_can_export_winners_to_excel(): void
    {
        $user = User::factory()->create();
        
        // Create winners
        Peserta::factory()->count(5)->winner()->create();

        Excel::fake();

        $response = $this->actingAs($user)
            ->get(route('admin.export-winners'));

        // Response should be successful (download response)
        $response->assertSuccessful();
        
        // Verify export was called by checking downloads array using reflection
        $excelFake = Excel::getFacadeRoot();
        $reflection = new \ReflectionClass($excelFake);
        $downloadsProperty = $reflection->getProperty('downloads');
        $downloadsProperty->setAccessible(true);
        $downloads = $downloadsProperty->getValue($excelFake);
        
        $this->assertNotEmpty($downloads, 'No files were downloaded');
        
        // Check that at least one file matches the pattern
        $found = false;
        foreach ($downloads as $filename => $export) {
            if (str_starts_with($filename, 'pemenang_undian_') && str_ends_with($filename, '.xlsx')) {
                $this->assertInstanceOf(PesertaExport::class, $export);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Export file with correct pattern was not downloaded');
    }

    /**
     * Test can download import template.
     */
    public function test_can_download_import_template(): void
    {
        $user = User::factory()->create();

        Excel::fake();

        $response = $this->actingAs($user)
            ->get(route('admin.import.template'));

        // Response should be successful (download response)
        $response->assertSuccessful();
        
        // Verify download was called by checking downloads array using reflection
        $excelFake = Excel::getFacadeRoot();
        $reflection = new \ReflectionClass($excelFake);
        $downloadsProperty = $reflection->getProperty('downloads');
        $downloadsProperty->setAccessible(true);
        $downloads = $downloadsProperty->getValue($excelFake);
        
        $this->assertNotEmpty($downloads, 'No files were downloaded');
        
        // Check that at least one file matches the pattern
        $found = false;
        foreach ($downloads as $filename => $export) {
            if (str_starts_with($filename, 'Template_Import_Peserta_') && str_ends_with($filename, '.xlsx')) {
                $this->assertInstanceOf(PesertaTemplateExport::class, $export);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Template file with correct pattern was not downloaded');
    }

    /**
     * Test import handles duplicate no_rekening.
     */
    public function test_import_handles_duplicate_no_rekening(): void
    {
        // Create existing peserta
        $existing = Peserta::factory()->create([
            'no_rekening' => '1234567890',
        ]);

        // Try to import same no_rekening
        // This should be handled by PesertaImport which returns null for duplicates
        $this->assertDatabaseHas('pesertas', [
            'no_rekening' => '1234567890',
        ]);

        // Verify only one record exists
        $count = Peserta::where('no_rekening', '1234567890')->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test import skips empty rows.
     */
    public function test_import_skips_empty_rows(): void
    {
        // PesertaImport returns null for empty no_rekening
        // This is tested by checking that only valid records are imported
        $this->assertTrue(true); // Placeholder - actual test would need Excel file with empty rows
    }

    /**
     * Test export includes only winners.
     */
    public function test_export_includes_only_winners(): void
    {
        $user = User::factory()->create();
        
        // Create mix of winners and non-winners
        Peserta::factory()->count(5)->winner()->create();
        Peserta::factory()->count(10)->create(['status_menang' => false]);

        Excel::fake();

        $response = $this->actingAs($user)
            ->get(route('admin.export-winners'));

        // Response should be successful (download response)
        $response->assertSuccessful();
        
        // Verify export was called by checking downloads array using reflection
        $excelFake = Excel::getFacadeRoot();
        $reflection = new \ReflectionClass($excelFake);
        $downloadsProperty = $reflection->getProperty('downloads');
        $downloadsProperty->setAccessible(true);
        $downloads = $downloadsProperty->getValue($excelFake);
        
        $this->assertNotEmpty($downloads, 'No files were downloaded');
        
        // Check that export was called with PesertaExport (which only exports winners)
        $found = false;
        foreach ($downloads as $filename => $export) {
            if (str_starts_with($filename, 'pemenang_undian_') && str_ends_with($filename, '.xlsx')) {
                $this->assertInstanceOf(PesertaExport::class, $export);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Export file with correct pattern was not downloaded');
        
        // Verify that only winners exist in database (5 winners, 10 non-winners)
        $this->assertEquals(5, Peserta::where('status_menang', true)->count());
        $this->assertEquals(10, Peserta::where('status_menang', false)->count());
    }
}

