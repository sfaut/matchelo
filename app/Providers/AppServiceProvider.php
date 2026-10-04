<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->syncNumberLocale();
    }

    /**
     * Aligne la locale du helper Number (formats de nombres, ordinaux) sur celle de
     * l'application, qui change par requête (SetLocaleFromBrowser).
     */
    protected function syncNumberLocale(): void
    {
        Number::useLocale($this->app->getLocale());

        Event::listen(LocaleUpdated::class, function (LocaleUpdated $event): void {
            Number::useLocale($event->locale);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
