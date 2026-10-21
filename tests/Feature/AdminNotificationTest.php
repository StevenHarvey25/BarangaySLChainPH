<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mark_submission_notifications_as_read(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $notification = $admin->notifications()->create([
            'id' => 'a4d1ba9c-1a0b-4d44-a87b-100001234570',
            'type' => 'App\\Notifications\\CitizenSubmissionReceived',
            'data' => [
                'title' => 'New permit request',
                'message' => 'A citizen submitted a permit request.',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.notifications.read-all'));

        $response->assertOk()
            ->assertJson(['ok' => true]);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_clicking_an_admin_notification_uses_the_current_host_for_its_destination(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $notification = $admin->notifications()->create([
            'id' => 'a4d1ba9c-1a0b-4d44-a87b-100001234571',
            'type' => 'App\\Notifications\\CitizenSubmissionReceived',
            'data' => [
                'title' => 'New identity verification',
                'message' => 'A citizen submitted an identity verification application.',
                'url' => 'https://barangaychainph.test/admin/identity-verification',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->post('https://barangayslchainph.onrender.com'.route('admin.notifications.read', $notification->id, false));

        $response->assertRedirect('https://barangayslchainph.onrender.com/admin/identity-verification');
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
