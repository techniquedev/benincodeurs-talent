<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#280050">
<meta name="robots" content="noindex, nofollow">
<title>{{ isset($title) ? $title.' · ' : '' }}Talent Board Bénin Codeurs</title>
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
