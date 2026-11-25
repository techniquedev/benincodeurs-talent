@use('App\Models\Profile')
@php
    $minCompletion = Profile::minCompletion();
    $canBrowse = $user->isApproved() || $user->is_admin;
    $missing = collect($profile->completionChecklist())->where('done', false)->take(4);
@endphp
<x-layouts::app title="Mon espace">
    <x-page-header :title="'Bonjour '.$user->first_name" description="Bienvenue dans ton espace membre du Talent Board." />

    <div class="page-container space-y-6">
        @if ($user->isPending() && ! $user->is_admin)
            @include('account.partials.pending-message')
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            {{-- Profil et jauge de complétion --}}
            <section class="card">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <x-completion-ring :value="$profile->completion" :threshold="$minCompletion" class="size-24 sm:size-28" />
                    <div class="min-w-0">
                        <h2 class="section-title">Mon profil</h2>
                        @if ($profile->isListed())
                            <p class="mt-1 text-sm text-muted">Ton profil est visible dans l'annuaire. Garde-le à jour : c'est ta vitrine auprès de la communauté.</p>
                        @elseif ($profile->completion >= $minCompletion)
                            <p class="mt-1 text-sm text-muted">Ton profil est prêt. Il apparaîtra dans l'annuaire dès que ton compte sera validé.</p>
                        @elseif ($profile->completion === 0)
                            <p class="mt-1 text-sm text-muted">Remplis ton profil en 6 étapes courtes. Chaque étape est enregistrée : tu peux t'arrêter et reprendre plus tard.</p>
                        @else
                            <p class="mt-1 text-sm text-muted">Encore {{ $minCompletion - $profile->completion }}&nbsp;% pour apparaître dans l'annuaire ({{ $minCompletion }}&nbsp;% minimum).</p>
                        @endif
                    </div>
                </div>

                @if ($missing->isNotEmpty())
                    <ul class="mt-5 grid gap-2 sm:grid-cols-2">
                        @foreach ($missing as $item)
                            <li>
                                <a href="{{ route('profile.edit', ['etape' => $item['step']]) }}" class="flex items-center justify-between gap-2 rounded-lg border border-line bg-paper px-3 py-2.5 text-sm text-ink hover:bg-violet/5">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="text-xs font-bold text-violet">+{{ $item['weight'] }}&nbsp;%</span>
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

            <div class="space-y-6">
                @if ($canBrowse)
                    <section class="card relative overflow-hidden">
                        <span class="grid size-10 place-items-center rounded-lg bg-violet/10 text-violet" aria-hidden="true">@svg('heroicon-o-users', 'size-5')</span>
                        <h2 class="section-title mt-4">L'annuaire des talents</h2>
                        <p class="mt-1 text-sm text-muted">Trouve un développeur, un designer ou un expert data de la communauté.</p>
                        <a href="{{ route('directory') }}" class="btn-primary mt-5 w-full sm:w-full">Explorer l'annuaire @svg('heroicon-m-arrow-right', 'size-4', ['aria-hidden' => 'true'])</a>
                    </section>
                @endif

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
                            <dd class="min-w-0 truncate font-medium">{{ $user->email }}</dd>
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
