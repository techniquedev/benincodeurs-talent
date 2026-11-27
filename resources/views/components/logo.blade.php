@props(['dark' => false])
{{-- Logo Bénin Codeurs, fin trait vertical, puis « Talent Board ». « dark » : version pour fond clair. --}}
<a href="{{ url('/') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 sm:gap-3']) }}>
    <img src="{{ asset($dark ? 'images/logo-benincodeurs-dark.png' : 'images/logo-benincodeurs.png') }}"
         alt="Bénin Codeurs" width="555" height="147" class="h-7 w-auto max-w-none shrink-0 sm:h-9">
    <span aria-hidden="true" class="h-6 w-px sm:h-8 {{ $dark ? 'bg-brand/25' : 'bg-white/35' }}"></span>
    <span class="text-sm font-extrabold tracking-tight whitespace-nowrap sm:text-lg {{ $dark ? 'text-brand' : 'text-white' }}">Talent Board</span>
</a>
