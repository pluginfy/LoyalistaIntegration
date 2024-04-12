<?php

namespace LoyalistaIntegration\Controllers;

use LoyalistaIntegration\Helpers\ConfigHelper;
use LoyalistaIntegration\Helpers\OrderHelper;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Plugin\Controller;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

/**
 * Basket Controller class
 */
class BasketController extends Controller
{
    /**
     * @return false|string
     */
    public function getBasketValue(){
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

    /**
     * @param $return
     * @param $basketItems
     * @return mixed
     */
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
        $return['total_cart_points'] += $return['item_extra_points'];

        $return['total_cart_points'] = number_format($return['total_cart_points'], 0, ',', '.');

        return $return;
    }


    /**
     * @return false|string
     */
    public function getBasket()
    {
        $basket_repo = pluginApp(BasketRepositoryContract::class);
        $customer_cart = $basket_repo->load();
        $return = ['status' => 'OK', 'customer_cart' => $customer_cart];

        return json_encode($return);
    }
}