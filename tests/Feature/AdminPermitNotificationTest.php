<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminPermitNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_a_permit_creates_the_citizen_notification_immediately(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $citizen->id,
            'reference_number' => 'BRGY-20261021-0002',
            'service_type' => ServiceRequest::BARANGAY_CLEARANCE,
            'applicant_name' => $citizen->name,
            'address' => '1 Main Street',
            'age' => 30,
            'purpose' => 'Employment',
            'status' => ServiceRequest::PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.permits.approve', $serviceRequest));

        $response->assertRedirect();
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $citizen->id,
            'type' => 'App\\Notifications\\ServiceRequestApproved',
        ]);
    }
}
