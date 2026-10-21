<?php

namespace Tests\Feature;

use App\Models\IdentityVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CitizenProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_citizen_can_upload_and_view_profile_photo_from_supabase(): void
    {
        config([
            'services.profile_photos.driver' => 'supabase',
            'services.supabase.url' => 'https://test-project.supabase.co',
            'services.supabase.storage_bucket' => 'profile-photos',
            'services.supabase.service_role_key' => 'test-service-role-key',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://test-project.supabase.co/storage/v1/object/profile-photos/*' => Http::response([], 200),
            'https://test-project.supabase.co/storage/v1/object/authenticated/profile-photos/*' => Http::response(
                'test-citizen-image',
                200,
            ),
        ]);
        $citizen = User::factory()->create([
            'role' => 'citizen',
            'is_active' => true,
        ]);
        $citizen->citizenProfile()->create([
            'phone' => '09171234567',
        ]);
        $citizen->identityVerifications()->create([
            'status' => IdentityVerification::VERIFIED,
            'document_type' => 'National ID',
            'document_path' => 'identity-documents/test-id.png',
        ]);

        $this->actingAs($citizen)
            ->post(route('citizen.profile.photo'), [
                'photo' => UploadedFile::fake()->image('citizen.png'),
            ])
            ->assertRedirect(route('citizen.profile'));

        $path = $citizen->fresh()->citizenProfile->photo_path;
        $this->assertStringStartsWith('profile-photos/', $path);

        Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
            && str_starts_with($request->url(), 'https://test-project.supabase.co/storage/v1/object/profile-photos/')
            && $request->hasHeader('Authorization', 'Bearer test-service-role-key'));

        $this->actingAs($citizen)
            ->get(route('citizen.profile.photo.show'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent('test-citizen-image');
    }
}
