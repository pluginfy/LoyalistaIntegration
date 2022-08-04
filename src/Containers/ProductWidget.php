<?php
namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\LoyalistaHelper;



class ProductWidget
{
    public function call(Twig $twig, $arg)
    {

        $helper = pluginApp(LoyalistaHelper::class);
        $user_account = pluginApp(AccountService::class);
        $loggedin_user_id  = $user_account->getAccountContactId();


        $widget_contents  = $helper->hydrate_product_contents($loggedin_user_id , 'en');

        $data = array( 'customer_id' => $loggedin_user_id  , 'contents' => $widget_contents);


        return $twig->render('LoyalistaIntegration::content.container.WidgetProduct', $data);
    }
}