{{-- Marque du panneau Filament : styles en ligne, la CSS de Filament ne contient pas les classes du site. --}}
@props(['dark' => false])
<span style="display: inline-flex; align-items: center; gap: .75rem; height: 100%; white-space: nowrap;">
    <img src="{{ asset($dark ? 'images/logo-benincodeurs.png' : 'images/logo-benincodeurs-dark.png') }}"
         alt="Bénin Codeurs" width="555" height="147" style="display: block; height: 100%; width: auto;">
    <span aria-hidden="true" style="width: 1px; height: 80%; background: {{ $dark ? 'rgba(255, 255, 255, .35)' : 'rgba(40, 0, 80, .25)' }};"></span>
    <span style="font-size: 1.05rem; font-weight: 800; letter-spacing: -.01em; color: {{ $dark ? '#ffffff' : '#280050' }};">Talent Board</span>
</span>
