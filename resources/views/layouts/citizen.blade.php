<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Citizen Portal') | Barangay San Luis</title>

    <link rel="icon" type="image/png" href="{{ asset('img/branding/barangayChainPH-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-800">
    @php
        $user = auth()->user();
        $photo = $user->citizenProfile?->photo_path;
        $initial = strtoupper(substr($user->name, 0, 1));

        $navLinks = [
            'citizen.dashboard' => 'Home',
            'citizen.services' => 'Services',
            'citizen.applications' => 'Applications',
            'citizen.documents' => 'Documents',
        ];

        $hideNotifications = request()->routeIs(
            'permits.verify',
            'citizen.applications.show',
            'citizen.applications.print',
            'citizen.certificate-of-indigency.preview',
            'citizen.barangay-clearance.preview',
            'citizen.certificate-of-residency.preview',
        );

        // Notifications for the bell: latest 10, and how many are unread
        $notifications = $hideNotifications ? collect() : $user->notifications()->latest()->limit(10)->get();
        $unreadCount = $hideNotifications ? 0 : $user->unreadNotifications()->count();

        // Notification look for each status
        $notifColors = [
            'verified' => 'bg-emerald-50 text-emerald-500',
            'rejected' => 'bg-red-50 text-red-500',
            'info' => 'bg-orange-50 text-orange-500',
        ];
        $notifIcons = [
            'verified' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'rejected' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
            'info' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
        ];

        // Identity badge: [label, classes]
        $identityBadge = match (true) {
            $user->isIdentityVerified() => ['Verified', 'bg-green-100 text-green-700'],
            $user->identityStatus() === 'pending' => ['Pending', 'bg-amber-100 text-amber-700'],
            default => ['Required', 'bg-red-100 text-red-700'],
        };
    @endphp

    {{-- Government bar --}}
    <div class="bg-blue-950 text-xs text-white">
        <div class="mx-auto flex h-9 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <span class="truncate text-blue-100">Official Barangay Website</span>
            <span class="hidden text-blue-200 sm:block">Republic of the Philippines | Antipolo City, Rizal</span>
        </div>
    </div>

    {{-- Header --}}
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <x-partials.logo />

            {{-- Desktop navigation --}}
            <nav class="hidden gap-1 lg:flex" aria-label="Main navigation">
                @foreach ($navLinks as $route => $label)
                    @php $active = request()->routeIs($route); @endphp
                    <a href="{{ route($route) }}"
                       @class([
                           'rounded-md px-3 py-2 text-sm font-semibold',
                           'bg-slate-100 text-[#17325c]' => $active,
                           'text-slate-600 hover:bg-slate-50' => ! $active,
                       ])
                       @if ($active) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            {{-- Bell + user menu --}}
            <div class="flex shrink-0 items-center gap-1 sm:gap-2">

                {{-- Notification bell --}}
                @unless ($hideNotifications)
                    <details id="notification-menu" class="group sm:relative">
                        <summary class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full text-slate-600 hover:bg-slate-100 [&::-webkit-details-marker]:hidden"
                                 aria-label="Open notifications">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>

                            @if ($unreadCount > 0)
                                <span id="notification-count" data-count="{{ $unreadCount }}"
                                      class="absolute right-0 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold leading-none text-white">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </summary>

                        <div class="absolute inset-x-4 top-full z-40 mt-2 rounded-xl border border-slate-200 bg-white shadow-xl sm:inset-x-auto sm:right-0 sm:w-96">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Notifications</p>
                                    <p id="notification-subtitle" class="mt-0.5 text-xs text-slate-500">
                                        {{ $unreadCount > 0 ? 'You have '.$unreadCount.' new '.\Illuminate\Support\Str::plural('notification', $unreadCount).'.' : "You're all caught up." }}
                                    </p>
                                </div>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h5l1 3h6l1-3h5M3 13V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v7M3 13v5a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5" />
                                    </svg>
                                </span>
                            </div>

                            {{-- Timeline list --}}
                            <div class="max-h-96 overflow-y-auto px-4 py-4">
                                @forelse ($notifications as $notification)
                                    @php
                                        $type = $notification->data['status'] ?? 'info';
                                        $unread = $notification->read_at === null;
                                    @endphp

                                    <div data-notification class="relative pb-5 last:pb-0">
                                        @unless ($loop->last)
                                            <span class="absolute bottom-1 left-4.5 top-10 w-px -translate-x-1/2 bg-slate-200" aria-hidden="true"></span>
                                        @endunless

                                        <form method="POST" action="{{ route('citizen.notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="group flex w-full items-start gap-3 text-left">
                                                <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $notifColors[$type] ?? $notifColors['info'] }}">
                                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $notifIcons[$type] ?? $notifIcons['info'] }}" />
                                                    </svg>
                                                </span>

                                                <span class="min-w-0 flex-1">
                                                    <span class="flex items-start justify-between gap-3">
                                                        <span class="text-[13px] font-semibold text-slate-900 group-hover:underline">
                                                            {{ $notification->data['title'] ?? 'Notification' }}
                                                            @if ($unread)
                                                                <span data-unread-dot class="ml-1 inline-block h-2 w-2 rounded-full bg-[#17325c] align-middle" aria-label="Unread"></span>
                                                            @endif
                                                        </span>
                                                        <span class="shrink-0 whitespace-nowrap text-[11px] text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                                                    </span>
                                                    <span class="mt-0.5 block text-xs text-slate-500">{{ $notification->data['message'] ?? '' }}</span>
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="px-2 py-8 text-center text-sm text-slate-500">No notifications yet.</p>
                                @endforelse
                            </div>

                            {{-- Footer --}}
                            <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-4 py-3">
                                @if ($unreadCount > 0)
                                    <form id="mark-all-form" method="POST" action="{{ route('citizen.notifications.read-all') }}">
                                        @csrf
                                        <button class="text-sm font-semibold text-[#17325c] underline">Mark all as read</button>
                                    </form>
                                @endif

                                <a href="{{ route('citizen.notifications') }}" class="ml-auto text-sm font-semibold text-[#17325c] underline">View all</a>
                            </div>
                        </div>
                    </details>
                @endunless

                {{-- User menu --}}
                <details id="user-menu" class="group relative shrink-0">
                    <summary class="flex cursor-pointer list-none items-center gap-3 rounded-lg p-1.5 hover:bg-slate-100 [&::-webkit-details-marker]:hidden"
                             aria-label="Open user menu">
                        <span class="hidden text-right lg:block">
                            <span class="block max-w-40 truncate text-sm font-bold">{{ $user->name }}</span>
                            <span class="block text-xs text-slate-500">Citizen</span>
                        </span>

                        @if ($photo)
                            <img src="{{ route('citizen.profile.photo.show') }}" alt=""
                                 class="hidden h-10 w-10 rounded-full object-cover lg:block">
                        @else
                            <span class="hidden h-10 w-10 items-center justify-center rounded-full bg-[#17325c] font-bold text-white lg:flex">
                                {{ $initial }}
                            </span>
                        @endif

                        {{-- Mobile menu icon --}}
                        <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#eef5ff] lg:hidden">
                            <svg class="h-6 w-6 group-open:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg class="hidden h-6 w-6 group-open:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </span>
                    </summary>

                    <div class="absolute right-0 z-40 mt-2 w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                        <div class="mb-2 border-b border-slate-100 px-3 pb-3 pt-2">
                            <p class="truncate font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                        </div>

                        {{-- Mobile navigation --}}
                        <nav class="mb-2 border-b border-slate-100 pb-2 lg:hidden" aria-label="Mobile navigation">
                            @foreach ($navLinks as $route => $label)
                                @php $active = request()->routeIs($route); @endphp
                                <a href="{{ route($route) }}"
                                   @class([
                                       'block rounded-md px-3 py-2.5 text-sm',
                                       'bg-slate-100 font-semibold text-[#17325c]' => $active,
                                       'hover:bg-slate-100' => ! $active,
                                   ])
                                   @if ($active) aria-current="page" @endif>
                                    {{ $label }}
                                </a>
                            @endforeach
                        </nav>

                        <a href="{{ route('citizen.identity-verification') }}"
                           class="flex items-center justify-between gap-2 rounded-md px-3 py-2.5 text-sm hover:bg-slate-100">
                            <span>Identity verification</span>
                            <span id="identity-status-badge" data-identity-status="{{ $user->identityStatus() }}"
                                  class="rounded-full px-2 py-0.5 text-xs font-bold {{ $identityBadge[1] }}">
                                {{ $identityBadge[0] }}
                            </span>
                        </a>

                        <a href="{{ route('citizen.profile') }}" class="block rounded-md px-3 py-2.5 text-sm hover:bg-slate-100">
                            My profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-slate-100 pt-2">
                            @csrf
                            <button class="w-full rounded-md px-3 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                Sign out
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main id="citizen-page-content" class="mx-auto min-h-screen w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <x:feedbacks.toast />

        @yield('content')
    </main>

    <x-partials.c-footer />

    <script>
        // Opening the bell marks all notifications as viewed.
        const notificationMenu = document.getElementById('notification-menu');

        notificationMenu?.addEventListener('toggle', async () => {
            if (!notificationMenu.open) return;

            const markAllForm = document.getElementById('mark-all-form');
            const badge = document.getElementById('notification-count');
            const subtitle = document.getElementById('notification-subtitle');

            if (!markAllForm || !badge) return;

            try {
                const response = await fetch(markAllForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: new FormData(markAllForm),
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Unable to mark notifications as read.');

                badge.remove();
                subtitle.textContent = "You're all caught up.";
                document.querySelectorAll('[data-unread-dot]').forEach((dot) => dot.remove());
                markAllForm.remove();
                document.querySelectorAll('.notification-dismiss-form').forEach((form) => form.remove());
            } catch (error) {
                console.error(error);
            }
        });

        // Synchronize the current page without a full reload.
        setInterval(async () => {
            const activeElement = document.activeElement;
            const isEditing = activeElement?.matches('input, textarea, select, [contenteditable="true"]');

            if (document.hidden || isEditing) return;

            const hasUnsavedInput = [...document.querySelectorAll('form input, form textarea, form select')]
                .some((field) => field.type === 'file'
                    ? field.files.length > 0
                    : field.value !== field.defaultValue);

            if (hasUnsavedInput) return;

            try {
                const response = await fetch(location.href, {
                    cache: 'no-store',
                    headers: { 'Accept': 'text/html' },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Unable to synchronize the citizen page.');

                const freshDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
                const freshContent = freshDocument.getElementById('citizen-page-content');
                const currentContent = document.getElementById('citizen-page-content');
                const freshMenu = freshDocument.getElementById('notification-menu');
                const freshIdentityBadge = freshDocument.getElementById('identity-status-badge');
                const identityBadge = document.getElementById('identity-status-badge');

                if (freshContent && currentContent && freshContent.innerHTML !== currentContent.innerHTML) {
                    currentContent.innerHTML = freshContent.innerHTML;
                }

                if (freshMenu && notificationMenu && freshMenu.innerHTML !== notificationMenu.innerHTML) {
                    const isOpen = notificationMenu.open;
                    notificationMenu.innerHTML = freshMenu.innerHTML;
                    notificationMenu.open = isOpen;
                }

                if (freshIdentityBadge && identityBadge) {
                    identityBadge.className = freshIdentityBadge.className;
                    identityBadge.dataset.identityStatus = freshIdentityBadge.dataset.identityStatus;
                    identityBadge.textContent = freshIdentityBadge.textContent;
                }
            } catch (error) {
                console.error(error);
            }
        }, 15000);

        // Dismiss a notification without reloading the page.
        notificationMenu?.addEventListener('submit', async (event) => {
            const form = event.target.closest('.notification-dismiss-form');

            if (!form) return;

            event.preventDefault();

            const item = form.closest('[data-notification]');
            const button = form.querySelector('button');
            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: new FormData(form),
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Unable to dismiss notification.');

                // Show the item as read
                item.querySelector('[data-unread-dot]')?.remove();
                form.remove();

                // Lower the number on the bell and the subtitle
                const badge = document.getElementById('notification-count');
                const subtitle = document.getElementById('notification-subtitle');

                if (badge) {
                    const left = Number(badge.dataset.count) - 1;
                    badge.dataset.count = left;
                    badge.textContent = left > 9 ? '9+' : left;

                    if (subtitle) {
                        subtitle.textContent = left > 0
                            ? `You have ${left} new notification${left === 1 ? '' : 's'}.`
                            : "You're all caught up.";
                    }

                    if (left <= 0) {
                        badge.remove();
                        document.getElementById('mark-all-form')?.remove();
                    }
                }
            } catch (error) {
                console.error(error);
                button.disabled = false;
            }
        });

        // Close the menus when clicking outside or pressing Escape.
        const menus = document.querySelectorAll('#user-menu, #notification-menu');

        document.addEventListener('click', (event) => {
            menus.forEach((menu) => {
                if (!menu.contains(event.target)) menu.removeAttribute('open');
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            menus.forEach((menu) => {
                if (menu.open) {
                    menu.removeAttribute('open');
                    menu.querySelector('summary')?.focus();
                }
            });
        });

        // Opening one menu closes the other.
        menus.forEach((menu) => {
            menu.addEventListener('toggle', () => {
                if (!menu.open) return;

                menus.forEach((other) => {
                    if (other !== menu) other.removeAttribute('open');
                });
            });
        });
    </script>
</body>

</html>