<?php

namespace LoyalistaIntegration\Services\API;

use LoyalistaIntegration\Core\Api\BaseApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;
use LoyalistaIntegration\Helpers\OrderHelper;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;
use Plenty\Plugin\Log\Loggable;

use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;
use Plenty\Plugin\Log\Reportable;

use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Item\Item\Contracts\ItemRepositoryContract;


class LoyalistaApiService extends BaseApiService
{
    use Loggable;
    use Reportable;

    public function __construct(ConfigHelper $configHelper, LoggerContract $loggerContract)
    {
        parent::__construct($configHelper, $loggerContract);
    }


    public function verifyApiToken()
    {
        $requestURL = ConfigHelper::BASE_URL .'/v1/validate_user_token';

        $requestType = static::REQUEST_METHOD_POST;

        $vendor_id = $this->configHelper->getVendorID();
        $vendorSecret = $this->configHelper->getVendorSecret();

        $data = [
            'vendor_id' => $vendor_id,
            'access_token' => $vendorSecret
        ];

        $response = $this->doCurl($requestURL ,$requestType , [], $data);

        return $response;
    }

    public function getCustomerPoints($loggedin_customer_id)
    {
        //Todo
    }

    /**
     * @param $customer_id
     * @return mixed|string
     */
    public function getCustomerTotalPoints($customer_id)
    {
        $data = array(
            'customer_reference_id'=> $customer_id,
        );

        $requestURL = ConfigHelper::BASE_URL .'/v1/get_customer_total_points';

        $requestType = static::REQUEST_METHOD_GET;

        $vendorSecret = $this->configHelper->getVendorSecret();

        $headers = array(
            'Authorization: ' . 'Bearer ' .$vendorSecret,
        );

        $response = $this->doCurl($requestURL ,$requestType , $headers, $data);

        return $response;

    }

