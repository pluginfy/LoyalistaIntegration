<?php
namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use LoyalistaIntegration\Helpers\LoyalistaHelper;

use LoyalistaIntegration\Helpers\ConfigHelper;



class CartProductWidget
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
            $response =   $api->getCartWidgetData($plenty_customer_id);

            if (isset($response['success']) && $response['success'] == true){

                // Get Widget data
                $widgetdata = $response['data'];

                $user_registered = $widgetdata['user_registered'];

                if (!$user_registered){

                    $disclaimer = $config_helper->getVar('text_for_unregistered_users_for_product_and_shopping_cart_' .$lang );
                    $point_label  =   $config_helper->getVar('account_points_label_text_' .$lang);

                    // Replace Signup Event
                    $disclaimer = str_ireplace("[points_for_signup]" ,$widgetdata['signup_event_point'], $disclaimer);

                    // Replace Points Label
                    $disclaimer = str_ireplace("[points_label]" ,$point_label ,$disclaimer);

                    // Hydrate Number of points text
                    $html = '<span data-revenue_to_point="'. $widgetdata['revenue_to_point'] .'" class="loyalista_num_of_points">[number_of_points]</span>';

                    $disclaimer = str_ireplace("[number_of_points]" , $html  ,$disclaimer);


                    $data['contents'] = $disclaimer;
                    $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;

                    return $twig->render('LoyalistaIntegration::content.container.WidgetCartProduct', $data);

                }else{




                }


            }else{
             // Todo save error log
                $api->test($response);

            }
        }














    }
}