<?php
namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\LoyalistaHelper;

class ProductWidget
{
    use Loggable;

    public function call(Twig $twig, $arg)
    {
        $this->getLogger('ProductWidget')->error('item', $arg);

        $helper = pluginApp(LoyalistaHelper::class);
        $user_account = pluginApp(AccountService::class);
        $loggedin_user_id  = $user_account->getAccountContactId();

        $api = pluginApp(LoyalistaApiService::class);
        $response =   $api->getMyAccountWidgetData($loggedin_user_id);

        $isRegistered = false;
        if (isset($response['success']) && $response['success'] && $response['data']['user_registered']){
            $isRegistered = true;
        }

        $widget_contents  = $helper->hydrate_product_contents(
            $isRegistered,
            $this->getItemId($arg),
            $this->getItemPrice($arg),
        );

        $data = [
            'customer_id' => $loggedin_user_id ,
            'contents' => $widget_contents,
            'widget_heading' => $helper->getWidgetHeading('product_page_widget_heading_text_'),
            'widget_border_width' => $helper->getWidgetBorderWidth(),
            'widget_border_color' => $helper->getWidgetBorderColor(),
        ];

        return $twig->render('LoyalistaIntegration::content.container.ProductWidget', $data);
    }

    private function getItemId($arg, $identifier = 'id')
    {
        if(isset($arg[0])) {
            $item = $arg[0];
            if ($identifier == 'number') {
                $productIdentifier = trim($item['variation']['number']);
            } else{
                $productIdentifier = trim($item['variation']['id']);
            }

            return $productIdentifier;
        }

        return false;
    }

    private function getItemPrice($arg)
    {
        if(isset($arg[0])) {
            $prices = $arg[0]['prices'];
            return $prices['graduatedPrices'][0]['baseSinglePrice'] * $prices['graduatedPrices'][0]['minimumOrderQuantity'];
        }

        return 0;
    }
}