<?php
namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\LoyalistaHelper;
use LoyalistaIntegration\Helpers\ConfigHelper;

class MyAccountWidget
{
    public function call(Twig $twig, $arg)
    {
        $helper = pluginApp(LoyalistaHelper::class);
        $data = $helper->hydrate_my_account_data();

        return $twig->render('LoyalistaIntegration::content.container.MyAccountWidget', $data);
    }
}