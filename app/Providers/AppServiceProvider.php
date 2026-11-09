<?php

namespace App\Providers;

use App\Services\GenericRawStore;
use App\Services\Interfaces\ProviderRawStoreInterface;
use App\Services\ProviderRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void

    {
        $this->app->bind('IndeedStore', fn() => new GenericRawStore('indeed'));
        $this->app->bind('AirworkStore', fn() => new GenericRawStore('airwork'));

        $this->app->tag(
            ['IndeedStore', 'AirworkStore'],
            ProviderRawStoreInterface::class,
        );

        $this->app->singleton(ProviderRegistry::class, fn($app): ProviderRegistry => new ProviderRegistry(
            $app->tagged(ProviderRawStoreInterface::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
