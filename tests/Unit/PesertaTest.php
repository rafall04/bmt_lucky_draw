<?php

namespace Tests\Unit;

use App\Models\Peserta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesertaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test peserta can be created.
     */
    public function test_peserta_can_be_created(): void
    {
        $peserta = Peserta::create([
            'no_rekening' => '1234567890',
            'nama' => 'Test Peserta',
            'alamat' => 'Test Alamat',
            'cabang' => 'Test Cabang',
            'status_menang' => false,
        ]);

        $this->assertDatabaseHas('pesertas', [
            'no_rekening' => '1234567890',
            'nama' => 'Test Peserta',
            'status_menang' => false,
        ]);

        $this->assertFalse($peserta->status_menang);
        $this->assertNull($peserta->waktu_menang);
    }

    /**
     * Test peserta status_menang is cast to boolean.
     */
    public function test_status_menang_is_boolean(): void
    {
        $peserta = Peserta::create([
            'no_rekening' => '1234567891',
            'nama' => 'Test Peserta 2',
            'alamat' => 'Test Alamat',
            'status_menang' => 1,
        ]);

        $this->assertIsBool($peserta->status_menang);
        $this->assertTrue($peserta->status_menang);
    }

    /**
     * Test peserta waktu_menang is cast to datetime.
     */
    public function test_waktu_menang_is_datetime(): void
    {
        $peserta = Peserta::create([
            'no_rekening' => '1234567892',
            'nama' => 'Test Peserta 3',
            'alamat' => 'Test Alamat',
            'status_menang' => true,
            'waktu_menang' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $peserta->waktu_menang);
    }

    /**
     * Test peserta uses soft deletes.
     */
    public function test_peserta_uses_soft_deletes(): void
    {
        $peserta = Peserta::create([
            'no_rekening' => '1234567893',
            'nama' => 'Test Peserta 4',
            'alamat' => 'Test Alamat',
        ]);

        $peserta->delete();

        $this->assertSoftDeleted('pesertas', [
            'no_rekening' => '1234567893',
        ]);

        $this->assertNull(Peserta::find($peserta->id));
        $this->assertNotNull(Peserta::withTrashed()->find($peserta->id));
    }
}

