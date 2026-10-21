@props([
    'name',
    'id' => null,
    'label',
    'type' => 'text',
    'value' => null,
    'options' => [],
    'hint' => null,
    'raised' => false,
    'light' => false, // true = white theme
])

@php
    $fieldId = $id ?? $name;
    // Colors for the white theme or the dark theme
    $field = $light
        ? 'border-slate-300 bg-white text-slate-900 hover:border-slate-400 focus:border-[#17325c] focus:ring-[#17325c]/20'
        : 'border-white/10 bg-[#0B2347] text-white hover:border-white/20 focus:border-emerald-400 focus:ring-emerald-400/10';

    // Autofill: paint over the browser's autofill background, keep text and caret readable
    $autofill = $light
        ? '[&:-webkit-autofill]:shadow-[inset_0_0_0_1000px_#ffffff] [&:-webkit-autofill]:[-webkit-text-fill-color:#0f172a] [&:-webkit-autofill]:caret-slate-900'
        : '[&:-webkit-autofill]:shadow-[inset_0_0_0_1000px_#0B2347] [&:-webkit-autofill]:[-webkit-text-fill-color:#ffffff] [&:-webkit-autofill]:caret-white';

    $muted = $light ? 'text-slate-500' : 'text-blue-100/60';
    $accent = $light ? 'peer-focus:text-[#17325c]' : 'peer-focus:text-emerald-400';
    $hover = $light ? 'hover:text-[#17325c] focus-visible:ring-[#17325c]' : 'hover:text-emerald-400 focus-visible:ring-emerald-400';
    $option = $light ? '[&>option]:bg-white' : '[&>option]:bg-[#0B2347]';

    // Placeholder stays hidden until focus (unless "raised")
    $ph = $light ? 'text-slate-400' : 'text-blue-100/40';
    $ph = $raised ? "placeholder:$ph" : "placeholder:text-transparent focus:placeholder:$ph";

    $base = "peer validation-input block w-full rounded-md border pb-1.5 pl-3.5 pt-5 text-base outline-none transition focus:ring-2 disabled:opacity-60 sm:text-sm $field $autofill";
@endphp

<div>
    <div class="relative">

        @if ($type === 'textarea')
            <textarea id="{{ $fieldId }}" name="{{ $name }}"
                {{ $attributes->class([$base, $ph, 'min-h-[7rem] resize-y pr-3.5']) }}>{{ old($name, $value) }}</textarea>

        @elseif ($type === 'select')
            {{-- options: ['value' => 'Label'] --}}
            <select id="{{ $fieldId }}" name="{{ $name }}"
                {{ $attributes->except('placeholder')->class([$base, $option, 'h-14 appearance-none pr-10']) }}>
                <option value="">{{ $attributes->get('placeholder', 'Select an option') }}</option>
                @foreach ($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>

            <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 {{ $muted }}"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>

        @else
            {{-- text, email, tel, number, password, date... --}}
            <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}"
                @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
                {{ $attributes->class([$base, $ph, 'h-14', $type === 'password' ? 'pr-11' : 'pr-3.5']) }}>
        @endif

        {{-- Floating label --}}
        <label for="{{ $fieldId }}"
            class="pointer-events-none absolute left-3.5 text-xs font-semibold uppercase tracking-wider transition-all duration-200 {{ $muted }} {{ $accent }}
            {{ $raised ? 'top-2.5 text-[10px]' : ($type === 'textarea' ? 'top-4' : 'top-1/2 -translate-y-1/2') }}
            peer-focus:top-2.5 peer-focus:translate-y-0 peer-focus:text-[10px]
            peer-not-placeholder-shown:top-2.5 peer-not-placeholder-shown:translate-y-0 peer-not-placeholder-shown:text-[10px]">
            {{ $label }}
        </label>

        {{-- Show / hide password button --}}
        @if ($type === 'password')
            <button type="button" data-password-toggle="{{ $name }}" aria-label="Show password"
                class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-3.5 transition focus-visible:outline-none focus-visible:ring-2 {{ $muted }} {{ $hover }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <g data-password-eye>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </g>
                </svg>
            </button>
        @endif
    </div>

    {{-- Error message (filled by JS on blur, hidden while typing) --}}
    <p data-error-for="{{ $name }}" class="mt-1 hidden text-xs text-red-500"></p>

    @if ($hint)
        <p class="mt-1 text-xs {{ $muted }}">{{ $hint }}</p>
    @endif

    {{ $slot }}
</div>