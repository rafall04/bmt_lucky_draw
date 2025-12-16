<?php

namespace Tests\Feature;

use App\Models\Peserta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPemenangTest extends TestCase
{

    /**
     * Test admin can reset pemenang with correct password.
     */
    public function test_admin_can_reset_pemenang_with_correct_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // Create some winners
        $peserta1 = Peserta::factory()->create([
            'status_menang' => true,
            'waktu_menang' => now(),
            'hadiah_didapat' => 'Hadiah 1',
        ]);

        $peserta2 = Peserta::factory()->create([
            'status_menang' => true,
            'waktu_menang' => now(),
            'hadiah_didapat' => 'Hadiah 2',
        ]);

        $response = $this->actingAs($admin)->post('/admin/reset-pemenang', [
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('success');

        // Verify winners are reset
        $peserta1->refresh();
        $peserta2->refresh();

        $this->assertFalse($peserta1->status_menang);
        $this->assertFalse($peserta2->status_menang);
        $this->assertNull($peserta1->hadiah_didapat);
        $this->assertNull($peserta2->hadiah_didapat);
        $this->assertNull($peserta1->waktu_menang);
        $this->assertNull($peserta2->waktu_menang);
    }

    /**
     * Test admin cannot reset pemenang with incorrect password.
     */
    public function test_admin_cannot_reset_pemenang_with_incorrect_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        $peserta = Peserta::factory()->create([
            'status_menang' => true,
            'waktu_menang' => now(),
        ]);

        $response = $this->actingAs($admin)->post('/admin/reset-pemenang', [
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('error');

        // Verify winner is not reset
        $peserta->refresh();
        $this->assertTrue($peserta->status_menang);
    }

    /**
     * Test operator cannot reset pemenang.
     */
    public function test_operator_cannot_reset_pemenang(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($operator)->post('/admin/reset-pemenang', [
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }
}

