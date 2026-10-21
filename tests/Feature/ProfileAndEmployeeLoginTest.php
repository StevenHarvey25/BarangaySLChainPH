<?php

namespace Tests\Feature;

use App\Models\EmployeeProfile;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProfileAndEmployeeLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_only_requests_basic_account_details(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertDontSee('name="date_of_birth"', false)
            ->assertDontSee('name="civil_status"', false)
            ->assertDontSee('name="nationality"', false)
            ->assertDontSee('name="religion"', false)
            ->assertDontSee('name="house_number"', false)
            ->assertDontSee('name="street"', false)
            ->assertDontSee('name="barangay"', false)
            ->assertDontSee('name="city_municipality"', false)
            ->assertDontSee('name="province"', false)
            ->assertDontSee('name="zip_code"', false);
    }

    public function test_login_form_locks_fields_while_submitting(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('data-lock-on-submit', false)
            ->assertSee('data-loading-text="Logging in..."', false);
    }

    public function test_production_assets_use_the_current_request_host_instead_of_the_configured_asset_host(): void
    {
        app()->detectEnvironment(fn (): string => 'production');
        config(['app.asset_url' => 'https://barangaychainph.onrender.com']);
        URL::useAssetOrigin('https://barangaychainph.onrender.com');

        new AppServiceProvider(app())->boot();

        $response = $this->get('https://barangayslchainph.onrender.com/login');

        $response->assertOk()
            ->assertSee('https://barangayslchainph.onrender.com/build/', false)
            ->assertDontSee('https://barangaychainph.onrender.com/build/', false);
    }

    public function test_privacy_policy_discloses_collected_citizen_details(): void
    {
        $response = $this->get(route('privacy'));

        $response->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('civil status')
            ->assertSee('house/lot/block number')
            ->assertSee('religion are optional');
    }

    public function test_terms_route_displays_terms_of_service(): void
    {
        $response = $this->get(route('terms'));

        $response->assertOk()
            ->assertSee('Terms of Service')
            ->assertSee('Acceptance of Terms');
    }

    public function test_email_verification_creates_a_separate_citizen_profile(): void
    {
        $registration = [
            'name' => 'Jordan Citizen',
            'email' => 'jordan@example.com',
            'password' => Hash::make('StrongPass123!'),
            'phone' => '09171234567',
            'otp_hash' => Hash::make('123456'),
            'otp_expires_at' => now()->addMinutes(5)->timestamp,
        ];

        $response = $this
            ->withSession(['pending_registration' => $registration])
            ->post(route('verification.otp.verify'), ['otp' => '123456']);

        $response->assertRedirect(route('citizen.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'jordan@example.com',
            'role' => 'citizen',
        ]);
        $this->assertNotNull(
            User::where('email', 'jordan@example.com')
                ->firstOrFail()
                ->email_verified_at
        );
        $citizen = User::where('email', 'jordan@example.com')->firstOrFail();
        $this->assertModelExists($citizen->citizenProfile);
        $this->assertSame('09171234567', $citizen->citizenProfile->phone);
        $this->assertNull($citizen->citizenProfile->date_of_birth);
        $this->assertNull($citizen->citizenProfile->civil_status);
        $this->assertNull($citizen->citizenProfile->nationality);
        $this->assertNull($citizen->citizenProfile->address);
        $this->assertNull($citizen->citizenProfile->barangay);
    }

    public function test_registration_accepts_only_basic_details_until_email_verification(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
            'https://api.pwnedpasswords.com/range/*' => Http::response('', 200),
        ]);
        Notification::fake();
        config(['services.recaptcha.secret_key' => 'test-secret']);

        $response = $this->post(route('register.store'), [
            'name' => 'Jordan Citizen',
            'email' => 'jordan@example.com',
            'phone' => '09171234567',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'terms' => '1',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $response->assertRedirect(route('verification.otp.show'));
        $response->assertSessionHas('pending_registration.name', 'Jordan Citizen');
        $response->assertSessionHas('pending_registration.email', 'jordan@example.com');
        $response->assertSessionHas('pending_registration.phone', '09171234567');
        $response->assertSessionMissing('pending_registration.date_of_birth');
        $response->assertSessionMissing('pending_registration.civil_status');
        $this->assertDatabaseMissing('users', ['email' => 'jordan@example.com']);
    }

    public function test_private_storage_files_are_not_served_publicly(): void
    {
        $this->assertFalse(Route::has('storage.local'));
    }

    public function test_employee_can_log_in_with_employee_id(): void
    {
        $user = User::factory()->create([
            'name' => 'Taylor Staff',
            'email' => 'taylor@example.com',
            'password' => 'StrongPass123!',
            'role' => 'staff',
        ]);

        EmployeeProfile::create([
            'user_id' => $user->id,
            'employee_id' => 'BSLCNPH-000123',
            'department' => 'Records',
            'position' => 'Clerk',
            'phone' => '09171234567',
        ]);

        $response = $this->post(route('login.store'), [
            'identifier' => 'bslcnph-000123',
            'password' => 'StrongPass123!',
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_created_staff_accounts_receive_an_employee_id(): void
    {
        Http::fake([
            'https://api.pwnedpasswords.com/range/*' => Http::response('', 200),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Staff / Employee');

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Morgan Employee',
            'email' => 'morgan@example.com',
            'role' => 'staff',
            'department' => 'Records',
            'position' => 'Clerk',
            'phone' => '09170000000',
            'office' => 'Barangay Hall',
            'date_hired' => '2024-01-15',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $employee = User::where('email', 'morgan@example.com')->firstOrFail();
        $this->assertSame('staff', $employee->role);
        $this->assertSame('BSLCNPH-'.str_pad((string) $employee->id, 6, '0', STR_PAD_LEFT), $employee->employeeProfile->employee_id);
        $this->assertSame('Records', $employee->employeeProfile->department);
        $this->assertSame('Barangay Hall', $employee->employeeProfile->office);
        $this->assertSame('2024-01-15', $employee->employeeProfile->date_hired->toDateString());

        $this->actingAs($employee)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->post(route('admin.users.store'), [
                'name' => 'Unauthorized Admin',
                'email' => 'unauthorized.admin@example.com',
                'role' => 'admin',
                'department' => 'Administration',
                'position' => 'Administrator',
                'password' => 'StrongPass123!',
                'password_confirmation' => 'StrongPass123!',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'unauthorized.admin@example.com',
        ]);

        $this->actingAs($employee)
            ->patch(route('admin.users.toggle-status', $employee))
            ->assertForbidden();
    }

    public function test_admin_created_administrator_uses_carlo_admin_default_and_gets_employee_id(): void
    {
        Http::fake([
            'https://api.pwnedpasswords.com/range/*' => Http::response('', 200),
        ]);

        $signedInAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($signedInAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('value="Carlo Admin"', false);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Carlo Admin',
            'email' => 'carlo.admin@example.com',
            'role' => 'admin',
            'department' => 'Administration',
            'position' => 'Administrator',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $carloAdmin = User::where('email', 'carlo.admin@example.com')->firstOrFail();
        $this->assertSame('Carlo Admin', $carloAdmin->name);
        $this->assertSame('BSLCNPH-000002', $carloAdmin->employeeProfile->employee_id);
        $this->assertSame('admin', $carloAdmin->role);
    }
}
