@props(['tagline' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="h-6 w-[3px] shrink-0 rounded-full bg-[#b32025]" aria-hidden="true"></span>
    <svg class="h-7 w-7 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="2.2"/>
        <path d="M5.2 8.5h7.1a3.5 3.5 0 1 1 0 7H8.1" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
        <path d="m9.7 5.3-3.1 3.2 3.1 3.1M14.3 18.7l3.1-3.2-3.1-3.1" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="leading-tight">
        <span class="block text-xl font-bold tracking-tight text-white">TAYA</span>
        @if ($tagline)
            <span class="mt-0.5 block text-xs text-white/55">Detainee Rights &amp; Overstay Alert System</span>
        @endif
    </span>
</span>
