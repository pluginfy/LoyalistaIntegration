<?php

namespace LoyalistaIntegration\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

/**
 * Class LoyalistaIntegrationRouteServiceProvider
 * @package LoyalistaIntegration\Providers
 */
class LoyalistaIntegrationRouteServiceProvider extends RouteServiceProvider
{
    /**
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('hello-world','LoyalistaIntegration\Controllers\LoyalistaIntegrationController@getHelloWorldPage');
    }
}