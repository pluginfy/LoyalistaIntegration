<?php


namespace LoyalistaIntegration\Widgets;

use Ceres\Widgets\Helper\BaseWidget;

use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;

class MyWidget extends BaseWidget
{
    protected $template = "LoyalistaIntegration::Widgets.MyWidget";

    protected function getTemplateData($widgetSettings, $isPreview)
    {
        $api = pluginApp(LoyalistaApiService::class);
        $configHelper = pluginApp(ConfigHelper::class);

       return [
            "widgetData" => [
                'heading' => "config helper setting data",
                'vendor_id' => $configHelper->getVendorID(),
                'vendor_hash' => $configHelper->getVendorHash(),
                'vendor_secret' => $configHelper->getVendorSecret(),
                'shop_id' => $configHelper->getShopID(),
            ],
        ];
    }
}