@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
    <div class="mx-auto max-w-3xl space-y-5">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">My Profile</h2>
            <p class="mt-1 text-sm text-slate-500">Update the details shown in your administrator account.</p>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-6 flex items-center gap-4 border-b border-slate-100 pb-5">
                @if ($user->employeeProfile?->photo_path)
                    <img src="{{ route('admin.profile.photo.show') }}?v={{ $user->employeeProfile->updated_at?->timestamp }}"
                        alt="Profile photo" class="h-16 w-16 rounded-full object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#17325c] text-xl font-bold text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h3 class="font-semibold text-slate-900">{{ $user->name }}</h3>
                    <p class="text-sm text-slate-500">Administrator</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.photo') }}" enctype="multipart/form-data" class="mb-6">
                @csrf
                <label for="admin-profile-photo"
                    class="block w-fit cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    {{ $user->employeeProfile?->photo_path ? 'Change profile photo' : 'Add profile photo' }}
                    <input id="admin-profile-photo" type="file" name="photo" accept=".jpg,.jpeg,.png" class="hidden"
                        onchange="this.form.submit()">
                </label>
                <p class="mt-1 text-xs text-slate-500">JPG or PNG, up to 2 MB.</p>
                @error('photo')
                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </form>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="pt-7">
                        <x-form.input light placeholder=" " name="name" label="Full name"
                            :value="$user->name" required maxlength="255" />
                    </div>
                    <div>
                        <button type="button" id="open-email-modal"
                            class="mb-1 block ml-auto text-sm font-semibold text-[#17325c] underline-offset-4 hover:underline">
                            Modify
                        </button>
                        <x-form.input light placeholder=" " name="email" label="Email address"
                            type="email" :value="$user->email" readonly
                            class="[&:read-only]:cursor-not-allowed [&:read-only]:bg-slate-100 [&:read-only]:text-slate-600" />
                    </div>
                </div>

                @if ($user->employeeProfile)
                    <dl class="grid gap-4 rounded-lg bg-slate-50 p-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-500">Employee ID</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $user->employeeProfile->employee_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Position</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $user->employeeProfile->position }}</dd>
                        </div>
                    </dl>
                @endif

                <div class="border-t border-slate-100 pt-5">
                    <h3 class="font-semibold text-slate-900">Change password</h3>
                    <p class="mt-1 text-sm text-slate-500">Leave these fields blank to keep your current password.</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-form.input light name="current_password" label="Current password" type="password"
                            autocomplete="current-password" />
                        <x-form.input light name="password" label="New password" type="password"
                            data-password-strength maxlength="16" autocomplete="new-password">
                            <x-feedbacks.password-requirement />
                        </x-form.input>
                        <div>
                            <x-form.input light name="password_confirmation" label="Confirm new password" type="password"
                                data-password-confirmation maxlength="16" autocomplete="new-password"
                                aria-describedby="password_confirmation-feedback">
                                <p id="password_confirmation-feedback" data-password-confirmation-feedback
                                    class="mt-1 hidden text-xs font-medium text-red-600" aria-live="polite">
                                </p>
                            </x-form.input>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button class="rounded-md bg-[#17325c] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#10244a]">
                        Save changes
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div id="email-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4"
        role="dialog" aria-modal="true" aria-labelledby="email-modal-title">
        <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="email-modal-title" class="text-lg font-bold text-slate-900">Modify email address</h2>
                    <p class="mt-1 text-sm text-slate-500">Enter the email address you want to use for this account.</p>
                </div>
                <button type="button" id="close-email-modal" aria-label="Close email dialog"
                    class="rounded-md px-2 py-1 text-xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ old('name', $user->name) }}">

                <div>
                    <x-form.input light id="modal-email" name="email" label="New email address" type="email"
                        :value="$user->email" required maxlength="255" />
                    @error('email')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" id="cancel-email-modal"
                        class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button class="rounded-md bg-[#17325c] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#10244a]">
                        Save email
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const emailModal = document.getElementById('email-modal');
        const modalEmail = document.getElementById('modal-email');

        const closeEmailModal = () => {
            emailModal.classList.add('hidden');
            emailModal.classList.remove('flex');
        };

        document.getElementById('open-email-modal').addEventListener('click', () => {
            emailModal.classList.remove('hidden');
            emailModal.classList.add('flex');
            modalEmail.focus();
            modalEmail.select();
        });

        document.getElementById('close-email-modal').addEventListener('click', closeEmailModal);
        document.getElementById('cancel-email-modal').addEventListener('click', closeEmailModal);
        emailModal.addEventListener('click', (event) => {
            if (event.target === emailModal) closeEmailModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeEmailModal();
        });
    </script>
@endsection
