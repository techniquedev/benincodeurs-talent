{{-- Pagination Livewire de l'annuaire : simple sur mobile, numérotée sur grand écran. --}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect([1, $current - 1, $current, $current + 1, $last])->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
        $button = 'inline-flex h-10 min-w-10 items-center justify-center rounded-full px-3 text-sm font-semibold transition';
    @endphp
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="{{ $button }} bg-white text-gray-300 ring-1 ring-line" aria-disabled="true">@svg('heroicon-m-arrow-left', 'size-4', ['aria-hidden' => 'true']) <span class="ml-1 hidden sm:inline">Précédent</span></span>
        @else
            <button type="button" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" wire:click="previousPage('{{ $paginator->getPageName() }}')" class="{{ $button }} bg-white text-brand ring-1 ring-line hover:ring-violet" rel="prev">@svg('heroicon-m-arrow-left', 'size-4', ['aria-hidden' => 'true']) <span class="ml-1 hidden sm:inline">Précédent</span></button>
        @endif

        <p class="text-sm text-gray-600 sm:hidden">Page {{ $current }} sur {{ $last }}</p>

        <ul class="hidden items-center gap-1 sm:flex">
            @foreach ($pages as $index => $page)
                @if ($index > 0 && $page - $pages[$index - 1] > 1)
                    <li class="px-1 text-gray-400" aria-hidden="true">…</li>
                @endif
                <li>
                    @if ($page === $current)
                        <span class="{{ $button }} bg-brand text-white" aria-current="page">{{ $page }}</span>
                    @else
                        <button type="button" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="{{ $button }} text-brand hover:bg-white" aria-label="Page {{ $page }}">{{ $page }}</button>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <button type="button" x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })" wire:click="nextPage('{{ $paginator->getPageName() }}')" class="{{ $button }} bg-white text-brand ring-1 ring-line hover:ring-violet" rel="next"><span class="mr-1 hidden sm:inline">Suivant</span> @svg('heroicon-m-arrow-right', 'size-4', ['aria-hidden' => 'true'])</button>
        @else
            <span class="{{ $button }} bg-white text-gray-300 ring-1 ring-line" aria-disabled="true"><span class="mr-1 hidden sm:inline">Suivant</span> @svg('heroicon-m-arrow-right', 'size-4', ['aria-hidden' => 'true'])</span>
        @endif
    </nav>
@endif
