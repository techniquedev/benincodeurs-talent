{{--
    Gabarit des pages d'erreur. Volontairement autonome (CSS en ligne, sans Vite, sans session ni base) :
    il doit s'afficher même quand le reste de l'application est en panne.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#280050">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Talent Board Bénin Codeurs</title>
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100dvh; display: flex; flex-direction: column; overflow-x: hidden;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #1a0035; color: #fff; -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(circle at 90% 0%, rgba(109, 35, 153, .55), transparent 45%),
                              radial-gradient(circle at 0% 100%, rgba(212, 240, 31, .14), transparent 40%);
        }
        header { padding: 24px 20px 0; }
        .logo { display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; font-weight: 800; letter-spacing: -.01em; }
        .logo-mark { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 12px; background: #D4F01F; color: #111; font-size: 14px; font-weight: 900; }
        .logo small { display: block; font-size: 12px; font-weight: 500; color: rgba(255, 255, 255, .7); }
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px 20px; }
        .box { width: 100%; max-width: 520px; text-align: center; }
        .code { font-size: clamp(72px, 22vw, 128px); line-height: 1; font-weight: 900; letter-spacing: -.04em; color: #D4F01F; margin: 0; }
        h1 { margin: 16px 0 0; font-size: clamp(24px, 6vw, 32px); line-height: 1.2; font-weight: 800; letter-spacing: -.02em; }
        p { margin: 12px auto 0; max-width: 420px; color: rgba(255, 255, 255, .78); line-height: 1.55; }
        .actions { margin-top: 32px; display: flex; flex-direction: column; gap: 12px; align-items: center; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-width: 220px; padding: 12px 24px; border-radius: 999px; font-weight: 700; text-decoration: none; }
        .btn-primary { background: #D4F01F; color: #111; }
        .btn-primary:hover { background: #c2dd10; }
        .btn-ghost { border: 1px solid rgba(255, 255, 255, .3); color: #fff; }
        .btn-ghost:hover { background: rgba(255, 255, 255, .1); }
        footer { padding: 0 20px 24px; text-align: center; font-size: 12px; color: rgba(255, 255, 255, .55); }
        @media (min-width: 640px) { .actions { flex-direction: row; justify-content: center; } .btn { min-width: 0; } }
    </style>
</head>
<body>
    <header>
        <a href="/" class="logo"><span class="logo-mark">BC</span><span>Talent Board<small>Bénin Codeurs</small></span></a>
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
