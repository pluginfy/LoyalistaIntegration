<?php
/**
 * Created by PhpStorm.
 * User: Toheed
 * Date: 5/30/2022
 * Time: 2:26 PM
 */

namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Log\Loggable;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

class BasketController extends Controller
{

    use Loggable;

    public function getBasketValue(){

        // Only Ajax Request

        $basket_repo = pluginApp(BasketRepositoryContract::class);

        $customer_cart = $basket_repo->load();

        $basket_total = $customer_cart->basketAmount;

        $return = ['status' => 'OK', 'basket_total' => $basket_total, 'customer_cart' => $customer_cart ];

        return json_encode($return);
    }



    public function getBasket()
    {

        // Only Ajax Request
        $basket_repo = pluginApp(BasketRepositoryContract::class);
        $customer_cart = $basket_repo->load();
        $return = ['status' => 'OK', 'customer_cart' => $customer_cart];

        return json_encode($return);

    }



}