@extends('layouts.citizen')

@section('title', 'Citizen Dashboard')

@section('content')
    <div class="space-y-6">
        {{-- Hero --}}
        <section class="grid overflow-hidden rounded-md border border-slate-200 bg-white lg:grid-cols-[1.4fr_1fr]">
            <div class="bg-[#17325c] p-8 text-white sm:p-10">
                <p class="text-sm text-blue-200">Barangay San Luis, Antipolo City</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl">Maasikaso at maaasahang serbisyo, para sa bawat
                    San Luiseño.</h1>
                <p class="mt-4 max-w-lg text-sm leading-6 text-blue-100">Find barangay services, check your identity
                    verification, and keep your account details up to date.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('citizen.services') }}"
                        class="rounded-md bg-white px-5 py-2.5 text-sm font-semibold text-[#17325c] hover:bg-blue-50">View
                        services</a>
                    <a href="{{ route('citizen.applications') }}"
                        class="rounded-md border border-blue-300 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10">My
                        applications</a>
                </div>
            </div>

            <div class="flex flex-col justify-center gap-4 bg-slate-50 p-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/branding/barangayChainPH-logo.png') }}" alt="" width="48" height="48"
                        class="h-12 w-12 rounded-full border border-slate-200 bg-white object-contain p-0.5">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="truncate text-sm text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="border-t border-slate-200 pt-4">
                    <p class="text-sm text-slate-500">Identity status</p>

                    @switch(auth()->user()->identityStatus())
                        @case('verified')
                            <p class="mt-1 text-lg font-bold text-emerald-700">Verified</p>
                        @break

                        @case('pending')
                            <p class="mt-1 text-lg font-bold text-amber-700">Pending review</p>
                        @break

                        @case('rejected')
                            <p class="mt-1 text-lg font-bold text-red-700">Rejected</p>
                        @break

                        @default
                            <p class="mt-1 text-lg font-bold text-slate-600">Not verified</p>
                    @endswitch

                    @unless (auth()->user()->isIdentityVerified())
                        <a href="{{ route('citizen.identity-verification') }}"
                            class="mt-2 inline-block text-sm font-semibold text-[#17325c] underline">
                            @if (auth()->user()->identityStatus() === 'pending')
                                View status
                            @else
                                Verify your account
                            @endif
                        </a>
                    @endunless
                </div>
            </div>
        </section>

        {{-- Services + announcements --}}
        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <section class="rounded-md border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-[#17325c]">Barangay services</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <a href="{{ route('citizen.services') }}"
                        class="flex flex-col items-center gap-2 rounded-md border border-slate-200 p-4 text-center text-sm font-medium text-slate-700 hover:border-[#17325c] hover:bg-slate-50">
                        <svg class="h-7 w-7 text-[#17325c]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" aria-hidden="true">
                            <path d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z" />
                        </svg>
                        Barangay clearance
                    </a>
                    <a href="{{ route('citizen.services') }}"
                        class="flex flex-col items-center gap-2 rounded-md border border-slate-200 p-4 text-center text-sm font-medium text-slate-700 hover:border-[#17325c] hover:bg-slate-50">
                        <svg class="h-7 w-7 text-[#17325c]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" aria-hidden="true">
                            <path d="M3 10.5 12 3l9 7.5M5 9v11h14V9M9 20v-6h6v6" />
                        </svg>
                        Certificate of residency
                    </a>
                    <a href="{{ route('citizen.services') }}"
                        class="flex flex-col items-center gap-2 rounded-md border border-slate-200 p-4 text-center text-sm font-medium text-slate-700 hover:border-[#17325c] hover:bg-slate-50">
                        <svg class="h-7 w-7 text-[#17325c]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" aria-hidden="true">
                            <path d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2ZM14 3v5h5M9 13h6M9 17h6" />
                        </svg>
                        Certificate of indigency
                    </a>
                </div>
                <a href="{{ route('citizen.documents') }}"
                    class="mt-4 inline-block text-sm font-semibold text-[#17325c] underline">Go to my documents</a>
            </section>

            <section class="rounded-md border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-[#17325c]">Announcements and notices</h2>
                {{-- Pass $announcements from the controller: [['title' => '...', 'date' => '...'], ...] --}}
                <ul class="mt-3 divide-y divide-slate-100">
                    @forelse (($announcements ?? []) as $item)
                        <li class="py-3">
                            <p class="text-sm font-medium text-slate-800">{{ $item['title'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $item['date'] }}</p>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-slate-500">No announcements right now. Check back later.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- Profile + support --}}
        <div class="grid gap-6 md:grid-cols-2">
            <section class="rounded-md border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-[#17325c]">Your profile</h2>
                    <a href="{{ route('citizen.profile') }}" class="text-sm font-semibold text-[#17325c] underline">Open
                        profile</a>
                </div>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="font-medium text-slate-800">{{ auth()->user()->citizenProfile?->phone ?: 'Not added' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Date of birth</dt>
                        <dd class="font-medium text-slate-800">
                            {{ auth()->user()->citizenProfile?->date_of_birth?->format('M j, Y') ?? 'Not added' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Address</dt>
                        <dd class="font-medium text-slate-800">
                            {{ auth()->user()->citizenProfile?->full_address ?: 'Add your address to complete your profile' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-md border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-[#17325c]">Citizen support</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Visit your barangay office for help with requests,
                    requirements, fees, and profile changes.</p>
                <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-[#17325c]">
                    <a href="{{ route('citizen.identity-verification') }}" class="underline">Identity verification
                        guide</a>
                    <a href="{{ route('privacy') }}" class="underline">Privacy notice</a>
                </div>
            </section>
        </div>
    </div>
@endsection
