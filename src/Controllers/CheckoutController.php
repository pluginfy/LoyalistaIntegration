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
use Plenty\Validation\Validator;
use Plenty\Plugin\Log\Loggable;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;



class CheckoutController extends Controller
{
    use Loggable;

    public function redeemPoints(Request $request)
    {
        $basketRepo = pluginApp(BasketRepositoryContract::class);
        $customerBasket = $basketRepo->load();
        if (!empty($customerBasket->couponCode)) {
            $response = ['status' => 'OK', 'message' => 'Coupon already applied!', 'coupon' => $customerBasket->couponCode];

            return json_encode($response);
        }

        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        //step1: check for the balance through API and redeem.
        /**
         * Do your API call here...
         */
        // Register in loyalista
        $api = pluginApp(LoyalistaApiService::class);

        $response = $api->redeemPoints($plenty_customer_id, $request->get('pointsToRedeem'));
        $this->getLogger(__FUNCTION__)->error('redeemPoints', $response);

        //step 2: create campaign.
        $campaign = $this->createNewCampaign($contact, $request->all());
        $this->getLogger(__FUNCTION__)->error('LoyalistaCampaign', ['campaign'=> $campaign, 'campaign_codes'=> $campaign->codes, 'campaign_references'=> $campaign->references]);
        if($campaign) {
            $basketRepo->setCouponCode($campaign->codes[0]->code);
            $response = [
                'status' => 'OK',
                'couponCampaign' => [
                    'id' => $campaign->id, 'name' => $campaign->name, 'value' => $campaign->value, 'usedCodesCount' => $campaign->usedCodesCount,
                ]
            ];
        } else {
            $response = ['status' => 'ERROR', 'message' => 'No coupon campaign is created!'];
        }

        return json_encode($response);
    }

    function createNewCampaign($contact, $postData) {
        $authHelper = pluginApp(AuthHelper::class);
        $couponCampaignRepo = pluginApp(CouponCampaignRepositoryContract::class);
        $couponCampaignRefRepo = pluginApp(CouponCampaignReferenceRepositoryContract::class);

        $data = [
            'name' => 'Loyalista Coupon Campaign',
            'minOrderValue' => 0.00,
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
            'value' => (float) ($postData['pointsToRedeem'] * $postData['pointToValue']),
            'codeAssignment' => CouponCampaign::CODE_ASSIGNMENT_GENERATE,
            'isPermittedForExternalReferrers' => TRUE,
            'startsAt' => date('c'),
            'endsAt' => date('c', strtotime("+5 min")),
//            'codes' => ['LOYALISTA' . strtoupper(substr(md5(time()), 0, 7))]
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

}