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
        $packagistResult =
            $libCall->call(
                'LoyalistaIntegration::guzzle_connector',
                ['packagist_query' => $request->get('search')]
            );

        $curl_response = $libCall->call(             'LoyalistaIntegration::curl_test', []);

        $api = pluginApp(LoyalistaApiService::class);
     ;

        //$curl_response1 = $api->verifyUserToken();

        $curl_response1 = $api->createOrder();
        $curl_response2 = $api->verifyUserToken();

        $data = array(
            'users' => 1,
            'a_user' => $curl_response2,
            'packagistResult' => $packagistResult,
            'curl_response' => $curl_response1

        );

        return $twig->render('LoyalistaIntegration::content.hello' , $data);
    }


}

