<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="xsrf-cookie-name" content="{{ \App\Http\Middleware\PreventRequestForgery::cookieName() }}">
    <title>Logowanie</title>
    @vite(['resources/css/app.css', 'resources/js/login.js'])
</head>
<body class="bg-light">
    <div id="auth-app"></div>
</body>
</html>
