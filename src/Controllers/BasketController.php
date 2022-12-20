<?php
/**
 * Created by PhpStorm.
 * User: Toheed
 * Date: 5/30/2022
 * Time: 2:26 PM
 */

namespace LoyalistaIntegration\Controllers;

use LoyalistaIntegration\Helpers\ConfigHelper;
use LoyalistaIntegration\Helpers\OrderHelper;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Log\Loggable;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

class BasketController extends Controller
{

    use Loggable;

    public function getBasketValue(){

        // Only Ajax Request

        $basket_repo = pluginApp(BasketRepositoryContract::class);
        $basket_items_repo = pluginApp(BasketItemRepositoryContract::class);

        $customer_cart = $basket_repo->load();

        $basket_total = $customer_cart->basketAmount;

        $return = [
            'status' => 'OK',
            'basket_total' => $basket_total,
            'customer_cart' => $customer_cart
        ];

        $return = $this->loadExtraPoints($return, $basket_items_repo->all());

        return json_encode($return);
    }

    private function loadExtraPoints($return, $basketItems) {
        $config_helper = pluginApp(ConfigHelper::class);
        $orderHelper = pluginApp(OrderHelper::class);
        $extraCatPoints = $config_helper->getVar('category_extra_points');
        $extraItemPoints = $config_helper->getVar('product_extra_points');
        $specialCatIds = explode(',', $config_helper->getVar('category_ids'));
        $specialItemIds = explode(',', $config_helper->getVar('product_ids'));

        $earnedCatPts = 0;
        $earnedItemPts = 0;

        foreach ($basketItems as $basketItem) {
            $variationCategory = $orderHelper->getVariationCategory($basketItem->variationId);
            if(in_array($variationCategory->categoryId, $specialCatIds)) {
                $earnedCatPts += ($extraCatPoints * $basketItem->quantity);
            }

            if(in_array($basketItem->variationId, $specialItemIds)) {
                $earnedItemPts += ($extraItemPoints * $basketItem->quantity);
            }
        }

        $return['order_created_points'] = round($config_helper->getVar('order_created_points'));
        $return['cat_extra_points'] = round($earnedCatPts);
        $return['item_extra_points'] = round($earnedItemPts);

        $return['total_cart_points'] = round($return['basket_total'] / $config_helper->getVar('revenue_to_one_point'));
        $return['total_cart_points'] += $return['order_created_points'];
        $return['total_cart_points'] += $return['cat_extra_points'];
        $return['total_cart_points'] += $return['item_extra_points'] ;

        return $return;
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