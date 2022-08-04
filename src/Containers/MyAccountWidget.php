<?php
namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use LoyalistaIntegration\Helpers\LoyalistaHelper;
use LoyalistaIntegration\Helpers\ConfigHelper;

class MyAccountWidget
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
        $response =   $api->getMyAccountWidgetData($plenty_customer_id);

        if (isset($response['success']) && $response['success'] == true){

            $config_helper = pluginApp(ConfigHelper::class);

            $widgetdata = $response['data'];

            $user_registered = $widgetdata['user_registered'];

            $data = array(
                'plenty_customer_id' => $plenty_customer_id ,
            );

            if ($user_registered){

                $customer = $widgetdata['customer'];

                $points =  $widgetdata['points'];

                $point_to_conversion = $widgetdata['point_to_conversion'];

                $disclaimer = $config_helper->getVar('my_account_text_for_exiting_the_participation_' .$lang);

                $disclaimer = str_ireplace("[value_of_account_balance]" ,($points * $point_to_conversion) ,$disclaimer);

                $point_label  =   $config_helper->getVar('account_points_label_text_' .$lang);

                $disclaimer = str_ireplace("[points_label]" ,$point_label ,$disclaimer);

                $data['disclaimer'] = $disclaimer;
                $data['loyalista_customer_id'] = $customer['id'];
                $data['btn_label'] = ($lang == 'de') ? 'Löschen!' : 'Delete user' ;
                $data['lang'] = $lang;
                return $twig->render('LoyalistaIntegration::content.container.Widget_MyAcoount_user_registered', $data);

            }else{

                $data['offer'] = $config_helper->getVar('my_account_text_for_unregistered_user_' .$lang);
                $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;
                $data['lang'] = $lang;

                return $twig->render('LoyalistaIntegration::content.container.Widget_MyAccount_user_unregistered', $data);

            }
        }
    }
}