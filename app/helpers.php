<?php

if (! function_exists('localized_route')) {
    /**
     * Generate a named route URL for a given locale.
     * English (default) has no prefix; Bangla uses /bn.
     */
    function localized_route(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $baseName = str_starts_with($name, 'bn.') ? substr($name, 3) : $name;
        $previous = app()->getLocale();

        app()->setLocale($locale);

        try {
            return route($baseName, $parameters);
        } finally {
            app()->setLocale($previous);
        }
    }
}

if (! function_exists('alternate_locale_url')) {
    /**
     * URL of the current page in another locale (preserves query string).
     */
    function alternate_locale_url(string $locale): string
    {
        $route = request()->route();
        $name = $route?->getName();

        if (! $name || str_starts_with($name, 'admin.')) {
            return $locale === 'bn' ? url('/bn') : url('/');
        }

        $baseName = str_starts_with($name, 'bn.') ? substr($name, 3) : $name;
        $parameters = $route->parameters();
        $url = localized_route($baseName, $parameters, $locale);

        if ($query = request()->getQueryString()) {
            $url .= (str_contains($url, '?') ? '&' : '?').$query;
        }

        return $url;
    }
}

if (! function_exists('locale_route_is')) {
    /**
     * Like request()->routeIs(), but matches both English and /bn route names.
     */
    function locale_route_is(string ...$patterns): bool
    {
        $expanded = [];

        foreach ($patterns as $pattern) {
            $expanded[] = $pattern;

            if (! str_starts_with($pattern, 'bn.') && ! str_starts_with($pattern, 'admin.')) {
                $expanded[] = 'bn.'.$pattern;
            }
        }

        return request()->routeIs(...$expanded);
    }
}
