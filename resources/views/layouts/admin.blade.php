<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - BarangayChain PH</title>

    <link rel="icon" type="image/png" href="{{ asset('img/branding/barangayChainPH-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $adminNotifications = auth()->user()->notifications()->latest()->limit(10)->get();
    $adminUnreadCount = auth()->user()->unreadNotifications()->count();
    $adminNotificationColors = [
        'verified' => 'bg-emerald-50 text-emerald-500',
        'rejected' => 'bg-red-50 text-red-500',
        'info' => 'bg-orange-50 text-orange-500',
    ];
    $adminNotificationIcons = [
        'verified' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'rejected' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        'info' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
    ];
@endphp

<body class="h-screen overflow-hidden bg-white font-sans text-slate-800 antialiased">

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar (dark) --}}
        <aside
            class="admin-sidebar-scroll fixed inset-y-0 left-0 z-50 hidden h-screen w-64 overflow-y-auto border-r border-[#172A45] bg-[#09172D] text-white md:block">

            {{-- Branding --}}
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-4">
                <img src="{{ asset('img/branding/barangayChainPH-logo.png') }}" alt="BarangayChain PH Logo" width="40" height="40"
                    class="h-10 w-10 shrink-0 rounded-md bg-white object-contain p-1">

                <div class="min-w-0">
                    <p class="whitespace-nowrap font-['Barlow_Condensed'] text-xl font-bold leading-none tracking-wide">
                        <span class="text-white">BARANGAYCHAIN PH</span>
                    </p>
                    <p class="mt-1 truncate text-[10px] font-semibold uppercase tracking-widest text-emerald-300">
                        Administration
                    </p>
                </div>
            </div>

            <nav class="space-y-6 px-3 py-5">

                {{-- Overview --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Overview
                    </p>

                    <x-admin.nav-link href="/admin/dashboard" match="admin/dashboard">
                        <x-slot:icon>
                            <rect x="3" y="3" width="7" height="9" rx="1" />
                            <rect x="14" y="3" width="7" height="5" rx="1" />
                            <rect x="14" y="12" width="7" height="9" rx="1" />
                            <rect x="3" y="16" width="7" height="5" rx="1" />
                        </x-slot:icon>
                        Dashboard
                    </x-admin.nav-link>
                </div>

                {{-- Management --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Management
                    </p>

                    <div class="space-y-1">
                        <x-admin.nav-link href="/admin/users" match="admin/users*">
                            <x-slot:icon>
                                <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="10" cy="7" r="4" />
                                <path d="M20 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </x-slot:icon>
                            Users &amp; Roles
                        </x-admin.nav-link>

                        <x-admin.nav-link href="/admin/identity-verification" match="admin/identity-verification*">
                            <x-slot:icon>
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <circle cx="9" cy="11" r="2" />
                                <path d="M6.17 15a3 3 0 0 1 5.66 0" />
                                <path d="M15 10h3M15 14h3" />
                            </x-slot:icon>
                            Identity Verification
                        </x-admin.nav-link>

                        <x-admin.nav-link href="/admin/permits" match="admin/permits*">
                            <x-slot:icon>
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z" />
                                <path d="M14 2v6h6" />
                                <path d="m9 15 2 2 4-4" />
                            </x-slot:icon>
                            Permit Management
                        </x-admin.nav-link>
                    </div>
                </div>

                {{-- Documents --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Documents
                        &amp; Verification</p>

                    <x-admin.nav-link href="/admin/document-authentication" match="admin/document-authentication*">
                        <x-slot:icon>
                            <path
                                d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                            <path d="m9 12 2 2 4-4" />
                        </x-slot:icon>
                        Document Authentication
                    </x-admin.nav-link>
                </div>

                {{-- Blockchain --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Blockchain
                    </p>

                    <x-admin.nav-link href="/admin/blockchain-ledger" match="admin/blockchain-ledger*">
                        <x-slot:icon>
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </x-slot:icon>
                        Blockchain Ledger
                    </x-admin.nav-link>
                </div>

                {{-- Communication --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                        Communication</p>

                    <x-admin.nav-link href="/admin/tickets" match="admin/tickets*">
                        <x-slot:icon>
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </x-slot:icon>
                        Ticket Messages
                    </x-admin.nav-link>
                </div>

                {{-- Insights and Security --}}
                <div>
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Insights
                        &amp; Security</p>

                    <div class="space-y-1">
                        <x-admin.nav-link href="/admin/reports" match="admin/reports*">
                            <x-slot:icon>
                                <path d="M3 3v18h18" />
                                <path d="M18 17V9M13 17V5M8 17v-3" />
                            </x-slot:icon>
                            Reports &amp; Analytics
                        </x-admin.nav-link>

                        <x-admin.nav-link href="/admin/audit-logs" match="admin/audit-logs*">
                            <x-slot:icon>
                                <rect x="8" y="2" width="8" height="4" rx="1" />
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                                <path d="M8 11h8M8 15h8M8 19h4" />
                            </x-slot:icon>
                            Audit Logs
                        </x-admin.nav-link>
                    </div>
                </div>

            </nav>
        </aside>

        {{-- Main area (white) --}}
        <div class="flex h-screen min-w-0 flex-1 flex-col overflow-hidden md:ml-64">

            {{-- Header --}}
            <header
                class="relative z-40 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">

                <div class="min-w-0">
                    <h1 class="truncate text-lg font-bold text-slate-900">@yield('title', 'Dashboard')</h1>
                    <p class="hidden text-xs text-slate-500 sm:block">Barangay San Luis | Antipolo City</p>
                </div>

                <div class="flex shrink-0 items-center gap-3 sm:gap-4">

                    {{-- Notifications --}}
                    <details id="admin-notification-menu" class="relative">
                        <summary aria-label="Notifications"
                            class="relative flex cursor-pointer list-none rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                                <path d="M10 21h4" />
                            </svg>
                            @if ($adminUnreadCount > 0)
                                <span id="admin-notification-count" data-count="{{ $adminUnreadCount }}"
                                    class="absolute right-0 top-0 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white">
                                    {{ $adminUnreadCount > 9 ? '9+' : $adminUnreadCount }}
                                </span>
                            @endif
                        </summary>

                        <div class="absolute right-0 z-50 mt-2 w-96 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-bold text-slate-900">Notifications</p>
                                <p id="admin-notification-subtitle" class="mt-0.5 text-xs text-slate-500">
                                    {{ $adminUnreadCount > 0 ? 'You have '.$adminUnreadCount.' new '.\Illuminate\Support\Str::plural('notification', $adminUnreadCount).'.' : "You're all caught up." }}
                                </p>
                            </div>

                            <div class="max-h-96 overflow-y-auto px-4 py-4">
                                @forelse ($adminNotifications as $notification)
                                    @php
                                        $type = $notification->data['status'] ?? 'info';
                                        $unread = $notification->read_at === null;
                                    @endphp

                                    <div data-admin-notification class="relative flex gap-3 pb-5 last:pb-0">
                                        @unless ($loop->last)
                                            <span class="absolute bottom-1 left-4.5 top-10 w-px -translate-x-1/2 bg-slate-200" aria-hidden="true"></span>
                                        @endunless

                                        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $adminNotificationColors[$type] ?? $adminNotificationColors['info'] }}">
                                            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $adminNotificationIcons[$type] ?? $adminNotificationIcons['info'] }}" />
                                            </svg>
                                        </span>

                                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}" class="min-w-0 flex-1">
                                            @csrf
                                            <button type="submit" class="group w-full text-left">
                                                <span class="flex items-start justify-between gap-3">
                                                    <span class="text-[13px] font-semibold text-slate-900 group-hover:underline">
                                                        {{ $notification->data['title'] ?? 'Notification' }}
                                                        @if ($unread)
                                                            <span data-admin-unread-dot class="ml-1 inline-block h-2 w-2 rounded-full bg-[#17325c] align-middle" aria-label="Unread"></span>
                                                        @endif
                                                    </span>
                                                    <span class="shrink-0 whitespace-nowrap text-[11px] text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                                                </span>
                                                <span class="mt-0.5 block text-xs text-slate-500">{{ $notification->data['message'] ?? '' }}</span>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="px-2 py-8 text-center text-sm text-slate-500">No notifications yet.</p>
                                @endforelse
                            </div>

                            <div class="flex items-center justify-between border-t border-slate-100 px-4 py-3">
                                @if ($adminUnreadCount > 0)
                                    <form id="admin-mark-all-form" method="POST" action="{{ route('admin.notifications.read-all') }}">
                                        @csrf
                                        <button class="text-xs font-semibold text-[#17325c] underline">Mark all as read</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.notifications.index') }}" class="ml-auto text-xs font-semibold text-[#17325c] underline">
                                    View all
                                </a>
                            </div>
                        </div>
                    </details>

                    <div class="h-8 border-l border-slate-200"></div>

                    {{-- Administrator profile --}}
                    <details id="admin-user-menu" class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-3 rounded-lg p-1.5 hover:bg-slate-100 [&::-webkit-details-marker]:hidden">
                            <span class="hidden text-right sm:block">
                                <span class="block max-w-40 truncate text-sm font-bold text-slate-900">{{ auth()->user()->name }}</span>
                                <span class="block text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Admin</span>
                            </span>
                            @if (auth()->user()->employeeProfile?->photo_path)
                                <img src="{{ route('admin.profile.photo.show') }}?v={{ auth()->user()->employeeProfile?->updated_at?->timestamp }}"
                                    alt="" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#17325c] text-sm font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                        </summary>

                        <div class="absolute right-0 z-50 mt-2 w-72 max-w-[calc(100vw-2rem)] rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            <div class="mb-2 border-b border-slate-100 px-3 pb-3 pt-2">
                                <p class="truncate font-bold text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            </div>

                            <a href="{{ route('admin.profile') }}"
                               class="block rounded-md px-3 py-2.5 text-sm hover:bg-slate-100">
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
            </header>

            {{-- Page content --}}
            <main id="admin-page-content" class="min-h-0 min-w-0 flex-1 overflow-y-auto bg-white p-4 sm:p-6 lg:p-7">
                @yield('content')
            </main>
        </div>
    </div>

</body>

<script>
    const adminNotificationMenu = document.getElementById('admin-notification-menu');
    const adminUserMenu = document.getElementById('admin-user-menu');

    adminNotificationMenu?.addEventListener('toggle', async () => {
        if (!adminNotificationMenu.open) return;

        const form = document.getElementById('admin-mark-all-form');
        const badge = document.getElementById('admin-notification-count');
        const subtitle = document.getElementById('admin-notification-subtitle');

        if (!form || !badge) return;

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

            if (!response.ok) throw new Error('Unable to mark admin notifications as read.');

            badge.remove();
            subtitle.textContent = "You're all caught up.";
            document.querySelectorAll('[data-admin-unread-dot]').forEach((dot) => dot.remove());
            form.remove();
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

            if (!response.ok) throw new Error('Unable to synchronize the admin page.');

            const freshDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
            const freshContent = freshDocument.getElementById('admin-page-content');
            const currentContent = document.getElementById('admin-page-content');
            const freshMenu = freshDocument.getElementById('admin-notification-menu');
            const freshUserMenu = freshDocument.getElementById('admin-user-menu');

            if (freshContent && currentContent && freshContent.innerHTML !== currentContent.innerHTML) {
                currentContent.innerHTML = freshContent.innerHTML;
            }

            if (freshMenu && adminNotificationMenu && freshMenu.innerHTML !== adminNotificationMenu.innerHTML) {
                const isOpen = adminNotificationMenu.open;
                adminNotificationMenu.innerHTML = freshMenu.innerHTML;
                adminNotificationMenu.open = isOpen;
            }

            if (freshUserMenu && adminUserMenu && freshUserMenu.innerHTML !== adminUserMenu.innerHTML) {
                const isOpen = adminUserMenu.open;
                adminUserMenu.innerHTML = freshUserMenu.innerHTML;
                adminUserMenu.open = isOpen;
            }
        } catch (error) {
            console.error(error);
        }
    }, 15000);
    document.addEventListener('click', (event) => {
        [adminNotificationMenu, adminUserMenu].forEach((menu) => {
            if (menu && !menu.contains(event.target)) menu.removeAttribute('open');
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;

        [adminNotificationMenu, adminUserMenu].forEach((menu) => {
            if (menu?.open) {
                menu.removeAttribute('open');
                menu.querySelector('summary')?.focus();
            }
        });
    });
</script>

</html>
