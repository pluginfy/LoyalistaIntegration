<?php

namespace LoyalistaIntegration\Controllers;

use LoyalistaIntegration\Services\ExportServices;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\Http\Request;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

class LoyalistaIntegrationController extends Controller
{
    use Loggable;

    /**
     * @param Twig $twig
     * @return string
     */
    public function getHelloWorldPage(Twig $twig, LibraryCallContract $libCall, Request $request  )
    {
        $packagistResult = [];

        $api = pluginApp(LoyalistaApiService::class);
		$test = null;

        $curl_response1 = $api->exportOrder();
        $curl_response2 = $api->verifyApiToken();

        $data = array(
            'users' => 1,
            'a_user' => 0,
            'packagistResult' => [],
            'curl_response' => ''

        );

        return $twig->render('LoyalistaIntegration::content.hello' , $data);
    }

    function getCampaign(Request $request) {
        $authHelper = pluginApp(AuthHelper::class);
        $couponCampRepo = pluginApp(CouponCampaignRepositoryContract::class);
        $campaignId = $request->get('campaign_id');
        $campaign = $authHelper->processUnguarded(
            function () use ($couponCampRepo, $campaignId) {
                return $couponCampRepo->findById($campaignId);
            }
        );

        return json_encode($campaign->toArray());
    }


    function pushConfiguration(Request $request)
    {
        $authHelper = pluginApp(AuthHelper::class);
        $data = $authHelper->processUnguarded(
            function () use ($request) {
                $api = pluginApp(LoyalistaApiService::class);
                $response = $api->pushConfiguration();

                return $response;
            }
        );

        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    function exportOrders(Request $request)
    {
        $exportService = pluginApp(ExportServices::class);
        $response = $exportService->exportPreviousOrders();

        $this->getLogger('LoyalistaIntegrationController')->error(__FUNCTION__, $response);

        echo json_encode($response);
    }

}

