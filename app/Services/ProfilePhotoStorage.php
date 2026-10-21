<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfilePhotoStorage
{
    public function store(UploadedFile $photo, string $directory): string
    {
        if ($this->driver() === 'local') {
            $storedPath = $photo->store($directory, 'local');

            if (! is_string($storedPath)) {
                throw new RuntimeException('The profile photo could not be stored locally.');
            }

            return $storedPath;
        }

        $path = $photo->hashName($directory);

        Http::withHeaders($this->supabaseHeaders())
            ->timeout(30)
            ->withBody($photo->get(), (string) $photo->getMimeType())
            ->post($this->objectUrl($path))
            ->throw();

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        if ($this->driver() === 'local') {
            $disk = Storage::disk('local');

            if ($disk->exists($path) && ! $disk->delete($path)) {
                throw new RuntimeException('The old profile photo could not be removed.');
            }

            return;
        }

        Http::withHeaders($this->supabaseHeaders())
            ->timeout(30)
            ->withBody(json_encode(['prefixes' => [$path]], JSON_THROW_ON_ERROR), 'application/json')
            ->delete($this->bucketUrl())
            ->throw();
    }

    public function response(string $path): BinaryFileResponse|Response
    {
        if ($this->driver() === 'local') {
            $disk = Storage::disk('local');

            abort_unless($disk->exists($path), 404);

            return response()->file($disk->path($path), [
                'Cache-Control' => 'private, max-age=60',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $photo = Http::withHeaders($this->supabaseHeaders())
            ->timeout(30)
            ->get($this->objectUrl($path, authenticated: true));

        if ($photo->notFound()) {
            abort(404);
        }

        $photo->throw();

        return response($photo->body(), 200, [
            'Cache-Control' => 'private, max-age=60',
            'Content-Disposition' => 'inline',
            'Content-Type' => $this->contentType($path),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function driver(): string
    {
        $driver = config('services.profile_photos.driver');

        if (! in_array($driver, ['local', 'supabase'], true)) {
            throw new RuntimeException('The profile photo storage driver is not supported.');
        }

        return $driver;
    }

    /**
     * @return array<string, string>
     */
    private function supabaseHeaders(): array
    {
        $key = config('services.supabase.service_role_key');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('The Supabase service role key is not configured for profile photos.');
        }

        return [
            'apikey' => $key,
            'Authorization' => 'Bearer '.$key,
        ];
    }

    private function bucketUrl(): string
    {
        return $this->storageUrl().'/object/'.rawurlencode($this->bucket());
    }

    private function storageUrl(): string
    {
        $baseUrl = config('services.supabase.url');

        if (! is_string($baseUrl) || ! filter_var($baseUrl, FILTER_VALIDATE_URL)
            || parse_url($baseUrl, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('The Supabase project URL must be configured with HTTPS.');
        }

        return rtrim($baseUrl, '/').'/storage/v1';
    }

    private function bucket(): string
    {
        $bucket = config('services.supabase.storage_bucket');

        if (! is_string($bucket) || $bucket === '') {
            throw new RuntimeException('The Supabase profile photo bucket must be configured.');
        }

        return $bucket;
    }

    private function objectUrl(string $path, bool $authenticated = false): string
    {
        $segments = array_map('rawurlencode', explode('/', $path));
        $access = $authenticated ? '/authenticated' : '';

        return $this->storageUrl().'/object'.$access.'/'.rawurlencode($this->bucket()).'/'.implode('/', $segments);
    }

    private function contentType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => throw new RuntimeException('The profile photo has an unsupported file type.'),
        };
    }
}
