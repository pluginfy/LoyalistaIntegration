<?php

namespace LoyalistaIntegration\Services\API;

use LoyalistaIntegration\Core\Api\BaseApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;
use Plenty\Log\Contracts\LoggerContract;

class LoyalistaApiService extends BaseApiService
{


    public function __construct(ConfigHelper $configHelper, LoggerContract $loggerContract)
    {
        parent::__construct($configHelper, $loggerContract);
    }


    public function verifyUserToken()
    {
        $requestURL = ConfigHelper::BASE_URL .'/api/validate_user_token';

        $requestType = static::REQUEST_METHOD_POST;

        $vendor_id = $this->configHelper->getVendorID();
        $vendorSecret = $this->configHelper->getVendorSecret();

        $data = [
            'vendor_id' => $vendor_id,
            'access_token' => $vendorSecret
        ];

        $resp = $this->doCurl($requestURL ,$requestType , [], $data);

        return $resp;
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

        $resp = $this->doCurl($requestURL ,$requestType , $headers, $data);

        return $resp;

    }

    public function createCustomer($customer = '')
    {
        //Todo get customer from plenty market from db crone.
        $customer = array(
            'reference_id'=> '105',
            'name' => 'Jack Ahamad',
            'email' => 'jack_man@yahoo.com'
        );

        $requestURL = ConfigHelper::BASE_URL .'/api/add_customer';

        $requestType = static::REQUEST_METHOD_POST;

        $vendor_id = $this->configHelper->getVendorID();
        $vendorSecret = $this->configHelper->getVendorSecret();

        $headers = array(
            'Authorization: ' . 'Bearer ' .$vendorSecret,
        );

        $resp = $this->doCurl($requestURL ,$requestType , $headers, $customer);

        return $resp;

    }

    public function createOrder($order = [])
    {

        //Todo get order from plenty market db while cron.
        $order = array(
            'customer' => array(
                'reference_id'=> '105',
                'name' => 'Toheed Ahamad',
                'email' => 'jack_man@yahoo.com'
            ),
            'reference_id'=> 4,
            'item_line_total'=> 300,
            'grand_total' => 300,
            'order_details' => array (
                array(
                   'item_reference_id' => 1,
                   'item_qty' => 1,
                   'item_name' => '',
                   'item_description' => '',
                   'item_price' => 50.00,
               ),
                array(
                    'item_reference_id' => 2,
                    'item_name' => '',
                    'item_description' => '',
                    'item_qty' => 1,

                    'item_price' => 100.00,
                ),
                array(
                    'item_reference_id' => 3,
                    'item_name' => '',
                    'item_description' => '',
                    'item_qty' => 1,
                    'item_price' => 150.00,
                )

            )
        );



        $requestURL = ConfigHelper::BASE_URL .'/api/add_order';

        $requestType = static::REQUEST_METHOD_POST;

        $vendor_id = $this->configHelper->getVendorID();
        $vendorSecret = $this->configHelper->getVendorSecret();

        $headers = array(
            'Authorization: ' . 'Bearer ' .$vendorSecret,
        );

        $resp = $this->doCurl($requestURL ,$requestType , $headers, $order);

        return $resp;

    }

    public function redeemPoints()
    {
        
    }

    public function exportCustomers()
    {


    }

    




}