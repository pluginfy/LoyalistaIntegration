<?php

namespace LoyalistaIntegration\Controllers;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Coupon\Campaign\Code\Contracts\CouponCampaignCodeRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Models\CouponCampaign;
use Plenty\Modules\Order\Coupon\Campaign\Reference\Contracts\CouponCampaignReferenceRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Reference\Models\CouponCampaignReference;
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

    function getCampaigns(Request $request ) {
        if($request->get('campaign_type') == 'create') {
            return $this->createNewCampaign();
        } else {
            $authHelper = pluginApp(AuthHelper::class);
            $couponCampRepo = pluginApp(CouponCampaignRepositoryContract::class);
            $campaignId = (int)$request->get('campaign_id');

            $campaign = $authHelper->processUnguarded(
                function () use ($couponCampRepo, $campaignId) {
                    return $couponCampRepo->findById($campaignId);
                }
            );

            $this->getLogger('Coupon')->error('Campaign', ['campaign-' . $campaignId => $campaign]);

            return json_encode($campaign->toArray());
        }
    }

    function createNewCampaign() {
        $authHelper = pluginApp(AuthHelper::class);
        $couponCampRepo = pluginApp(CouponCampaignRepositoryContract::class);
        $couponCampRefRepo = pluginApp(CouponCampaignReferenceRepositoryContract::class);
        $data = [
            'name' => 'pluginfy-test-camp',
            'minOrderValue' => 0.00,
            'codeLength' => CouponCampaign::CODE_LENGTH_SMALL,
            'codeDurationWeeks' => 1,
            'usage' => CouponCampaign::CAMPAIGN_USAGE_SINGLE,
            'concept' => CouponCampaign::CAMPAIGN_CONCEPT_SINGLE_CODE,
            'redeemType' => CouponCampaign::CAMPAIGN_REDEEM_TYPE_UNIQUE,
            'discountType' => CouponCampaign::DISCOUNT_TYPE_FIXED,
            'campaignType' => CouponCampaign::CAMPAIGN_TYPE_COUPON,  // coupon
            'couponType' => CouponCampaign::COUPON_TYPE_SALES,
            'description' => 'pluginfy-test-camp description description description',
            'value' => 10,
            'codeAssignment' => CouponCampaign::CODE_ASSIGNMENT_GENERATE,
            'isPermittedForExternalReferrers' => true,
//            'references' => ['referenceType' => CouponCampaignReference::REFERENCE_TYPE_ITEM , 'value' => 109],
        ];

        $campaign = $authHelper->processUnguarded(
            function () use ($couponCampRepo, $couponCampRefRepo,$data) {
                $cam = $couponCampRepo->create($data);
                $couponCampRefRepo->create(['campaignId' => $cam->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_WEBSTORE, 'value' => 0]);
                $couponCampRefRepo->create(['campaignId' => $cam->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_ITEM, 'value' => 109]);
                $couponCampRefRepo->create(['campaignId' => $cam->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_WEBSTORE, 'value' => 1]);
                $couponCampRefRepo->create(['campaignId' => $cam->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_GROUP, 'value' => 1]);
                $couponCampRefRepo->create(['campaignId' => $cam->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_TYPE, 'value' => 1]);
//                $refData['references'] = [
//                    ['campaignId' =>35, 'referenceType' => CouponCampaignReference::REFERENCE_TYPE_ITEM , 'value' => 109],
//                    ['campaignId' =>35, 'referenceType' => CouponCampaignReference::REFERENCE_TYPE_WEBSTORE , 'value' => 1],
//                    ['campaignId' =>35, 'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_GROUP , 'value' => 1],
//                    ['campaignId' =>35, 'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_TYPE, 'value' => 1],
//                ];
//                $cam = $couponCampRepo->update($refData);

                return $cam;
            }
        );
        $this->getLogger('Campaign')->error('Campaign', ['campaign'=> $campaign]);

        return json_encode($campaign);
    }


}

