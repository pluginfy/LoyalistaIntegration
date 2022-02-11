<?php

namespace LoyalistaIntegration\Services\API;

use LoyalistaIntegration\Core\Api\BaseApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Plugin\Log\Loggable;

use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;
use Plenty\Plugin\Log\Reportable;

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

    public function createOrder($order = NULL)
    {
        try {
            // https://developers.plentymarkets.com/en-gb/developers/main/rest-api-guides/order-data.html
            if ($order)
            {

                // Insert Into OrderSyncedDataTable in any case.
                $OrderSyncedRepo = pluginApp(OrderSyncedRepositoryContract::class);
                $OrderSynced = $OrderSyncedRepo->createOrderSync(['orderId' => $order->id, 'isSynced' => false]);

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
                                    'name' => $contact->fullName,
                                    'email' => $contact->email );


                $out['customer'] = $customer;
                $out['reference_id'] = $order->id;
                $amounts = $order->amounts;

                $amount = $amounts[0];

                $out['item_line_total'] = $amount->netTotal;
                $out['grand_total'] = $amount->invoiceTotal;

                // Tax on Products
               $vats = $amount->vats;
               $vat = $vats[0];
              // $out['tax_percentage'] = $vat->vatRate;

               $items = [] ;

                foreach ($order->orderItems as $o_item) {

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
                   $temp_itm['item_price'] = $item_amount->priceNet;
                   $items[] = $temp_itm;
                }

                $out['order_details'] = $items;

                $requestURL = ConfigHelper::BASE_URL .'/api/add_order';
                $requestType = static::REQUEST_METHOD_POST;
                $vendor_id = $this->configHelper->getVendorID();
                $vendorSecret = $this->configHelper->getVendorSecret();

                $headers = array(
                    'Authorization: ' . 'Bearer ' .$vendorSecret,
                );


                $verified =  $this->verifyApiToken();


                $resp = $this->doCurl($requestURL ,$requestType , $headers, $out);

                $data = ['verified' => $verified['success'] , 'order_original' => $order , 'out' => $out, 'api_response' => $resp , 'type' => is_object($resp)];

                // Log Entry
                $this->getLogger('sendingOrderToLoyalista')->error('get_customer', ['data'=> $data]);

            }else{
                $this->getLogger('sendOrderToLoyalista')->error('only fail', ['contact'=> NULL]);
            }
        }

        catch (\Exception $e)
        {
            $this->getLogger('sendOrderToLoyalista')
                ->error('Error while get order', ['message'=> $e->getMessage() ]);

            // TODO add to external log api service

        }
        finally {
            // TODO count to external apli log service


        }

        return true;
    }

    public function redeemPoints()
    {
        
    }

    public function exportCustomers()
    {


    }

    




}