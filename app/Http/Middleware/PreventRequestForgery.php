<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as BasePreventRequestForgery;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Sets the CSRF cookie under a name derived from APP_KEY instead of
 * Laravel's static default "XSRF-TOKEN". Browsers scope cookies by host
 * only, never by port — so two installs of this project (or any two local
 * Laravel apps) running on different ports of 127.0.0.1/localhost would
 * otherwise silently overwrite each other's XSRF-TOKEN cookie, breaking
 * CSRF validation for whichever tab loses the race. Deriving the name from
 * APP_KEY (unique per `php artisan key:generate`) means every install gets
 * its own cookie name automatically, with no manual config needed.
 */
class PreventRequestForgery extends BasePreventRequestForgery
{
    public static function cookieName(): string
    {
        return 'XSRF_TOKEN_'.substr(hash('sha256', (string) config('app.key')), 0, 12);
    }

    protected function newCookie($request, $config)
    {
        return new Cookie(
            self::cookieName(),
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        );
    }
}
