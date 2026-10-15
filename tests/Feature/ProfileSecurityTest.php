<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_password_and_authenticate(): void
    {
        $this->seed(RoleSeeder::class);
        $role = Role::first();

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role_id' => $role->id,
            'password' => 'OldPassword123!',
            'is_active' => true,
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'John Doe Updated',
            'email' => 'john@example.com',
            'current_password' => 'OldPassword123!',
            'password' => 'NewSecretPassword456!',
            'password_confirmation' => 'NewSecretPassword456!',
        ]);

        $user->refresh();

        // Must be verifiable with Hash::check (verifying single hashing, not double hashed)
        $this->assertTrue(Hash::check('NewSecretPassword456!', $user->password));

        // Attempt authentication
        Auth::logout();
        $this->assertTrue(Auth::attempt([
            'email' => 'john@example.com',
            'password' => 'NewSecretPassword456!',
        ]));
    }
}
