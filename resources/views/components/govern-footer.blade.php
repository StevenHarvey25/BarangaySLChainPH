<footer class="border-t-4 border-[#FFC933] bg-[#0F1B3D] text-white">
    <div class="mx-auto max-w-6xl px-5 py-14">

        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-[1.3fr_1fr_1fr_1fr]">

            {{-- BRAND --}}
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/branding/barangayChainPH-logo.png') }}" alt="Barangay San Luis logo" width="48" height="48"
                        class="h-12 w-12 rounded-full object-cover">
                    <p class="text-lg font-extrabold leading-tight"
                        style="font-family: 'Bricolage Grotesque', 'Inter', sans-serif; letter-spacing: -0.02em;">
                        Barangay San Luis
                        <span class="block text-xs font-medium tracking-normal text-blue-200/70">
                            Antipolo City, Rizal
                        </span>
                    </p>
                </div>

                <p class="mt-5 max-w-xs text-sm leading-6 text-blue-100/75">
                    The official online portal of Barangay San Luis, for public information and
                    barangay services.
                </p>
            </div>

            {{-- QUICK LINKS --}}
            <nav aria-label="Footer">
                <h3 class="text-sm font-bold text-[#FFC933]">Explore</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-blue-100/80">
                    <li><a href="{{ url('/') }}#services" class="transition hover:text-white hover:underline">Services</a></li>
                    <li><a href="{{ url('/') }}#announcements" class="transition hover:text-white hover:underline">Announcements</a></li>
                    <li><a href="{{ url('/') }}#about" class="transition hover:text-white hover:underline">About</a></li>
                    <li><a href="{{ route('login') }}" class="transition hover:text-white hover:underline">Log in</a></li>
                    <li><a href="{{ route('register') }}" class="transition hover:text-white hover:underline">Create account</a></li>
                </ul>
            </nav>

            {{-- CONTACT --}}
            <div>
                <h3 class="text-sm font-bold text-[#FFC933]">Contact</h3>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-blue-200/60">Address</dt>
                        <dd class="mt-0.5 text-blue-50">Barangay San Luis, Antipolo City, Rizal</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-blue-200/60">Phone</dt>
                        <dd class="mt-0.5">
                            <a href="tel:+639233232322" class="text-blue-50 transition hover:text-[#FFC933]">+63 923 323 2322</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-blue-200/60">Email</dt>
                        <dd class="mt-0.5 break-all">
                            <a href="mailto:barangaychain@gmail.com" class="text-blue-50 transition hover:text-[#FFC933]">barangaychain@gmail.com</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-blue-200/60">Facebook</dt>
                        <dd class="mt-0.5">
                            <a href="https://www.facebook.com/barangay.sanluis.35" target="_blank"
                                rel="noopener noreferrer"
                                class="font-semibold text-[#FFC933] underline-offset-4 hover:underline">
                                Visit our Facebook page
                            </a>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- OFFICE HOURS --}}
            <div>
                <h3 class="text-sm font-bold text-[#FFC933]">Office hours</h3>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="font-semibold text-blue-50">Monday to Friday</dt>
                        <dd class="mt-0.5 text-blue-100/75">8:00 AM to 5:00 PM</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-blue-50">Saturday and Sunday</dt>
                        <dd class="mt-0.5 text-blue-100/75">Closed</dd>
                    </div>
                </dl>
            </div>

        </div>

        {{-- COPYRIGHT --}}
        <div class="mt-12 flex flex-col gap-2 border-t border-white/15 pt-6 text-xs text-blue-200/60 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} Barangay San Luis. All rights reserved.</p>
            <p>Republic of the Philippines, Antipolo City, Rizal</p>
        </div>

    </div>
</footer>