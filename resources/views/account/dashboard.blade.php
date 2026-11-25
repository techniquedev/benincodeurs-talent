<x-layouts::app title="Mon espace">
    <h1 class="text-3xl font-extrabold tracking-tight text-brand">Bonjour {{ $user->first_name }}</h1>
    <p class="mt-1 text-gray-600">Bienvenue dans ton espace membre du Talent Board.</p>

    <div class="mt-6 space-y-6">
        @if ($user->isPending() && ! $user->is_admin)
            @include('account.partials.pending-message')
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            <section class="card">
                <h2 class="text-lg font-bold text-brand">Mon compte</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Statut</dt>
                        <dd>
                            <span @class([
                                'rounded-full px-3 py-1 text-xs font-bold',
                                'bg-lime text-ink' => $user->isApproved(),
                                'bg-amber-100 text-amber-900' => $user->isPending(),
                            ])>{{ $user->status->label() }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">E-mail</dt>
                        <dd class="truncate font-medium">{{ $user->email }}</dd>
                    </div>
                    @if ($user->whatsapp)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">WhatsApp</dt>
                            <dd class="font-medium">{{ $user->whatsapp }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Inscrit le</dt>
                        <dd class="font-medium">{{ $user->created_at->translatedFormat('j F Y') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="card">
                <h2 class="text-lg font-bold text-brand">Mon profil</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Le formulaire de profil (identité, métier, compétences, situation, réalisations, liens) arrive très bientôt.
                </p>
                @if ($user->isApproved() || $user->is_admin)
                    <a href="{{ route('directory') }}" class="btn-primary mt-5">Voir l'annuaire</a>
                @endif
            </section>
        </div>
    </div>
</x-layouts::app>
