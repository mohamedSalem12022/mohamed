<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleAndTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'ar';
        $theme = 'light';
        $defaultTz = config('app.timezone', 'Asia/Gaza');
        if (!in_array($defaultTz, \DateTimeZone::listIdentifiers(), true)) {
            $defaultTz = 'Asia/Gaza';
        }
        $timezone = $defaultTz;

        // 1. Check user preferences for locale and theme
        if (session()->has('locale')) {
            $locale = session('locale', 'ar');
        } elseif (Auth::check() && Auth::user()->settings?->language) {
            $locale = Auth::user()->settings->language;
        }

        if (session()->has('theme')) {
            $theme = session('theme', 'light');
        } elseif (Auth::check() && Auth::user()->settings?->theme) {
            $theme = Auth::user()->settings->theme;
        }

        // 2. Auto-detect timezone: check browser cookie first (reflects current visitor's city)
        $cookieTz = $request->cookie('user_timezone') ?? ($_COOKIE['user_timezone'] ?? null);
        if ($cookieTz) {
            $cookieTz = urldecode($cookieTz);
            if (in_array($cookieTz, \DateTimeZone::listIdentifiers(), true)) {
                $timezone = $cookieTz;
            }
        }

        // 3. Fallback to authenticated user's setting if no cookie
        if ($timezone === $defaultTz && Auth::check() && !empty(Auth::user()->settings?->timezone)) {
            $userTz = Auth::user()->settings->timezone;
            if (in_array($userTz, \DateTimeZone::listIdentifiers(), true)) {
                $timezone = $userTz;
            }
        }
        // 4. Fallback to session
        elseif ($timezone === $defaultTz && session()->has('timezone')) {
            $sessTz = session('timezone');
            if (in_array($sessTz, \DateTimeZone::listIdentifiers(), true)) {
                $timezone = $sessTz;
            }
        }

        // Final safety check
        if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $timezone = $defaultTz;
        }
        if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $timezone = 'UTC';
        }

        // Keep authenticated user settings in sync with their current detected city
        if (Auth::check() && Auth::user()->settings && Auth::user()->settings->timezone !== $timezone) {
            Auth::user()->settings->update(['timezone' => $timezone]);
        }

        // Apply globally to PHP, Carbon, and Laravel safely
        try {
            date_default_timezone_set($timezone);
        } catch (\Throwable) {
            $timezone = 'UTC';
            date_default_timezone_set('UTC');
        }
        config(['app.timezone' => $timezone]);
        session(['timezone' => $timezone]);

        App::setLocale($locale);

        $dir = $locale === 'ar' ? 'rtl' : 'ltr';

        View::share('currentLocale', $locale);
        View::share('currentTheme', $theme);
        View::share('currentDir', $dir);
        View::share('currentTimezone', $timezone);

        return $next($request);
    }
}