    /**
     * Add Newly created order to loyalista
     * @param null $order
     *
     */
    public function exportOrder($order = NULL, $type = '')
    {
        $orderHelper = pluginApp(OrderHelper::class);

        try {

            // https://developers.plentymarkets.com/en-gb/developers/main/rest-api-guides/order-data.html

            if ($order)
            {
                // Plenty ID or Intance
                $shop_reference = $order->plentyId;

                // Insert Into OrderSyncedDataTable in any case token verified or not.
                $OrderSyncedRepo = pluginApp(OrderSyncedRepositoryContract::class);
                $OrderSynced = $OrderSyncedRepo->createOrderSync(['orderId' => $order->id]);

                // Get customer/contact
                $customer_id = NULL;
                foreach ($order->relations as $o_relation_item) {
                    if ($o_relation_item->referenceType == "contact"){
                        $customer_id = $o_relation_item->referenceId;
                        break;
                    }
                }

                $contactRepo = pluginApp(ContactRepositoryContract::class);
                $contact = $contactRepo->findContactById($customer_id);

                $customer = array(
                    'OrderSyncedID' => $OrderSynced->id,
                    'reference_id'=> $contact->id,
                    'shop_reference' => $shop_reference,
                    'name' => $contact->fullName,
                    'email' => $contact->email );


                $out['customer'] = $customer;

                $out['type'] = $type;
                $out['parent_reference_id'] = $orderHelper->getOrderReferenceId($order);
                $out['reference_id'] = $order->id;
                $out['shop_reference'] = $shop_reference;
                //$out['platform_id'] = 1;

                $amounts = $order->amounts;

                $amount = $amounts[0];

                $out['shipping_costs_gross'] = $amount->shippingCostsGross;
                $out['shipping_costs_net'] =  $amount->shippingCostsNet;

                $out['currency'] =     $amount->currency;
                $out['exchange_rate'] = $amount->exchangeRate;

                $out['gross_total'] = $amount->netTotal;
                $out['grand_total'] = $amount->invoiceTotal;

                // Tax on Products
                $vats = $amount->vats;
                $vat = $vats[0];

                $out['tax_type'] = 'VAT';
                $out['tax_amount'] = $vat->value;
                $out['tax_percentage'] = $vat->vatRate;


                $out['item_line_total_gross'] = 0.00;
                $out['item_line_total_net'] = 0.00;

                $out['billing_address'] = $orderHelper->getbillingAddress($order) ;
                $out['shipping_address'] = $orderHelper->getShippingAddress($order);
                // Address information
                $out['order_details'] = $orderHelper->getOrderItems($order);


                $coupon = $orderHelper->getCoupon($order);
                $couponCampaignRepo = pluginApp(CouponCampaignRepositoryContract::class);
                $out['coupon_code'] = '';
                $out['coupon_value'] = 0;
                $out['points_redeemed'] = 0;

                if(!empty($coupon)) {
                    $campainCoupon = $couponCampaignRepo->findByCouponCode($coupon['code']);
                    if($campainCoupon && $campainCoupon->name === ConfigHelper::LOYALISTA_CAMPAIGN_NAME) {
                        $out['coupon_code'] = $coupon['code'];
                        $out['coupon_value'] = $coupon['value'];
                        $one_point_to_value = floatval(trim($this->configHelper->getVar('one_point_to_value')));
                        $out['points_redeemed'] = ($coupon['value'] / $one_point_to_value);
                    }
                }

                $requestURL = ConfigHelper::BASE_URL .'/v1/add_order';
                $responses = $this->doCurl($requestURL ,self::REQUEST_METHOD_POST , [], $out);

                $logResponse = ['order_original' => $order , 'out' => json_encode($out), 'api_response' => $responses];
                $logResponse['preSynced'] = $OrderSynced;

                if (is_array($responses) && $responses['success'] == true ){
                    // Update Sync
                    $MarkedOrderSynced = $OrderSyncedRepo->markSyncedOrder($OrderSynced->id);
                    $logResponse['postSynced'] = $MarkedOrderSynced;
                    // Report save case
                    $this->getLogger(__FUNCTION__ . '-' . $type)->error('Export Order Passed successfully', $logResponse);

                }else{
                    $this->getLogger(__FUNCTION__ . '-' . $type)->error('Export Order Failed', $logResponse);
                }
            }else{
                $this->getLogger(__FUNCTION__ . '-' . $type)->error('Order not get');
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger(__FUNCTION__ . '-' . $type)->error('Exception Error while get order', ['message'=> $e->getMessage() ]);

        }
        finally {}
    }

    public function refundOrder($order = NULL) {
        $orderHelper = pluginApp(OrderHelper::class);
        try {

        } catch (\Exception $ex) {

        } finally {

        }

    }
    /**
     * @return void
     */
    public function getCheckoutWidgetData(){

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){

        }

        $requestURL = ConfigHelper::BASE_URL .'/v1/verify_customer';
        $requestType = static::REQUEST_METHOD_POST;
        $vendor_id = $this->configHelper->getVendorID();
        $vendorSecret = $this->configHelper->getVendorSecret();

        $headers = array(
            'Authorization: ' . 'Bearer ' .$vendorSecret,
        );

        $responses = $this->doCurl($requestURL ,$requestType , $headers, []);

    }


    public function getMyAccountWidgetData($loggedin_customer_id){

        $requestURL = ConfigHelper::BASE_URL .'/v1/get_user_account_data';


        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        $response = $this->doCurl($requestURL ,$requestType , [], $data);

        return $response;
    }


