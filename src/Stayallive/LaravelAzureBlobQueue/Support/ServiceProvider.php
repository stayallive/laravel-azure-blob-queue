<?php

namespace Stayallive\LaravelAzureBlobQueue\Support;

use Stayallive\LaravelAzureBlobQueue\AzureConnector;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booted(function () {
            $this->app->queue->extend('azure.blob', function () {
                return new AzureConnector;
            });
        });
    }
}
