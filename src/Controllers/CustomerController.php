<?php
namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;


use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Frontend\Services\AccountService;

use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;

use Plenty\Plugin\Http\Request;
use Plenty\Validation\Validator;



use Plenty\Plugin\Log\Loggable;


// Temp CouponCampaignRepositoryContract

use Plenty\Modules\Order\Coupon\Campaign\Code\Contracts\CouponCampaignCodeRepositoryContract;
use Plenty\Modules\Order\Coupon\Campaign\Contracts\CouponCampaignRepositoryContract;



class CustomerController extends Controller
{
    use Loggable;

    public function unRegisterCustomer()
    {

        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        // user login
        if ($contact->id > 0){

            $configHelper = pluginApp(configHelper::class);
            $shopReference = $configHelper->getShopID();

            // data to send api
            $customer = array(
                'shop_reference' => $shopReference,
                'reference_id'=> $contact->id,
            );

            // Register in loyalista
            $api = pluginApp(LoyalistaApiService::class);
            $response = $api->unRegisterCustomer($customer);

            if ($response['success'] === true){
                $return = ['status' => 'OK'];
                return json_encode($return);
            }else{
                $return = ['status' => 'Error', 'message' => 'Failed' , 'response' => $response ];
                return json_encode($return);
            }
        }
    }

    public function registerCustomer()
    {
        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        // user login
        if ($contact->id > 0){

            $configHelper = pluginApp(configHelper::class);
            $shopReference = $configHelper->getShopID();

            // data to send api
            $customer = array(
                'shop_reference' => $shopReference,
                'reference_id'=> $contact->id,
                'name' => $contact->fullName,
                'email' => $contact->email
            );

            // Register in loyalista
            $api = pluginApp(LoyalistaApiService::class);
            $response = $api->registerCustomer($customer);

            if ($response['success'] == true){
                $return = ['status' => 'OK'];
                return json_encode($return);
            }else{
                $return = ['status' => 'Error', 'message' => 'Failed' , 'response' => $response  ];
                return json_encode($return);
            }
        }
    }


    public function mergeCustomer(Request $request)
    {
/*
        $rules = [
            'customer_email_address' => 'required|email',

        ];

        $messages = array(
            'required' => 'The :attribute field is required',
            'email' => 'The :attribute field must be valid email',
        );

        $validator = Validator::make(Input::all(), $rules, $messages);

        if ($validator->fails()) {

            $return = ['status' => 'Error', 'message' => 'Failed' , 'response' => $this->errorResponse($validator->errors()->all())];
            return json_encode($return);
        }*/


        $post_data = $request->all();

        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $contactRepo = pluginApp(ContactRepositoryContract::class);
        $contact = $contactRepo->findContactById($plenty_customer_id);

        // user login
        if ($contact->id > 0){

            $configHelper = pluginApp(configHelper::class);
            $shopReference = $configHelper->getShopID();

            // data to send api
            $customer = array(
                'shop_reference' => $shopReference,
                'reference_id'=> $contact->id,
                'customer_email_address' => $post_data['customer_email_address'],
            );

            // Register in loyalista
            $api = pluginApp(LoyalistaApiService::class);

            $response = $api->mergeCustomr($customer);

            if ($response['success'] === true){
                $return = ['status' => 'OK'];
                return json_encode($return);
            }else{
                $return = ['status' => 'ERROR', 'message' => $response['message']];
                return json_encode($return);
            }
        }
    }

}