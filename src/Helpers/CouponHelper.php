<?php

namespace LoyalistaIntegration\Helpers;
use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Models\CouponCampaign;
use Plenty\Modules\Order\Coupon\Campaign\Reference\Contracts\CouponCampaignReferenceRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Reference\Models\CouponCampaignReference;

/**
 * Coupon Class
 */
class CouponHelper
{
    private $couponCampaignRepo;
    private $couponCampaignRefRepo;
    private $authHelper;


    /**
     * @param CouponCampaignRepositoryContract $couponCampaignRepo
     * @param CouponCampaignReferenceRepositoryContract $couponCampaignRefRepo
     * @param AuthHelper $authHelper
     */
    public function __construct(
        CouponCampaignRepositoryContract $couponCampaignRepo,
        CouponCampaignReferenceRepositoryContract $couponCampaignRefRepo,
        AuthHelper $authHelper)
    {
        $this->couponCampaignRepo = $couponCampaignRepo;
        $this->couponCampaignRefRepo = $couponCampaignRefRepo;
        $this->authHelper = $authHelper;
    }

    /**
     * @param $contact
     * @param $value
     * @return mixed
     */
    function createNewCampaign($contact, $value) {
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

        $campaign = $this->authHelper->processUnguarded(
            function () use ($data, $contact, $basedItems) {
                $campaign = $this->couponCampaignRepo->create($data);
                $this->couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_WEBSTORE, 'value' => Utils::getWebstoreId()]);
                $this->couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_GROUP, 'value' => $contact->classId]);
                $this->couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_CUSTOMER_TYPE, 'value' => $contact->typeId]);

                foreach ($basedItems as $basedItem) {
                    $this->couponCampaignRefRepo->create(['campaignId' => $campaign->id,'referenceType' => CouponCampaignReference::REFERENCE_TYPE_ITEM, 'value' => $basedItem->itemId]);
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
    function deleteCampaign($campaignId): mixed
    {
        return $this->authHelper->processUnguarded(
            function () use ($campaignId) {
                return $this->couponCampaignRepo->delete($campaignId);
            }
        );
    }

    /**
     * @param $customerPoint
     * @return mixed
     */
    function deleteCampaignByCoupon($customerPoint): mixed
    {
        return $this->authHelper->processUnguarded(
            function () use ($customerPoint) {
                $couponCampaign =  $this->couponCampaignRepo->findByCouponCode($customerPoint['reference_value']);
                return $this->couponCampaignRepo->delete($couponCampaign->id);
            }
        );
    }
}
