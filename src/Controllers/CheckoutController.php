<?php
namespace LoyalistaIntegration\Controllers;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Plugin\Controller;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Frontend\Services\AccountService;

use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;
use LoyalistaIntegration\Helpers\CouponHelper;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\Loggable;

class CheckoutController extends Controller
{
    use Loggable;

    public function createCoupon(Request $request)
    {
        $basketRepo = pluginApp(BasketRepositoryContract::class);
        $couponHelper = pluginApp(CouponHelper::class);
        $customerBasket = $basketRepo->load();
        if (!empty($customerBasket->couponCode)) {
            $response = ['status' => 'OK', 'message' => 'Coupon already applied!', 'coupon' => $customerBasket->couponCode];
            return json_encode($response);
        }


        $configHelper = pluginApp(ConfigHelper::class);

        // validate max_redeem
        $basket_total = $customerBasket->basketAmount;
        $one_point_to_value = floatval(trim($configHelper->getVar('one_point_to_value')));
        $revenue_to_one_point = floatval(trim($configHelper->getVar('revenue_to_one_point')));
        $max_points =  floatval($basket_total /  $one_point_to_value);
        $point_to_redeem = floatval($request->get('pointsToRedeem'));

        if( $point_to_redeem <= 0 || $point_to_redeem >  $max_points ){
            $data = [
                'bt' => $basket_total
                , 'mp' => $max_points
                , 'ptr' => $point_to_redeem
                , 'ptr-minus-max' => ($point_to_redeem - $max_points)
                , 'max-minus-ptr' => ($max_points - $point_to_redeem )
                , 'eval' => ($point_to_redeem >  $max_points)
                , 'eval1' => ($point_to_redeem ==  $max_points)
                , 'eval2' => ($point_to_redeem <  $max_points)
            ];

            return ['status' => 'ERROR', 'message' => 'Validation Error' , 'data' => $data ];
        }

        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        $couponValue = floatval($point_to_redeem * $one_point_to_value);
        $campaign = $couponHelper->createNewCampaign($contact, $couponValue);
        $this->getLogger(__FUNCTION__)->error('LoyalistaCampaign', ['campaign'=> $campaign, 'campaign_codes'=> $campaign->codes, 'campaign_references'=> $campaign->references]);

        if(!$campaign) {
            return ['status' => 'ERROR', 'message' => 'No coupon campaign is created!'];
        }

        /**
         * Do your API call here...
         */
        // Register in loyalista
        $api = pluginApp(LoyalistaApiService::class);
        $response = $api->redeemPoints($plenty_customer_id, $point_to_redeem, 'coupon', $campaign->codes[0]->code);
        if(!isset($response['success']) || !$response['success']) {
            $couponHelper->deleteCampaign($campaign->id);
            return ['status' => 'ERROR', 'message' => $response['message']];
        }

        $basketRepo->setCouponCode($campaign->codes[0]->code);
        $response = [
            'status' => 'OK',
            'couponCampaign' => [
                'id' => $campaign->id, 'name' => $campaign->name, 'value' => $campaign->value, 'usedCodesCount' => $campaign->usedCodesCount,
            ]
        ];

        return json_encode($response);
    }

    public function revertUnusedPoints()
    {
        $api = pluginApp(LoyalistaApiService::class);
        $response = $api->revertUnusedPoints('coupon');

        $couponHelper = pluginApp(CouponHelper::class);
        if($response['success'] && !empty($response['data'])) {
            foreach ($response['data'] as $customerPoint) {
                $couponHelper->deleteCampaignByCoupon($customerPoint);
            }
        }
    }

}