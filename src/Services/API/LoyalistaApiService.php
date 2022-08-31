<?php

namespace LoyalistaIntegration\Services\API;

use LoyalistaIntegration\Core\Api\BaseApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Plugin\Log\Loggable;

use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;
use Plenty\Plugin\Log\Reportable;

use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;


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
        $requestURL = ConfigHelper::BASE_URL .'/api/validate_user_token';

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

    public function getCustomerTotalPoints($customer_id)
    {
        $data = array(
            'customer_reference_id'=> $customer_id,
        );

        $requestURL = ConfigHelper::BASE_URL .'/api/get_customer_total_points';

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
    public function createOrder($order = NULL)
    {
        try {

            // https://developers.plentymarkets.com/en-gb/developers/main/rest-api-guides/order-data.html
            $tokenVerified =  $this->verifyApiToken();

            if ($order)
            {
                // Plenty ID or Intance
                $shop_reference = $order->plentyId;

                // Insert Into OrderSyncedDataTable in any case token verified or not.
                $OrderSyncedRepo = pluginApp(OrderSyncedRepositoryContract::class);
                $OrderSynced = $OrderSyncedRepo->createOrderSync(['orderId' => $order->id]);

                if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){
                    // token good

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



                    // Address information

                    $addressRelations = $order->addressRelations;

                    $address_repo = pluginApp(AddressRepositoryContract::class);

                    $billing_address = null ;
                    $shipping_address = null;

                    foreach ($addressRelations as $address) {

                        $temp_address = $address_repo->findAddressById($address->addressId, ['options' , 'country']);

                        if ($address->typeId == 1){

                            $billing_address['company_title'] = $temp_address->name1;
                            $billing_address['fname'] = $temp_address->name2;
                            $billing_address['lname'] = $temp_address->name3;
                            $billing_address['address_line1'] = $temp_address->address1;
                            $billing_address['address_line2'] = $temp_address->address2;
                            $billing_address['address_line3'] = $temp_address->address3;
                            $billing_address['town_city'] = $temp_address->town;
                            $billing_address['zip_postal_code'] = $temp_address->postalCode;
                            $billing_address['country'] = $temp_address->country->name;

                            if (isset($temp_address->options)){

                                foreach ($temp_address->options as $address_option) {

                                    switch($address_option->typeId){
                                        case 5:
                                            $billing_address['email'] = $address_option->value;
                                            break;
                                        case 6:
                                            $billing_address['phone_number'] = $address_option->value;
                                            break;
                                    }
                                }
                            }

                        }else if($address->typeId == 2){

                            $shipping_address['company_title'] = $temp_address->name1;
                            $shipping_address['fname'] = $temp_address->name2;
                            $shipping_address['lname'] = $temp_address->name3;
                            $shipping_address['address_line1'] = $temp_address->address1;
                            $shipping_address['address_line2'] = $temp_address->address2;
                            $shipping_address['address_line3'] = $temp_address->address3;
                            $shipping_address['town_city'] = $temp_address->town;
                            $shipping_address['zip_postal_code'] = $temp_address->postalCode;
                            $shipping_address['country'] = $temp_address->country->name;

                            if (isset($temp_address->options)){

                                foreach ($temp_address->options as $address_option) {

                                    switch($address_option->typeId){
                                        case 5:
                                            $shipping_address['email'] = $address_option->value;
                                            break;
                                        case 6:
                                            $shipping_address['phone_number'] = $address_option->value;
                                            break;
                                    }
                                }
                            }
                        }
                    }

                    $out['billing_address'] = $billing_address ;
                    $out['shipping_address'] = $shipping_address;
                    // Address information

                    $items = [] ;
                    foreach ($order->orderItems as $o_item) {

                        if($o_item->typeId != 1){
                            continue;
                        }

                        $temp_itm =  array(
                            'item_reference_id' => $o_item->id,
                            'item_name' => $o_item->orderItemName,
                            'item_description' => '',
                            'item_extra_info' => '',
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

                        $out['item_line_total_gross'] = $out['item_line_total_gross'] + ($item_amount->priceGross * $o_item->quantity);
                        $out['item_line_total_net'] = $out['item_line_total_net'] + ($item_amount->priceNet * $o_item->quantity);

                    }

                    $out['order_details'] = $items;

                    $requestURL = ConfigHelper::BASE_URL .'/api/add_order';
                    $requestType = static::REQUEST_METHOD_POST;
                    $vendor_id = $this->configHelper->getVendorID();
                    $vendorSecret = $this->configHelper->getVendorSecret();

                    $headers = array(
                        'Authorization: ' . 'Bearer ' .$vendorSecret,
                    );

                    $responses = $this->doCurl($requestURL ,$requestType , $headers, $out);

                    $data = ['order_original' => $order , 'out' => $out, 'api_response' => $responses];
                    $data['preSynced'] = $OrderSynced;

                    if (is_array($responses) && $responses['success'] == true ){

                        // Update Sync
                        $MarkedOrderSynced = $OrderSyncedRepo->markSyncedOrder($OrderSynced->id);

                        $data['postSynced'] = $MarkedOrderSynced;
                        // Report save case
                        $this->getLogger('sendingOrderToLoyalista')->error('Export Order Pass', ['data'=> $data]);

                    }else{
                        $this->getLogger('sendingOrderToLoyalista')->error('Export Order Failed', ['data'=> $data]);
                    }
                }else{
                    $this->getLogger('sendOrderToLoyalista')->error('Api Token Not verified' , ['data' => ['error' => $tokenVerified]]);
                }
            }else{
                $this->getLogger('sendOrderToLoyalista')->error('Order not get');
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger('sendOrderToLoyalista')
                ->error('Exception Error while get order', ['message'=> $e->getMessage() ]);

        }
        finally {


        }
    }

    public function getCheckoutWidgetData(){

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){

        }

        $requestURL = ConfigHelper::BASE_URL .'/api/verify_customer';
        $requestType = static::REQUEST_METHOD_POST;
        $vendor_id = $this->configHelper->getVendorID();
        $vendorSecret = $this->configHelper->getVendorSecret();

        $headers = array(
            'Authorization: ' . 'Bearer ' .$vendorSecret,
        );

        $responses = $this->doCurl($requestURL ,$requestType , $headers, []);

    }


    public function getMyAccountWidgetData($loggedin_customer_id){

        $requestURL = ConfigHelper::BASE_URL .'/api/get_user_account_data';


        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){

            $response = $this->doCurl($requestURL ,$requestType , [], $data);




            return $response;

        }else{
            $this->getLogger('Token Verification Failed')->error('Loyalista Token Expire or invalid');
        }
    }


    public function getMyMergeAccountWidgetData($loggedin_customer_id){

        $requestURL = ConfigHelper::BASE_URL .'/api/get_user_merge_account_data';

        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){

            $response = $this->doCurl($requestURL ,$requestType , [], $data);




            return $response;

        }else{
            $this->getLogger('Token Verification Failed')->error('Loyalista Token Expire or invalid');
        }
    }


    public function getCartWidgetData($loggedin_customer_id){


        $requestURL = ConfigHelper::BASE_URL .'/api/get_user_cart_data';

        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);
            return $response;
        }else{
            $this->getLogger('Token Verification Failed')->error('Loyalista Token Expire or invalid');
        }
    }



    public function getCartCheckoutWidgetData($loggedin_customer_id){


        $requestURL = ConfigHelper::BASE_URL .'/api/get_user_checkout_cart_data';

        $requestType = static::REQUEST_METHOD_GET;

        $shopReference = $this->configHelper->getShopID();

        $data = [
            'shop_reference' => $shopReference,
            'reference_id' => $loggedin_customer_id,
        ];

        $tokenVerified =  $this->verifyApiToken();

        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);

            $this->getLogger('response')->error('Checkout Widget', ['res'=> $response ]);

            return $response;
        }else{
            $this->getLogger('Token Verification Failed')->error('Loyalista Token Expire or invalid');
        }
    }





    /**
     * Login Customer.
     * @param $data
     * @return mixed|string
     */
    public function registerCustomer($data)
    {
        $requestURL = ConfigHelper::BASE_URL .'/api/add_customer';
        $requestType = static::REQUEST_METHOD_POST;
        $tokenVerified =  $this->verifyApiToken();
        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);
            return $response;
        }
        else{
            $this->getLogger('Api Token')->error('Unverified Token Used');
        }
    }

    public function unRegisterCustomer($data)
    {
        $requestURL = ConfigHelper::BASE_URL .'/api/remove_customer';
        $requestType = static::REQUEST_METHOD_POST;
        $tokenVerified =  $this->verifyApiToken();
        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);
            return $response;
        }
        else{
            $this->getLogger('Api Token')->error('Unverified Token Used');
        }
    }


    public function mergeCustomr($data)
    {
        $requestURL = ConfigHelper::BASE_URL .'/api/merge_customer_request';
        $requestType = static::REQUEST_METHOD_POST;
        $tokenVerified =  $this->verifyApiToken();
        if (isset($tokenVerified['success']) &&  $tokenVerified['success'] == true ){
            $response = $this->doCurl($requestURL ,$requestType , [], $data);
            return $response;
        }
        else{
            $this->getLogger('Api Token')->error('Unverified Token Used');
        }
    }




    public function test($data)
    {
        $this->getLogger('dump')->error('Dump', ['data'=> $data]);

    }


}