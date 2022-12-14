<?php
namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Frontend\Services\AccountService;

use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\ConfigHelper;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Log\Loggable;

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

    public function myAccountWidget(Request $request, Twig $twig)
    {
        // Get Language
        $lang = $request->getLocale();

        // Get Loggedin Customer
        // Allways loggedin.
        $account_service = pluginApp(AccountService::class);
        $plenty_customer_id  = $account_service->getAccountContactId();

        $api = pluginApp(LoyalistaApiService::class);
        $response =   $api->getMyMergeAccountWidgetData($plenty_customer_id);

        if (isset($response['success']) && $response['success'] == true){

            $config_helper = pluginApp(ConfigHelper::class);

            $widgetdata = $response['data'];

            $user_registered = $widgetdata['user_registered'];

            $data = array(
                'plenty_customer_id' => $plenty_customer_id ,
                'is_user_registered' => $user_registered ,
                'widget_heading'  =>   $config_helper->getVar('my_account_widget_heading_text_' .$lang),
        );

            if ($user_registered){
                $point_label  =   $config_helper->getVar('account_points_label_text_' .$lang);

                $customer = $widgetdata['customer'];

                $points =  $widgetdata['points'];

                $point_to_conversion = $widgetdata['point_to_conversion'];

                $txt_redeem_points = $config_helper->getVar('my_account_text_for_exiting_the_participation_redeem_hint_text_' .$lang);
                $txt_locked_points = $config_helper->getVar('my_account_text_for_exiting_the_participation_locked_hint_text_' .$lang);
                $txt_expiry_points = $config_helper->getVar('my_account_text_for_exiting_the_participation_expiry_hint_text_' .$lang);
                $txt_merge_account = $config_helper->getVar('my_account_text_for_exiting_the_participation_join_request_hint_text_' .$lang);
                $disclaimer = $config_helper->getVar('my_account_text_for_exiting_the_participation_' .$lang);


                $txt_redeem_points = str_ireplace("[total_number_of_redeemable_points]" , $widgetdata['total_number_of_redeemable_points'],$txt_redeem_points);
                $txt_redeem_points = str_ireplace("[name_of_points]" , $point_label, $txt_redeem_points);

                $txt_locked_points = str_ireplace("[total_number_of_locked_points]" , $widgetdata['total_number_of_locked_points'],$txt_locked_points);
                $txt_locked_points = str_ireplace("[name_of_points]" , $point_label , $txt_locked_points);

                $txt_expiry_points = str_ireplace("[amount_of_points]"  ,$widgetdata['expired_amount_of_points'],$txt_expiry_points);
                $txt_expiry_points = str_ireplace("[name_of_points]"  ,$point_label , $txt_expiry_points);

                $disclaimer = str_ireplace("[value_of_account_balance]" ,floor($points * $point_to_conversion) ,$disclaimer);

                $disclaimer = str_ireplace("[points_label]" ,$point_label ,$disclaimer);

                $data['disclaimer'] = $disclaimer;
                $data['txt_redeem_points'] = $txt_redeem_points;
                $data['txt_locked_points'] = $txt_locked_points;
                $data['txt_expiry_points'] = $txt_expiry_points;
                $data['txt_merge_account'] = $txt_merge_account;
                $data['loyalista_customer_id'] = $customer['id'];
                $data['join_btn_label'] = ($lang == 'de') ? 'Verbinden' : 'Join' ;
                $data['btn_label'] = ($lang == 'de') ? 'Löschen!' : 'Delete user' ;
                $data['lang'] = $lang;

            }else{
                $data['offer'] = $config_helper->getVar('my_account_text_for_unregistered_user_' .$lang);
                $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;
                $data['lang'] = $lang;
            }

            return $twig->render('LoyalistaIntegration::content.MyAccountPage', $data);
        }
    }
}