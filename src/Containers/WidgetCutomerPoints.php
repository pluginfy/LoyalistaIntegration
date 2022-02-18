<?php

namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;


use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;

use LoyalistaIntegration\Core\Api\Services\ApiTokenService;
use LoyalistaIntegration\Helpers\ConfigHelper;

use LoyalistaIntegration\Services\API\LoyalistaApiService;



/**
 * Ekomi Feedback Reviews Container.
 */
class WidgetCutomerPoints
{


    /**
     * Renders HTML content for newly created tab on the product page.
     *
     * @param Twig  $twig
     * @param array $arg
     *
     * @return string
     */
    public function call(Twig $twig, $arg)
    {
        $customer_points = NULL;

        // Get Logged in user
        $authHelper = pluginApp(AuthHelper::class);
        $authUserRepo = pluginApp(ContactRepositoryContract::class);
        $auth_user = null;
        $loggedin_user_id = null;
        $loggedin_user_id = $authHelper->processUnguarded(
            function () use ($authUserRepo, $loggedin_user_id) {
                return $authUserRepo->getContactId();
            }
        );

        if ($loggedin_user_id){
            $api = pluginApp(LoyalistaApiService::class);

            $response  = $api->getCustomerTotalPoints($loggedin_user_id);

            if (isset($response['success']) && $response['success'] == true){
                $customer_points = $response['data']['total_points'];
            }
        }

        $data = array(
            'total_points' => $customer_points,
         );

        return $twig->render('LoyalistaIntegration::content.pointsWidget', $data);
    }
}
