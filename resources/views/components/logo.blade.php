@props(['dark' => false])
<a href="{{ url('/') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 font-extrabold tracking-tight']) }}>
    <span class="grid size-9 place-items-center rounded-xl bg-lime text-sm font-black text-ink">BC</span>
    <span class="{{ $dark ? 'text-brand' : 'text-white' }} text-lg leading-tight">
        Talent Board
        <span class="block text-xs font-medium {{ $dark ? 'text-violet' : 'text-white/70' }}">Bénin Codeurs</span>
    </span>
</a>
