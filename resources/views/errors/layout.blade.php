{{--
    Gabarit des pages d'erreur. Volontairement autonome (CSS en ligne, sans Vite, sans session ni base) :
    il doit s'afficher même quand le reste de l'application est en panne.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Talent Board Bénin Codeurs</title>
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" href="/favicon.png" type="image/png" sizes="64x64">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100dvh; display: flex; flex-direction: column; overflow-x: hidden;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #fafafa; color: #111; -webkit-font-smoothing: antialiased;
        }
        header { padding: 0 20px; height: 64px; display: flex; align-items: center; background: #fff; border-bottom: 1px solid #e8e8ec; }
        .logo { display: inline-flex; align-items: center; gap: 10px; color: #111; text-decoration: none; font-size: 14px; font-weight: 800; letter-spacing: -.01em; white-space: nowrap; }
        .logo img { display: block; height: 28px; width: auto; }
        .logo-sep { width: 1px; height: 24px; background: rgba(40, 0, 80, .2); }
        @media (min-width: 640px) { .logo { gap: 12px; font-size: 18px; } .logo img { height: 36px; } .logo-sep { height: 32px; } }
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px 20px; }
        .box { width: 100%; max-width: 520px; text-align: center; }
        .code { font-size: clamp(72px, 22vw, 128px); line-height: 1; font-weight: 700; letter-spacing: -.04em; color: #6d2399; margin: 0; }
        h1 { margin: 16px 0 0; font-size: clamp(24px, 6vw, 32px); line-height: 1.2; font-weight: 600; letter-spacing: -.02em; }
        p { margin: 12px auto 0; max-width: 420px; color: #4b5563; line-height: 1.55; }
        .actions { margin-top: 32px; display: flex; flex-direction: column; gap: 12px; align-items: center; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-width: 220px; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; }
        .btn-primary { background: #6d2399; color: #fff; }
        .btn-primary:hover { background: #5a1c80; }
        .btn-ghost { border: 1px solid #e8e8ec; background: #fff; color: #111; }
        .btn-ghost:hover { background: #f9fafb; }
        footer { padding: 0 20px 24px; text-align: center; font-size: 12px; color: #6b7280; }
        @media (min-width: 640px) { .actions { flex-direction: row; justify-content: center; } .btn { min-width: 0; } }
    </style>
</head>
<body>
    <header>
        <a href="/" class="logo"><img src="/images/logo-benincodeurs-dark.png" alt="Bénin Codeurs" width="555" height="147"><span class="logo-sep" aria-hidden="true"></span><span>Talent Board</span></a>
    </header>
    <main>
        <div class="box">
            <p class="code">{{ $code }}</p>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            <div class="actions">
                @foreach ($links ?? [['Retour à l\'accueil', '/']] as $i => [$label, $href])
                    <a href="{{ $href }}" class="btn {{ $i === 0 ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </main>
    <footer>© {{ date('Y') }} Bénin Codeurs · Communauté VIP</footer>
</body>
</html>