    public function getMyMergeAccountWidgetData($loggedin_customer_id){

        $requestURL = ConfigHelper::BASE_URL .'/v1/get_user_merge_account_data';

        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) && $tokenVerified['success']){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);

            return $response;

        }else{
            $this->getLogger('Token Verification Failed')->error('Loyalista Token Expire or invalid');
        }
    }


    public function getCartWidgetData($loggedin_customer_id){


        $requestURL = ConfigHelper::BASE_URL .'/v1/get_user_cart_data';

        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) && $tokenVerified['success']){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);
            return $response;
        }else{
            $this->getLogger('Token Verification Failed')->error('Loyalista Token Expire or invalid');
        }
    }

    /**
     * @param $loggedin_customer_id
     * @return mixed|string|void
     */
    public function getCartCheckoutWidgetData($loggedin_customer_id){
        $requestURL = ConfigHelper::BASE_URL .'/v1/get_user_checkout_cart_data';

        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        return $this->doCurl($requestURL ,$requestType , [], $data);
    }

    /**
     * @param $data
     * @return mixed|string|void
     */
    public function registerCustomer($data)
    {
        $requestURL = ConfigHelper::BASE_URL .'/v1/add_customer';
        $requestType = static::REQUEST_METHOD_POST;
        $response = $this->doCurl($requestURL ,$requestType , [], $data);
        if (!isset($response['success'])){
            $this->getLogger(__FUNCTION__)->error( $response['message']);
        }

        return $response;
    }

    /**
     * @param $data
     * @return mixed|string|void
     */
    public function unRegisterCustomer($data)
    {
        $requestURL = ConfigHelper::BASE_URL .'/v1/remove_customer';
        $requestType = static::REQUEST_METHOD_POST;
        $tokenVerified =  $this->verifyApiToken();
        if (isset($tokenVerified['success']) && $tokenVerified['success']){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);
            return $response;
        }
        else{
            $this->getLogger('Api Token')->error('Unverified Token Used');
        }
    }

    /**
     * @param $data
     * @return mixed|string|void
     */
    public function mergeCustomr($data)
    {
        $requestURL = ConfigHelper::BASE_URL .'/v1/merge_customer_request';
        $requestType = static::REQUEST_METHOD_POST;
        $response = $this->doCurl($requestURL ,$requestType , [], $data);
        if (!isset($response['success'])){
            $this->getLogger(__FUNCTION__)->error( $response['message']);
        }

        return $response;
    }

    /**
     * @param $loggedin_customer_id
     * @param $points
     * @return mixed|string|void
     */
    public function redeemPoints($loggedin_customer_id, $points, $reference_type, $reference_value){
        $requestURL = ConfigHelper::BASE_URL .'/v1/redeem_points';
        $requestType = static::REQUEST_METHOD_POST;
        $shopReference = $this->configHelper->getShopID();
        $data = [
            'shop_reference' => $shopReference,
            'customer_reference_id' => $loggedin_customer_id,
            'points' => $points,
            'reference_type' => $reference_type,
            'reference_value' => $reference_value,
        ];

        $response = $this->doCurl($requestURL ,$requestType , [], $data);
        if (!isset($response['success'])){
            $this->getLogger(__FUNCTION__)->error( $response['message']);
        }

        return $response;
    }

    /**
     * @param $data
     * @return void
     */
    public function logData($data , $code = 'Dump')
    {
        $this->getLogger('log_data_dump')->error($code, ['data'=> $data]);
    }

    public function pullConfiguration(){

        $requestURL = ConfigHelper::BASE_URL .'/v1/get_configurations';
        $requestType = static::REQUEST_METHOD_GET;

        $response = $this->doCurl($requestURL ,$requestType , []);

        if (!isset($response['success'])){
            $this->getLogger(__FUNCTION__)->error( $response['message']);
        }

        return $response;
    }



    public function pushConfiguration()
    {
        $requestURL = ConfigHelper::BASE_URL .'/v1/update_configurations';
        $requestType = static::REQUEST_METHOD_POST;

        $redeemable_after = $this->configHelper->getVar('redeemable_after');
        $expiry_period = $this->configHelper->getVar('expiry_period');

        $data = [
            'expiry_period' => $expiry_period,
            'redeemable_after' => $redeemable_after,
            'revenue_to_point' => $this->configHelper->getVar('revenue_to_one_point'),
            'point_to_value' => $this->configHelper->getVar('one_point_to_value'),

            'events' => [
                'signup' => [
                    'points' => $this->configHelper->getVar('signup_points')

                ],
                'orderCreated' => [
                    'points' => $this->configHelper->getVar('order_created_points')
                ],
                'revenue' => [
                    'points' => $this->configHelper->getVar('revenue_to_one_point')
                ],

                'CategoryExtraPoint' => [
                    'special_ids' => $this->configHelper->getVar('category_ids'),
                    'points' => $this->configHelper->getVar('category_extra_points')
                ],

                'ProductExtraPoint' => [
                    'special_ids' => $this->configHelper->getVar('product_ids'),
                    'points' => $this->configHelper->getVar('product_extra_points')
                ],
            ]
        ] ;

        $response = $this->doCurl($requestURL ,$requestType , [], $data);
        if (!isset($response['success'])){
            $this->getLogger(__FUNCTION__)->error( $data);
            $this->getLogger(__FUNCTION__)->error( $response['message']);
        }

        return $response;
    }

    public function revertUnusedPoints($reference_type){
        $requestURL = ConfigHelper::BASE_URL .'/v1/revert_unused_points';
        $data = [
            'shop_reference' => $this->configHelper->getShopID(),
            'reference_type' => $reference_type,
        ];

        $response = $this->doCurl($requestURL ,static::REQUEST_METHOD_PUT , [], $data);
        if (!isset($response['success']) || !$response['success']){
            $this->getLogger(__FUNCTION__)->error($response);
        }

        $this->getLogger(__FUNCTION__)->error('Api-Response', $response);

        return $response;
    }

}