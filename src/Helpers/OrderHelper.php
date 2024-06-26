<?php

namespace LoyalistaIntegration\Helpers;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract;
use Plenty\Modules\Item\Variation\Contracts\VariationRepositoryContract;
use Plenty\Modules\Item\VariationCategory\Contracts\VariationCategoryRepositoryContract;

/**
 * Order Helper Class
 */
class OrderHelper
{

    const ORDER_TYPE_NEW = 'new';
    const ORDER_TYPE_REFUND = 'refund';
    private $authHelper;
    private $addressRepo;
    private $variationCategoryRepo;
    private $variationRepo;

    /**
     * @param AuthHelper $authHelper
     * @param AddressRepositoryContract $addressRepo
     * @param VariationRepositoryContract $variationRepo
     * @param VariationCategoryRepositoryContract $variationCategoryRepo
     */
    public function __construct(AuthHelper $authHelper, AddressRepositoryContract $addressRepo, VariationRepositoryContract $variationRepo ,VariationCategoryRepositoryContract $variationCategoryRepo)
    {
        $this->authHelper = $authHelper;
        $this->addressRepo = $addressRepo;
        $this->variationCategoryRepo = $variationCategoryRepo;
        $this->variationRepo = $variationRepo;
    }

    /**
     * @param $order
     * @return mixed|null
     */
    public function getOrderReferenceId($order) {
        $orderReference = $order->orderReferences;
        if(isset($orderReference[0])) {
            return $orderReference[0]->referenceOrderId;
        }

        return NULL;
    }

    /**
     * @param $order
     * @return array
     */
    public function getBillingAddress($order){
        $billingAddress = [] ;
        $addressRelations = $order->addressRelations;
        foreach ($addressRelations as $address) {
            $temp_address = $this->addressRepo->findAddressById($address->addressId, ['options' , 'country']);
            if ($address->typeId == 1){
                $billingAddress = $this->getAddress($temp_address);
            }
        }

        return $billingAddress;
    }

    /**
     * @param $order
     * @return array
     */
    public function getShippingAddress($order){
        $shippingAddress = [];
        $addressRelations = $order->addressRelations;
        foreach ($addressRelations as $address) {
            $temp_address = $this->addressRepo->findAddressById($address->addressId, ['options' , 'country']);
            if($address->typeId == 2){
                $shippingAddress = $this->getAddress($temp_address);
            }
        }

        return $shippingAddress;
    }

    /**
     * @param $address
     * @return array
     */
    function getAddress($address) {
        $data['company_title'] = $address->name1;
        $data['fname'] = $address->name2;
        $data['lname'] = $address->name3;
        $data['address_line1'] = $address->address1;
        $data['address_line2'] = $address->address2;
        $data['address_line3'] = $address->address3;
        $data['town_city'] = $address->town;
        $data['zip_postal_code'] = $address->postalCode;
        $data['country'] = $address->country->name;
        if (isset($address->options)){
            foreach ($address->options as $option) {
                switch($option->typeId){
                    case 5:
                        $data['email'] = $option->value;
                        break;
                    case 6:
                        $data['phone_number'] = $option->value;
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * @param $order
     * @return array
     */
    public function getCoupon($order){
        $coupon = [];

        try {
            foreach ($order->orderItems as $o_item) {
                if ($o_item->typeId != 4) {
                    continue;
                }

                $coupon['code'] = $o_item->properties[0]->value;
                $coupon['value'] = $o_item->amounts[0]->priceOriginalGross;
            }
        } catch (\Exception $e) {

        }

        return $coupon;
    }

    /**
     * @param $order
     * @return array
     */
    public function getOrderItems($order) {
        $items = [] ;
        foreach ($order->orderItems as $o_item) {
            if($o_item->typeId != 1){
                continue;
            }
            $itemVariationId = $o_item->itemVariationId;

            $variation = $this->variationRepo->findById($itemVariationId);
            $variationCategory = $this->getVariationCategory($variation->categoryVariationId);
            $temp_itm =  array(
                'item_reference_id' => $variation->itemId,
                'variation_reference_id' => $o_item->itemVariationId,
                'item_category_reference_id' => $variationCategory->categoryId,
                'variation_category_reference_id' => $variationCategory->categoryId,
                'item_name' => $o_item->orderItemName,
                'item_description' => '',
                'item_extra_info' => json_encode($variation),
                'item_qty' => $o_item->quantity,
                'item_type' => $o_item->typeId,
            );


            $item_amounts = $o_item->amounts;
            $item_amount = $item_amounts[0];

            $temp_itm['item_gross_price'] = ($item_amount->priceGross);
            $temp_itm['item_net_price'] = ($item_amount->priceNet);
            $temp_itm['item_tax_amount'] = ($item_amount->priceGross - $item_amount->priceNet);

            $temp_itm['tax_type'] = 'VAT';
            $temp_itm['currency'] = $item_amount->currency;
            $temp_itm['exchange_rate'] = $item_amount->exchangeRate;

            $items[] = $temp_itm;
        }

        return $items;
    }

    /**
     * @param $itemVariationId
     * @return mixed
     */
    public function getVariationCategory($itemVariationId) {
        $variationCategory =  $this->authHelper->processUnguarded(
            function () use ($itemVariationId) {
                return $this->variationCategoryRepo->findByVariationId($itemVariationId);
            }
        );

        return $variationCategory[0];
    }
}
