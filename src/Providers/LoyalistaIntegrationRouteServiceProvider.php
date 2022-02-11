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
        $router->post('/todo', 'LoyalistaIntegration\Controllers\ContentController@createToDo');
        $router->get('/todo', 'LoyalistaIntegration\Controllers\ContentController@showToDo');
        $router->put('/todo/{id}', 'LoyalistaIntegration\Controllers\ContentController@updateToDo')->where('id', '\d+');
        $router->delete('/todo/{id}', 'LoyalistaIntegration\Controllers\ContentController@deleteToDo')->where('id', '\d+');
    }
}