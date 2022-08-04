<?php
namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\LoyalistaHelper;
use LoyalistaIntegration\Helpers\ConfigHelper;

class MyAccountMergeWidget
{
    public function call(Twig $twig, $arg)
    {

        // Get Language
        $lang = 'en';

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




                $disclaimer = str_ireplace("[value_of_account_balance]" ,($points * $point_to_conversion) ,$disclaimer);

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
                return $twig->render('LoyalistaIntegration::content.container.Widget_MyAcoount_merge_account', $data);

            }else{


                /*$data['offer'] = $config_helper->getVar('my_account_text_for_unregistered_user_' .$lang);
                $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;
                $data['lang'] = $lang;

                return $twig->render('LoyalistaIntegration::content.container.Widget_MyAcoount_user_unregistered', $data);*/

            }
        }
    }
}