<?php

namespace Drafolin\FilamentInverseShield;

use Illuminate\Support\ServiceProvider;

class FilamentInverseShieldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([Console\InverseShield::class]);
        }
    }
}
