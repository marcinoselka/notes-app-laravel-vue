<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="xsrf-cookie-name" content="{{ \App\Http\Middleware\PreventRequestForgery::cookieName() }}">
    <title>Notatki</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div id="app">
        <nav class="navbar navbar-expand navbar-light bg-white border-bottom px-3 mb-4">
            <span class="navbar-brand mb-0 h1">📝 Notatki</span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <notification-bell></notification-bell>
                <span class="text-muted small">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Wyloguj</button>
                </form>
            </div>
        </nav>

        <div class="container py-2" style="max-width: 900px">
            <note-manager></note-manager>
        </div>
    </div>
</body>
</html>
