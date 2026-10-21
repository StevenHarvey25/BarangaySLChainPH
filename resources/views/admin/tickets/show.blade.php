@extends('layouts.admin')

@section('title', 'Ticket')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <a href="{{ route('admin.tickets.index') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-[#17325c] hover:text-[#10244a]">
            <span aria-hidden="true">←</span>
            Back to tickets
        </a>

        <div class="admin-card p-6 mb-1">
            {{-- Ticket header --}}
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $ticket->serviceRequest->reference_number }}</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $ticket->serviceRequest->applicant_name }} · {{ $ticket->serviceRequest->user->email }}
                    </p>
                </div>

                <a href="{{ route('admin.permits.show', $ticket->serviceRequest) }}"
                   class="inline-flex rounded-sm border border-[#17325c] px-3 py-1.5 text-xs font-semibold text-[#17325c] hover:bg-slate-50">
                    View request
                </a>
            </div>

            {{-- Messages --}}
            <div id="messages" class="mt-5 space-y-3">
                @foreach ($messages as $message)
                    @if ($message->is_system)
                        {{-- Automatic message from the system --}}
                        <p class="rounded-sm bg-slate-50 px-3 py-2 text-center text-xs text-slate-500">
                            {{ $message->body }}
                        </p>
                    @else
                        <div @class(['flex', 'justify-end' => $message->user_id !== $ticket->user_id])>
                            <div @class([
                                'max-w-[80%] rounded-sm px-4 py-2.5 text-sm',
                                'bg-slate-100 text-slate-900' => $message->user_id === $ticket->user_id,
                                'bg-[#17325c] text-white' => $message->user_id !== $ticket->user_id,
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
        </div>

        {{-- Reply form (only while the ticket is open) --}}
        @if ($ticket->status === 'open')
            <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="admin-card space-y-3 p-5">
                @csrf
                <textarea name="body" required maxlength="1000" rows="3"
                    placeholder="Write a reply to the citizen"
                    class="admin-field w-full">{{ old('body') }}</textarea>

                @error('body')
                    <p class="text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror

                <button class="rounded-sm bg-[#17325c] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#10244a]">
                    Send reply
                </button>
            </form>
        @else
            <p class="rounded-sm bg-slate-50 px-4 py-3 text-center text-sm text-slate-500">
                This ticket is closed.
            </p>
        @endif
    </div>

    <script>
        const box = document.getElementById('messages');
        box.scrollTop = box.scrollHeight;

        // Every 5 seconds, fetch this page again and swap only the message list
        setInterval(async () => {
            if (document.hidden) return;

            try {
                const html = await (await fetch(location.href)).text();
                const fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('messages');
                const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;

                if (fresh && fresh.innerHTML !== box.innerHTML) {
                    box.innerHTML = fresh.innerHTML;
                    if (atBottom) box.scrollTop = box.scrollHeight;
                }
            } catch (e) {
                // network problem: try again on the next round
            }
        }, 5000);
    </script>
@endsection