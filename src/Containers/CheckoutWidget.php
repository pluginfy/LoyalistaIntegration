<?php
namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use LoyalistaIntegration\Helpers\ConfigHelper;

class CheckoutWidget
{
    public function call(Twig $twig, $arg)
    {

        $lang = 'en';
        $config_helper = pluginApp(ConfigHelper::class);
        $account_service = pluginApp(AccountService::class);
        $api = pluginApp(LoyalistaApiService::class);

        // Get Loggedin Customer
        // Allways loggedin.
        $plenty_customer_id  = $account_service->getAccountContactId();

        // User must Logged IN
        if ($plenty_customer_id){

            // Checked User is registered
            $response =   $api->getCartCheckoutWidgetData($plenty_customer_id);

            if (isset($response['success']) && $response['success'] == true){

                // Get Widget data
                $widgetdata = $response['data'];

                $user_registered = $widgetdata['user_registered'];

                if ($user_registered){


                    $text_registered = $config_helper->getVar('checkout_text_for_registered_user_' .$lang);
                    $text_redeem_no =  $config_helper->getVar('checkout_text_for_no_redeem_the_points_' .$lang);
                    $text_redeem_full =  $config_helper->getVar('checkout_text_for_full_redeeming_points_' .$lang);
                    $text_redeem_partial =  $config_helper->getVar('checkout_text_partial_redemption_points_' .$lang);


                    $point_label  =   $config_helper->getVar('account_points_label_text_' .$lang);

                    $html = '<span data-basket_total=""  data-total_redeemable_points="'. $widgetdata['total_redeemable_points'] .'" data-revenue_to_point="'. $widgetdata['revenue_to_point'] .'" data-point_to_value="'. $widgetdata['point_to_value'] .'" class="loyalista_co_num_of_points">[number_of_points_shopping_cart]</span>';


                    $text_registered = str_ireplace("[account_balance]" ,$widgetdata['total_redeemable_points'], $text_registered);
                    $text_registered = str_ireplace("[number_of_points_shopping_cart]" ,$html , $text_registered);
                    $text_registered = str_ireplace("[points_label]" ,$point_label , $text_registered);



                    // Full redeem.
                    //$text_redeem_full = str_ireplace("[account_balance]" ,$widgetdata['total_redeemable_points'], $text_redeem_full);
                    $text_redeem_full = str_ireplace("[account_balance]" ,'<span class="cow_account_balance">'.$widgetdata['total_redeemable_points'].'</span>', $text_redeem_full);
                    $text_redeem_full = str_ireplace("[points_label]" ,$point_label , $text_redeem_full);



                    $Point_value = ($widgetdata['total_redeemable_points'] * $widgetdata['point_to_value']);

                    //$text_redeem_full = str_ireplace("[value_of_account_balance]" ,$Point_value , $text_redeem_full);

                    $text_redeem_full = str_ireplace("[value_of_account_balance]" ,'<span class="cow_points_label">'.$Point_value.'</span>' , $text_redeem_full);

                    // Partial redeem.
                    $html = '<span data-total_redeemable_points="'. $widgetdata['total_redeemable_points'] .'" data-revenue_to_point="'. $widgetdata['revenue_to_point'] .'" data-point_to_value="'. $widgetdata['point_to_value'] .'" class="loyalista_co_num_of_points">[number_of_points]</span>';
                    $text_redeem_partial = str_ireplace("[points_label]" ,$point_label , $text_redeem_partial);


                    $data['content_1'] = $text_registered ;
                    $data['content_2'] = $text_redeem_no ;
                    $data['content_3'] =  $text_redeem_full;
                    $data['content_4'] =  $text_redeem_partial;

                    $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;
                    return $twig->render('LoyalistaIntegration::content.container.WidgetCheckoutRegistered', $data);

                }else{

                    $point_label  =   $config_helper->getVar('account_points_label_text_' .$lang);


                    $disclaimer = $config_helper->getVar('checkout_text_unregistered_user_' .$lang );

                    // Replace Signup Event
                    $disclaimer = str_ireplace("[points_for_signup]" ,$widgetdata['signup_event_point'], $disclaimer);

                    // Replace Points Label
                    $disclaimer = str_ireplace("[points_label]" ,$point_label ,$disclaimer);

                    // Hydrate Number of points text
                    $html = '<span class="loyalista_co_num_of_points" data-total_redeemable_points="null" data-checkout_revenue_to_point="'. $widgetdata['revenue_to_point'] .'" data-point_to_value="'. $widgetdata['point_to_value'] .'">[number_of_points_shopping_cart]</span>';

                    $disclaimer = str_ireplace("[number_of_points_shopping_cart]" , $html  ,$disclaimer);

                    $data['contents'] = $disclaimer;
                    $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;

                    return $twig->render('LoyalistaIntegration::content.container.WidgetCheckoutUnregistered', $data);
                }

            }else{
                // Todo save error log
                $api->logData($response, __FUNCTION__);

            }
        }
    }
}