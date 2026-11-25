@props(['profile' => null, 'user', 'size' => 'size-16', 'pixels' => 160, 'text' => 'text-xl'])
@php
    $url = $profile?->photoUrl($pixels);
    $initials = mb_strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1));
@endphp
@if ($url)
    <img src="{{ $url }}" alt="Photo de {{ $user->name }}" loading="lazy" decoding="async"
         {{ $attributes->merge(['class' => "$size shrink-0 rounded-full object-cover bg-line"]) }}>
@else
    <span aria-hidden="true" {{ $attributes->merge(['class' => "$size $text shrink-0 grid place-items-center rounded-full bg-linear-to-br from-violet to-brand font-extrabold text-lime"]) }}>{{ $initials }}</span>
@endif
