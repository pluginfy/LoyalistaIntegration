<?php

namespace LoyalistaIntegration\Providers;

use Plenty\Plugin\ServiceProvider;

/**
 * Class LoyalistaIntegrationServiceProvider
 * @package LoyalistaIntegration\Providers
 */
class LoyalistaIntegrationServiceProvider extends ServiceProvider
{
    /**
    * Register the route service provider
    */
    public function register()
    {
        $this->getApplication()->register(LoyalistaIntegrationRouteServiceProvider::class);
    }
}