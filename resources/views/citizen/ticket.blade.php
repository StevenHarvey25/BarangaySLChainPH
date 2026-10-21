@extends('layouts.citizen')

@section('title', 'Ticket')

@section('content')
    <div class="mx-auto max-w-3xl space-y-4">
        <a href="{{ route('citizen.applications') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-[#17325c]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M15 18l-6-6 6-6" />
            </svg>
            Back to applications
        </a>

        <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
            {{-- Ticket header --}}
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Ticket</p>
                    <h1 class="mt-0.5 truncate text-base font-bold text-[#09172D]">{{ $application->reference_number }}</h1>
                </div>

                <span @class([
                    'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold capitalize',
                    'bg-green-100 text-green-700' => $ticket->status === 'open',
                    'bg-slate-100 text-slate-700' => $ticket->status !== 'open',
                ])>{{ $ticket->status }}</span>
            </div>

            {{-- Messages --}}
            <div id="messages" class="max-h-112 space-y-3 overflow-y-auto bg-slate-50/60 p-5">
                @foreach ($messages as $message)
                    @php $mine = $message->user_id === auth()->id(); @endphp

                    @if ($message->is_system)
                        <p class="text-center text-xs text-slate-400">{{ $message->body }}</p>
                    @else
                        <div @class(['flex', 'justify-end' => $mine])>
                            <div @class([
                                'max-w-[80%] rounded-lg px-4 py-2.5 text-sm',
                                'bg-[#17325c] text-white' => $mine,
                                'border border-slate-200 bg-white text-slate-900' => ! $mine,
                            ])>
                                <x-tickets.message-sender :message="$message" :ticket="$ticket" />
                                <p class="mt-1 whitespace-pre-line">{{ $message->body }}</p>
                                <p class="mt-1 text-[11px] opacity-70">
                                    {{ $message->created_at->timezone(config('app.timezone'))->format('M j, g:i A') }}
                                </p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Reply form (only while the ticket is open) --}}
            @if ($ticket->status === 'open')
                <form method="POST" action="{{ route('citizen.tickets.reply', $application) }}"
                    class="space-y-2 border-t border-slate-100 p-4">
                    @csrf
                    <textarea name="body" required maxlength="1000" rows="2" placeholder="Write a message to the barangay"
                        class="block w-full rounded-md border border-slate-200 p-3 text-sm text-slate-900 outline-none focus:border-[#17325c] focus:ring-2 focus:ring-[#17325c]/20">{{ old('body') }}</textarea>

                    @error('body')
                        <p class="text-sm font-medium text-red-700">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end">
                        <button class="rounded-md bg-[#17325c] px-5 py-2 text-sm font-semibold text-white hover:bg-[#10244a]">
                            Send message
                        </button>
                    </div>
                </form>
            @else
                <p class="border-t border-slate-100 bg-slate-50 px-4 py-3 text-center text-sm text-slate-500">
                    This ticket is closed. You can no longer send messages.
                </p>
            @endif
        </section>
    </div>

    <script>
        const box = document.getElementById('messages');
        box.scrollTop = box.scrollHeight; // start at the latest message

        // Every 5 seconds, fetch this page again and swap only the message list
        setInterval(async () => {
            if (document.hidden) return; // skip while the tab is in the background

            try {
                const html = await (await fetch(location.href)).text();
                const fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('messages');
                const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;

                if (fresh && fresh.innerHTML !== box.innerHTML) {
                    box.innerHTML = fresh.innerHTML;
                    if (atBottom) box.scrollTop = box.scrollHeight; // follow new messages only if already at the bottom
                }
            } catch (e) {
                // network problem: try again on the next round
            }
        }, 5000);
    </script>
@endsection