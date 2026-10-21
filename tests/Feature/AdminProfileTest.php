<?php

namespace Tests\Feature;

use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_shows_read_only_email_with_modify_dialog(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        EmployeeProfile::create([
            'user_id' => $admin->id,
            'employee_id' => 'BSLCNPH-'.$admin->id,
            'department' => 'Administration',
            'position' => 'Administrator',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.profile'));

        $response->assertOk()
            ->assertSee('Modify')
            ->assertSee('Modify email address')
            ->assertSee('id="email"', false)
            ->assertSee('name="email"', false)
            ->assertSee('type="email"', false)
            ->assertSee('readonly', false)
            ->assertSee('data-password-toggle="current_password"', false)
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false)
            ->assertSee('data-password-strength', false)
            ->assertSee('data-password-strength-container', false)
            ->assertSee('data-password-confirmation', false)
            ->assertSee('data-password-confirmation-feedback', false);
    }

    public function test_admin_can_update_profile_details(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'name' => 'Original Admin',
            'email' => 'original@example.com',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('admin.profile'));
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_admin_can_change_password_with_current_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => 'OldStrong123!',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'current_password' => 'OldStrong123!',
            'password' => 'NewStrong123!',
            'password_confirmation' => 'NewStrong123!',
        ]);

        $response->assertRedirect(route('admin.profile'));
        $this->assertTrue(Hash::check('NewStrong123!', $admin->fresh()->password));
    }

    public function test_admin_can_upload_a_profile_photo(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        EmployeeProfile::create([
            'user_id' => $admin->id,
            'employee_id' => 'BSLCNPH-'.$admin->id,
            'department' => 'Administration',
            'position' => 'Administrator',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.profile.photo'), [
            'photo' => UploadedFile::fake()->image('admin.png'),
        ]);

        $response->assertRedirect(route('admin.profile'));
        $path = $admin->fresh()->employeeProfile->photo_path;

        $this->assertNotNull($path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_admin_profile_photo_requires_an_image(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        EmployeeProfile::create([
            'user_id' => $admin->id,
            'employee_id' => 'BSLCNPH-'.$admin->id,
            'department' => 'Administration',
            'position' => 'Administrator',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.profile.photo'), [
            'photo' => UploadedFile::fake()->create('admin.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('photo');
        $this->assertNull($admin->fresh()->employeeProfile->photo_path);
    }

    public function test_admin_can_view_their_profile_photo(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        EmployeeProfile::create([
            'user_id' => $admin->id,
            'employee_id' => 'BSLCNPH-'.$admin->id,
            'department' => 'Administration',
            'position' => 'Administrator',
        ]);

        $this->actingAs($admin)->post(route('admin.profile.photo'), [
            'photo' => UploadedFile::fake()->image('admin.png'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.profile.photo.show'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }
}
