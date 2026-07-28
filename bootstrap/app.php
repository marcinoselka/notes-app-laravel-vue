<?php

use App\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // App-specific XSRF cookie name so this app doesn't collide with
        // other local Laravel instances sharing the same host on a
        // different port (see App\Http\Middleware\PreventRequestForgery).
        // PreventRequestForgery lives in the "web" group, not the global
        // stack, so it must be swapped via replaceInGroup() — replace()
        // only rewrites the global middleware array and silently has no
        // effect on group members.
        $middleware->replaceInGroup(
            'web',
            Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            PreventRequestForgery::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
