<?php

namespace App\Support\Modules;

use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Events\LaravelDomainEventDispatcher;
use App\Support\Modules\Commands\MakeModuleCommand;
use App\Integration\Support\IntegrationRegistry;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DomainEventDispatcher::class, LaravelDomainEventDispatcher::class);
        $this->app->singleton(IntegrationRegistry::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
            ]);
        }

        foreach (ModuleRegistry::serviceProviders() as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot(): void
    {
        foreach (ModuleRegistry::eventListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        foreach (ModuleRegistry::routeFiles() as $routeFile) {
            Route::middleware('web')->group($routeFile);
        }
    }
}
