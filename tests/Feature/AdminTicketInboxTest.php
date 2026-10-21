<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTicketInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ticket_conversation_shows_the_citizens_name_and_profile_photo(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'name' => 'Taylor Citizen',
        ]);
        $photoPath = UploadedFile::fake()->image('citizen.png')->store('profile-photos', 'local');
        $citizen->citizenProfile()->create([
            'phone' => '09171234567',
            'photo_path' => $photoPath,
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
        $ticket = $serviceRequest->ticket()->create([
            'user_id' => $citizen->id,
        ]);
        $ticket->messages()->create([
            'user_id' => $citizen->id,
            'body' => 'I submitted my application.',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket));

        $response->assertOk()
            ->assertSee('I submitted my application.')
            ->assertSee($citizen->name)
            ->assertSee(route('tickets.participants.photo', ['ticket' => $ticket, 'participant' => $citizen]), false);

        $this->actingAs($admin)
            ->get(route('tickets.participants.photo', ['ticket' => $ticket, 'participant' => $citizen]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_ticket_conversation_renders_when_a_message_sender_was_deleted(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $citizen = User::factory()->create(['role' => 'citizen']);
        $sender = User::factory()->create(['role' => 'staff']);
        $serviceRequest = ServiceRequest::create([
            'user_id' => $citizen->id,
            'reference_number' => 'BRGY-20261021-0001',
            'service_type' => ServiceRequest::BARANGAY_CLEARANCE,
            'applicant_name' => $citizen->name,
            'address' => '1 Main Street',
            'age' => 30,
            'purpose' => 'Employment',
            'status' => ServiceRequest::PENDING,
        ]);
        $ticket = $serviceRequest->ticket()->create([
            'user_id' => $citizen->id,
        ]);
        $ticket->messages()->create([
            'user_id' => $sender->id,
            'body' => 'Your request is being reviewed.',
        ]);

        $sender->delete();

        $response = $this->actingAs($admin)
            ->get(route('admin.tickets.show', $ticket));

        $response->assertOk()
            ->assertSee('Your request is being reviewed.')
            ->assertSee('Former account');
    }
}
