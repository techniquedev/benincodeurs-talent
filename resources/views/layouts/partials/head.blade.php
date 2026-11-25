<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#ffffff">
<script>document.documentElement.classList.add('js');</script>
<meta name="robots" content="noindex, nofollow">
<title>{{ ! empty($title) ? $title.' · ' : '' }}Talent Board Bénin Codeurs</title>
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
<link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="64x64">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
