@props(['message', 'ticket'])

@php
    $sender = $message->user;
    $senderName = $sender?->name ?? 'Former account';
    $profile = $sender?->isCitizen() ? $sender->citizenProfile : $sender?->employeeProfile;
    $photoUrl = $sender && $profile?->photo_path
        ? route('tickets.participants.photo', ['ticket' => $ticket, 'participant' => $sender])
            . '?v=' . ($profile->updated_at?->timestamp ?? '')
        : null;
@endphp

<div class="flex min-w-0 items-center gap-2">
    @if ($photoUrl)
        <img src="{{ $photoUrl }}" alt="" class="h-7 w-7 shrink-0 rounded-full object-cover">
    @else
        <span aria-hidden="true"
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-200 text-[11px] font-bold text-slate-700">
            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($senderName, 0, 1)) }}
        </span>
    @endif

    <span class="truncate text-xs font-semibold opacity-80">{{ $senderName }}</span>
    @if ($sender?->isAdmin())
        <span role="img" aria-label="Verified administrator" title="Verified administrator"
            class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
            <svg viewBox="0 0 16 16" fill="none" class="h-3 w-3" aria-hidden="true">
                <path d="m4 8 2.5 2.5L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </span>
    @endif
</div>
