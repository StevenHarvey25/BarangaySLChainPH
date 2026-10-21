@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
    <div class="w-full min-w-0">

        {{-- Header --}}
        <header class="mb-5">
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#00229b]">
                Citizen Portal
            </p>

            <h1 class="text-2xl font-bold leading-tight tracking-tight text-[#0F1B3D] sm:text-3xl">
                Sign In to Continue
            </h1>

            <p class="mt-2 text-sm leading-6 text-[#020746]">
                Log in to access your account and barangay services.
            </p>
        </header>

        {{-- Log In / Sign Up tabs --}}
        <div class="mb-5 flex rounded-md border border-[#DDE3F3] bg-[#E6ECFA] p-1">
            <a href="{{ route('login') }}"
                aria-current="page"
                class="flex min-w-0 flex-1 items-center justify-center rounded bg-white px-3 py-2.5 text-sm font-semibold text-[#0F1B3D] shadow-sm">
                Log In
            </a>

            <a href="{{ route('register') }}"
                class="flex min-w-0 flex-1 items-center justify-center rounded px-3 py-2.5 text-sm font-semibold text-[#5A6788] transition hover:text-[#0F1B3D]">
                Sign Up
            </a>
        </div>

        {{-- Errors --}}
        <x-feedbacks.form-errors />

        {{-- Login form --}}
        <form action="{{ route('login.store') }}" method="POST" class="space-y-4" data-lock-on-submit>
            @csrf

            {{-- Email or Employee ID --}}
            <x-form.input
                light
                name="identifier"
                label="Email Address or Employee ID"
                required
                autofocus
                autocomplete="username"
                placeholder="Enter your email or employee ID"
            />

            {{-- Password --}}
            <x-form.input
                light
                name="password"
                label="Password"
                type="password"
                maxlength="30"
                required
                autocomplete="current-password"
                placeholder="Password"
            />

            {{-- Remember me / Forgot password --}}
            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                <x-form.checkbox name="remember">
                    Remember me
                </x-form.checkbox>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-sm font-semibold text-[#1D3FBB] transition hover:text-[#16318F]">
                        Forgot password?
                    </a>
                @endif
            </div>

            {{-- Submit --}}
            <x-form.button type="submit" class="w-full" loading-text="Logging in...">
                LOGIN
            </x-form.button>
        </form>

        {{-- Registration link --}}
        <p class="mt-5 text-center text-sm text-[#3B4868]">
            Don't have an account?
            <a href="{{ route('register') }}"
                class="font-semibold text-[#0F1B3D] transition hover:text-[#1D3FBB]">
                Create an account
            </a>
        </p>

        {{-- OR divider --}}
        <div class="my-4 flex items-center gap-3 text-sm text-gray-500">
            <div class="h-px min-w-0 flex-1 bg-gray-200"></div>
            <span class="shrink-0">or</span>
            <div class="h-px min-w-0 flex-1 bg-gray-200"></div>
        </div>

        {{-- Google login --}}
        <a href="{{ route('google.redirect') }}"
            class="flex w-full min-w-0 items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:px-4">
            <img
                src="https://www.svgrepo.com/show/475656/google-color.svg"
                class="h-5 w-5 shrink-0"
                alt="Google"
            >
            <span>Continue with Google</span>
        </a>

        {{-- Security note --}}
        <p class="mt-3 text-center text-xs leading-5 text-[#5A6788]">
            Your account information is protected.
            Never share your password with anyone.
        </p>

    </div>
@endsection

@section('panel')
    <x-partials.journey-panel :active="0" />
@endsection