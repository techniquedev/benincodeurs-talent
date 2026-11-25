@props(['level'])
{{-- Niveau d'une compétence (App\Enums\SkillLevel) : 4 points. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }} title="{{ $level->label() }}">
    @for ($i = 1; $i <= 4; $i++)
        <span aria-hidden="true" @class(['size-1.5 rounded-full', 'bg-violet' => $i <= $level->rank(), 'bg-line' => $i > $level->rank()])></span>
    @endfor
    <span class="sr-only">{{ $level->label() }}</span>
</span>
