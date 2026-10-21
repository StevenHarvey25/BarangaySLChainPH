<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitizenNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_a_notification_as_read_uses_the_current_host_for_its_destination(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);
        $notification = $citizen->notifications()->create([
            'id' => 'a4d1ba9c-1a0b-4d44-a87b-100001234567',
            'type' => 'App\\Notifications\\ServiceRequestApproved',
            'data' => ['url' => 'https://barangaychainph.test/citizen/applications/37/ticket'],
        ]);

        $response = $this->actingAs($citizen)
            ->post('https://barangayslchainph.onrender.com'.route('citizen.notifications.read', $notification->id, false));

        $response->assertRedirect('https://barangayslchainph.onrender.com/citizen/applications/37/ticket');
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_opening_the_notification_bell_marks_all_unread_notifications_as_read(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);
        $unreadNotification = $citizen->notifications()->create([
            'id' => 'a4d1ba9c-1a0b-4d44-a87b-100001234568',
            'type' => 'App\\Notifications\\ServiceRequestApproved',
            'data' => ['title' => 'Approved'],
        ]);
        $readNotification = $citizen->notifications()->create([
            'id' => 'a4d1ba9c-1a0b-4d44-a87b-100001234569',
            'type' => 'App\\Notifications\\ServiceRequestRejected',
            'data' => ['title' => 'Rejected'],
            'read_at' => now(),
        ]);

        $response = $this->actingAs($citizen)
            ->postJson(route('citizen.notifications.read-all'));

        $response->assertOk()
            ->assertJson(['ok' => true]);
        $this->assertNotNull($unreadNotification->fresh()->read_at);
        $this->assertNotNull($readNotification->fresh()->read_at);
    }
}
