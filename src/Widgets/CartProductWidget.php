<?php

namespace LoyalistaIntegration\Widgets;

use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Plugin\Application;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Log\Loggable;
use Ceres\Widgets\Helper\BaseWidget;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Contexts\MyContext;

class CartProductWidget extends BaseWidget{

    use Loggable;

    protected $template = "LoyalistaIntegration::Widgets.CartProductWidget";
    private $login_id ;
    private $sess;

    public function __construct(Twig $twig, Application $app , AccountService $accountService, LoyalistaApiService $api )
    {
        parent::__construct($twig, $app);
    }

    protected function getTemplateData($widgetSettings, $isPreview)
    {

        $con = pluginApp(MyContext::class);

        return [
            "widgetData" => [
                'heading' => $con->myText(),
                'customer_id' => $this->login_id ,
            ],
        ];
    }
}