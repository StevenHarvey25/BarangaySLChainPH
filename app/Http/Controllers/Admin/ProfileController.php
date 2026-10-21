<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('admin.profile', [
            'user' => $request->user()->load('employeeProfile'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => [
                'nullable',
                'confirmed',
                'max:16',
                Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ]);

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Your profile was updated successfully.');
    }

    public function photo(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'photo.max' => 'The photo must not be larger than 2 MB.',
        ]);

        $user = $request->user();
        $employeeProfile = $user->employeeProfile;

        abort_unless($employeeProfile, 403);
        $oldPath = $employeeProfile->photo_path;

        $employeeProfile->update([
            'photo_path' => $request->file('photo')->store('admin-profile-photos', 'local'),
        ]);

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Your profile photo was updated.');
    }

    public function showPhoto(Request $request): BinaryFileResponse
    {
        $path = $request->user()->employeeProfile?->photo_path;
        $disk = Storage::disk('local');

        abort_unless($path && $disk->exists($path), 404);

        return response()->file($disk->path($path));
    }
}
