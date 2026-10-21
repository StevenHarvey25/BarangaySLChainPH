<?php

namespace Tests\Feature;

use App\Models\EmployeeProfile;
use App\Models\ServiceRequest;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CitizenTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_ticket_conversation_shows_message_sender_names_and_admin_profile_photo(): void
    {
        Storage::fake('local');
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
            'name' => 'Taylor Citizen',
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'name' => 'Morgan Administrator',
        ]);
        $photoPath = UploadedFile::fake()->image('admin.png')->store('admin-profile-photos', 'local');
        EmployeeProfile::create([
            'user_id' => $admin->id,
            'employee_id' => 'BSLCNPH-'.$admin->id,
            'department' => 'Administration',
            'position' => 'Administrator',
            'photo_path' => $photoPath,
        ]);
        $ticket = $this->createTicket($citizen);
        $ticket->messages()->create([
            'user_id' => $citizen->id,
            'body' => 'I need help with my application.',
        ]);
        $ticket->messages()->create([
            'user_id' => $admin->id,
            'body' => 'I can help you with that.',
        ]);

        $response = $this->actingAs($citizen)
            ->get(route('citizen.tickets.show', $ticket->serviceRequest));

        $response->assertOk()
            ->assertSee('I need help with my application.')
            ->assertSee('I can help you with that.')
            ->assertSee($citizen->name)
            ->assertSee($admin->name)
            ->assertSee('aria-label="Verified administrator"', false)
            ->assertSee(route('tickets.participants.photo', ['ticket' => $ticket, 'participant' => $admin]), false);

        $this->actingAs($citizen)
            ->get(route('tickets.participants.photo', ['ticket' => $ticket, 'participant' => $admin]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_citizen_cannot_view_participant_photos_from_another_citizens_ticket(): void
    {
        $citizen = User::factory()->create(['role' => 'citizen', 'is_active' => true]);
        $otherCitizen = User::factory()->create(['role' => 'citizen']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $ticket = $this->createTicket($otherCitizen);
        $ticket->messages()->create([
            'user_id' => $admin->id,
            'body' => 'Private conversation.',
        ]);

        $this->actingAs($citizen)
            ->get(route('tickets.participants.photo', ['ticket' => $ticket, 'participant' => $admin]))
            ->assertNotFound();
    }

    public function test_staff_cannot_access_ticket_participant_photos(): void
    {
        $citizen = User::factory()->create(['role' => 'citizen']);
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $ticket = $this->createTicket($citizen);
        $ticket->messages()->create([
            'user_id' => $citizen->id,
            'body' => 'Citizen message.',
        ]);

        $this->actingAs($staff)
            ->get(route('tickets.participants.photo', ['ticket' => $ticket, 'participant' => $citizen]))
            ->assertForbidden();
    }

    private function createTicket(User $citizen): Ticket
    {
        $serviceRequest = ServiceRequest::create([
            'user_id' => $citizen->id,
            'reference_number' => 'BRGY-20261021-'.$citizen->id,
            'service_type' => ServiceRequest::BARANGAY_CLEARANCE,
            'applicant_name' => $citizen->name,
            'address' => '1 Main Street',
            'age' => 30,
            'purpose' => 'Employment',
            'status' => ServiceRequest::PENDING,
        ]);

        return $serviceRequest->ticket()->create([
            'user_id' => $citizen->id,
        ]);
    }
}
