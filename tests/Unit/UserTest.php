<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user isAdmin method.
     */
    public function test_user_is_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isOperator());
    }

    /**
     * Test user isOperator method.
     */
    public function test_user_is_operator(): void
    {
        $user = User::factory()->create([
            'role' => 'operator',
        ]);

        $this->assertTrue($user->isOperator());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test password is hashed.
     */
    public function test_password_is_hashed(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * Test password is hidden from serialization.
     */
    public function test_password_is_hidden(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }
}

