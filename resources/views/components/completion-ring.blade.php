@props(['value' => 0, 'threshold' => 70, 'size' => 'size-24'])
@php
    $value = max(0, min(100, (int) $value));
    $circumference = 2 * M_PI * 42;
    $offset = $circumference * (1 - $value / 100);
    $reached = $value >= $threshold;
@endphp
<div {{ $attributes->merge(['class' => "relative $size shrink-0"]) }} role="img" aria-label="Profil complet à {{ $value }} %">
    <svg viewBox="0 0 100 100" class="size-full -rotate-90">
        <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" class="stroke-line" />
        <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" stroke-linecap="round"
                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                @class(['transition-all duration-700', 'stroke-lime' => $reached, 'stroke-violet' => ! $reached]) />
    </svg>
    <span class="absolute inset-0 grid place-items-center text-lg font-extrabold text-brand">{{ $value }}&nbsp;%</span>
</div>
