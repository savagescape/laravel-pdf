<?php

namespace Savagescape\Pdf;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            Factory::class,
        ];
    }

    public function register()
    {
        $this->app->when(Client::class)
            ->needs('$url')
            ->giveConfig('services.gotenberg.url');
    }
}
