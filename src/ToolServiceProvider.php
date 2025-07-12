<?php

namespace BenColmer\NovaResourceHierarchy;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use BenColmer\NovaResourceHierarchy\Http\Middleware\Authorize;
use Illuminate\Support\Facades\App;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->booted(function () {
            $this->routes();
        });

        $this->publishes([
            __DIR__.'/../lang/' => base_path('lang/vendor/nova-resource-hierarchy/'),
        ]);

        Nova::serving(function (ServingNova $event) {
            $this->translations();
        });
    }

    /**
     * Register the tool's routes.
     */
    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', 'nova.auth', Authorize::class], 'nova-resource-hierarchy')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', 'nova.auth', Authorize::class])
            ->prefix('nova-vendor/nova-resource-hierarchy')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register the tool's translations.
     */
    protected function translations(): void
    {
        // load the translations for the current locale if they exist
        $locale = (string) App::getLocale();
        if ($this->loadTranslationsForLocale($locale)) return;

        // otherwise check the fallback locale
        $fallbackLocale = (string) config('app.fallback_locale');
        if ($this->loadTranslationsForLocale($fallbackLocale)) return;

        // default to EN translations
        $this->loadTranslationsForLocale('en');
    }

    /**
     * Load the translations for a locale.
     */
    protected function loadTranslationsForLocale(string $locale): bool
    {
        $locale = strtolower(trim($locale));
        if (! $locale) return false;

        // load the default translation file first, then any published translations for the locale
        $basePaths = [
            __DIR__.'/../lang/',
            base_path('lang/vendor/nova-resource-hierarchy/'),
        ];

        $hasLoadedTranslations = false;
        foreach ($basePaths as $basePath) {
            // skip if the file does not exist
            $filePath = $basePath.$locale.'.json';
            if (! file_exists($filePath)) continue;

            Nova::translations($filePath);
            $hasLoadedTranslations = true;
        }

        return $hasLoadedTranslations;
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
