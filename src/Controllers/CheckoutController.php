<?php
namespace LoyalistaIntegration\Controllers;

use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Models\CouponCampaign;
use Plenty\Modules\Order\Coupon\Campaign\Reference\Contracts\CouponCampaignReferenceRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Reference\Models\CouponCampaignReference;
use Plenty\Plugin\Controller;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Frontend\Services\AccountService;

use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\Loggable;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;


class CheckoutController extends Controller
{
    use Loggable;

    public function createCoupon(Request $request)
    {
        $basketRepo = pluginApp(BasketRepositoryContract::class);
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

        if($point_to_redeem <= 0 || $point_to_redeem >  $max_points){
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
        $campaign = $this->createNewCampaign($contact, $couponValue);
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
            $this->deleteCampaign($campaign->id);
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

    function createNewCampaign($contact, $value) {
        $authHelper = pluginApp(AuthHelper::class);
        $couponCampaignRepo = pluginApp(CouponCampaignRepositoryContract::class);
        $couponCampaignRefRepo = pluginApp(CouponCampaignReferenceRepositoryContract::class);

        $data = [
            'name' => ConfigHelper::LOYALISTA_CAMPAIGN_NAME,
            'minOrderValue' => 0.00,
            'variable' => 0,
            'codeLength' => CouponCampaign::CODE_LENGTH_MEDIUM,
            'codeDurationWeeks' => 1,
            'includeShipping' => TRUE,
            'usage' => CouponCampaign::CAMPAIGN_USAGE_SINGLE,
            'concept' => CouponCampaign::CAMPAIGN_CONCEPT_SINGLE_CODE,
            'redeemType' => CouponCampaign::CAMPAIGN_REDEEM_TYPE_UNIQUE,
            'discountType' => CouponCampaign::DISCOUNT_TYPE_FIXED,
            'campaignType' => CouponCampaign::CAMPAIGN_TYPE_COUPON,  // coupon
            'couponType' => CouponCampaign::COUPON_TYPE_SALES,
            'description' => 'Coupon Campaign created by Loyalista to redeem the customer points',
            'value' => (float) $value,
            'codeAssignment' => CouponCampaign::CODE_ASSIGNMENT_GENERATE,
            'isPermittedForExternalReferrers' => TRUE,
            'startsAt' => date('c'),
            'endsAt' => date('c', strtotime("+5 min")),
        ];

        $basketItemRepo = pluginApp(BasketItemRepositoryContract::class);
        $basedItems = $basketItemRepo->all();

        $campaign = $authHelper->processUnguarded(
            function () use ($couponCampaignRepo, $couponCampaignRefRepo,$data, $contact, $basedItems) {
                $campaign = $couponCampaignRepo->create($data);
                $couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_WEBSTORE, 'value' => Utils::getWebstoreId()]);
                $couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_GROUP, 'value' => $contact->classId]);
                $couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_TYPE, 'value' => $contact->typeId]);

                foreach ($basedItems as $basedItem) {
                    $couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_ITEM, 'value' => $basedItem->itemId]);
                }

                return $campaign;
            }
        );

        return $campaign;
    }

    /**
     * @param $campaignId
     * @return mixed
     */
    function deleteCampaign($campaignId) {
        $authHelper = pluginApp(AuthHelper::class);
        $couponCampaignRepo = pluginApp(CouponCampaignRepositoryContract::class);
        return $authHelper->processUnguarded(
            function () use ($couponCampaignRepo, $campaignId) {
                return $couponCampaignRepo->delete($campaignId);
            }
        );
    }

}