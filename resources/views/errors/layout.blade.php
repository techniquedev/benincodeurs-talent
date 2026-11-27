{{--
    Gabarit des pages d'erreur. Volontairement autonome (CSS en ligne, sans Vite, sans session ni base) :
    il doit s'afficher même quand le reste de l'application est en panne.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1A0035">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Talent Board Bénin Codeurs</title>
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" href="/favicon.png" type="image/png" sizes="64x64">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; max-width: 100%; overflow-x: clip; }
        body {
            min-height: 100dvh; display: flex; flex-direction: column; overflow-wrap: break-word;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #F6F5FA; color: #1A1523; -webkit-font-smoothing: antialiased;
        }
        header { padding: 0 16px; height: 64px; display: flex; align-items: center; background: #1A0035; }
        .logo { display: inline-flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; font-size: 14px; font-weight: 800; letter-spacing: -.01em; white-space: nowrap; }
        .logo img { display: block; height: 28px; width: auto; max-width: none; flex-shrink: 0; }
        .logo-sep { width: 1px; height: 24px; background: rgba(255, 255, 255, .35); }
        @media (min-width: 640px) { .logo { gap: 12px; font-size: 18px; } .logo img { height: 36px; } .logo-sep { height: 32px; } }
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px 16px; min-width: 0; }
        .box { width: 100%; max-width: 520px; min-width: 0; text-align: center; }
        .code { font-size: clamp(72px, 22vw, 128px); line-height: 1; font-weight: 700; letter-spacing: -.04em; color: #6B21A8; margin: 0; }
        h1 { margin: 16px 0 0; font-size: clamp(24px, 6vw, 32px); line-height: 1.2; font-weight: 600; letter-spacing: -.02em; }
        p { margin: 12px auto 0; max-width: 420px; color: #6B6578; line-height: 1.55; }
        .actions { margin-top: 32px; display: flex; flex-direction: column; gap: 12px; align-items: stretch; }
        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; max-width: 100%; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; }
        .btn-primary { background: #6B21A8; color: #fff; }
        .btn-primary:hover { background: #280050; }
        .btn-ghost { border: 1px solid rgba(107, 101, 120, .4); background: #fff; color: #1A1523; }
        .btn-ghost:hover { background: #F6F5FA; }
        footer { padding: 0 16px 24px; text-align: center; font-size: 12px; color: #6B6578; }
        @media (min-width: 640px) { .actions { flex-direction: row; justify-content: center; } .btn { width: auto; min-width: 200px; } }
    </style>
</head>
<body>
    <header>
        <a href="/" class="logo"><img src="/images/logo-benincodeurs.png" alt="Bénin Codeurs" width="555" height="147"><span class="logo-sep" aria-hidden="true"></span><span>Talent Board</span></a>
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
