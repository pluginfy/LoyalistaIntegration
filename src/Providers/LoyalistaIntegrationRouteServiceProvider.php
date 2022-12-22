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
        $router->get('/account/register/customer/', 'LoyalistaIntegration\Controllers\CustomerController@registerCustomer');
        $router->get('/account/unregister/customer/', 'LoyalistaIntegration\Controllers\CustomerController@unRegisterCustomer');
        $router->post('/account/merge/customer/', 'LoyalistaIntegration\Controllers\CustomerController@mergeCustomer');
        $router->get('/user/total/basket/', 'LoyalistaIntegration\Controllers\BasketController@getBasketValue');
        $router->post('/checkout/redeem/points/', 'LoyalistaIntegration\Controllers\CheckoutController@createCoupon');
        $router->get('/my-loyalista-account/', 'LoyalistaIntegration\Controllers\CustomerController@loayslistaAccountPage');

        $router->get('/getCampaign/', 'LoyalistaIntegration\Controllers\LoyalistaIntegrationController@getCampaign');
        $router->get('/pushConfiguration/', 'LoyalistaIntegration\Controllers\LoyalistaIntegrationController@pushConfiguration');
        $router->get('/revertUnusedPoints/', 'LoyalistaIntegration\Controllers\CheckoutController@revertUnusedPoints');
        $router->get('/exportOrders/', 'LoyalistaIntegration\Controllers\LoyalistaIntegrationController@exportOrders');
        $router->get('/showSyncOrder/', 'LoyalistaIntegration\Controllers\ContentController@showSyncOrder');
    }
}