@use('App\Models\Profile')
@php
    $minCompletion = Profile::minCompletion();
    $missing = collect($profile->completionChecklist())->where('done', false)->take(4);
@endphp
<x-layouts::app title="Mon espace">
    <x-page-header :title="'Bonjour '.$user->first_name" description="Bienvenue dans ton espace membre du Talent Board." />

    <div class="page-container space-y-6">
        @if ($user->isPending() && ! $user->is_admin)
            @include('account.partials.pending-message')
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            {{-- Profil et jauge de complétion --}}
            <section class="card">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <x-completion-ring :value="$profile->completion" :threshold="$minCompletion" class="size-24 sm:size-28" />
                    <div class="min-w-0">
                        <h2 class="section-title">Mon profil</h2>
                        @if ($profile->completion >= $minCompletion)
                            <p class="mt-1 text-sm text-muted">Ton profil est complet. L'équipe Bénin Codeurs le consulte quand elle cherche des compétences pour une mission ou une opportunité : garde-le à jour.</p>
                        @elseif ($profile->completion === 0)
                            <p class="mt-1 text-sm text-muted">Remplis ton profil en 6 étapes courtes. Chaque étape est enregistrée : tu peux t'arrêter et reprendre plus tard.</p>
                        @else
                            <p class="mt-1 text-sm text-muted">Encore {{ $minCompletion - $profile->completion }}&nbsp;% pour un profil complet ({{ $minCompletion }}&nbsp;% minimum). Plus il est précis, mieux l'équipe connaît tes compétences.</p>
                        @endif
                    </div>
                </div>

                @if ($missing->isNotEmpty())
                    <ul class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($missing as $item)
                            <li>
                                <a href="{{ route('profile.edit', ['etape' => $item['step']]) }}" class="flex items-center justify-between gap-2 rounded-lg border border-line bg-paper px-3 py-2.5 text-sm text-ink hover:bg-violet/5">
                                    <span class="min-w-0">{{ $item['label'] }}</span>
                                    <span class="shrink-0 text-xs font-bold text-violet">+{{ $item['weight'] }}&nbsp;%</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('profile.edit') }}" class="btn-primary">{{ $profile->completion === 0 ? 'Remplir mon profil' : 'Modifier mon profil' }}</a>
                    @if ($profile->completion > 0)
                        <a href="{{ route('profile.preview') }}" class="btn-secondary">Voir mon profil</a>
                    @endif
                </div>
            </section>

            <div class="min-w-0 space-y-6">
                <section class="card">
                    <span class="grid size-10 place-items-center rounded-lg bg-violet/10 text-violet" aria-hidden="true">@svg('heroicon-o-light-bulb', 'size-5')</span>
                    <h2 class="section-title mt-4">À quoi sert ton profil ?</h2>
                    <p class="mt-1 text-sm text-muted">
                        Il te fait connaître de l'équipe Bénin Codeurs. Ton profil n'est visible que par l'équipe, qui s'en sert
                        pour proposer des missions et des opportunités aux membres dont les compétences correspondent.
                    </p>
                </section>

                <section class="card">
                    <h2 class="section-title">Mon compte</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">Statut</dt>
                            <dd>
                                <span @class([
                                    'badge',
                                    'bg-lime text-ink' => $user->isApproved(),
                                    'bg-violet/10 text-violet' => $user->isPending(),
                                ])>{{ $user->status->label() }}</span>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">E-mail</dt>
                            <dd class="min-w-0 truncate font-medium" title="{{ $user->email }}">{{ $user->email }}</dd>
                        </div>
                        @if ($user->whatsapp)
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">WhatsApp</dt>
                                <dd class="font-medium">{{ $user->whatsapp }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">Inscrit le</dt>
                            <dd class="font-medium">{{ $user->created_at->translatedFormat('j F Y') }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </div>
</x-layouts::app>
