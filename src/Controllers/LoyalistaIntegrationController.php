<?php

namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\Http\Request;

use LoyalistaIntegration\Services\API\LoyalistaApiService;



class LoyalistaIntegrationController extends Controller
{
    /**
     * @param Twig $twig
     * @return string
     */
    public function getHelloWorldPage(Twig $twig, LibraryCallContract $libCall, Request $request  )
    {
        $packagistResult = [];

        $api = pluginApp(LoyalistaApiService::class);

        $curl_response1 = $api->createOrder();
        $curl_response2 = $api->verifyApiToken();

        $data = array(
            'users' => 1,
            'a_user' => 0,
            'packagistResult' => [],
            'curl_response' => ''

        );

        return $twig->render('LoyalistaIntegration::content.hello' , $data);
    }


}

