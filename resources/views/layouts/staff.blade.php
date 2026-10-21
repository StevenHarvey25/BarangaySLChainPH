<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Staff') | BarangayChain PH</title>

    <link rel="icon" type="image/png" href="{{ asset('img/branding/barangayChainPH-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#020A18] text-slate-100 antialiased">

    <header class="border-b border-slate-800 bg-[#071326]">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">

            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('img/branding/barangayChainPH-logo.png') }}" alt="BarangayChain PH" width="40" height="40"
                    class="h-10 w-10 object-contain">

                <div>
                    <p class="font-bold text-white">BarangayChain PH</p>
                    <p class="text-xs text-slate-400">Barangay San Luis, Antipolo City</p>
                </div>
            </a>

            <div class="flex items-center gap-4">
                <span class="hidden text-sm text-slate-300 sm:inline">
                    {{ auth()->user()->name }}
                </span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">
                        Sign Out
                    </button>
                </form>
            </div>

        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x:feedbacks.toast />

        @yield('content')
    </main>

</body>

</html>
