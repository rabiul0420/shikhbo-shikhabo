<?php

namespace App\Providers;

use App\Routing\LocaleUrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');

        $this->app->extend('url', function ($url, $app) {
            $routes = $app['router']->getRoutes();

            $localeUrl = new LocaleUrlGenerator(
                $routes,
                $app->rebinding('request', function ($app, $request) use (&$localeUrl) {
                    $localeUrl->setRequest($request);
                }),
                $app['config']['app.asset_url']
            );

            $localeUrl->setSessionResolver(fn () => $app['session'] ?? null);
            $localeUrl->setKeyResolver(fn () => $app->make('config')->get('app.key'));
            $localeUrl->setRootControllerNamespace(
                $app->make('config')->get('app.root_namespace', 'App\\Http\\Controllers')
            );

            return $localeUrl;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
