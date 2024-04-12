<?php
namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;

use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;

/**
 * Class ContentController
 *
 */
class ContentController extends Controller
{

    /**
     * @param Twig $twig
     * @return string
     */
    public function showSyncOrder(Request $request, Twig $twig, OrderSyncedRepositoryContract $osr): string
    {
        $orderSyncList = $osr->getOrderSyncedList();

       return json_encode($orderSyncList);
    }
}