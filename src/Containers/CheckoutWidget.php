<?php
namespace LoyalistaIntegration\Containers;

use LoyalistaIntegration\Helpers\LoyalistaHelper;
use Plenty\Plugin\Templates\Twig;

use Plenty\Modules\Frontend\Services\AccountService;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use LoyalistaIntegration\Helpers\ConfigHelper;

class CheckoutWidget
{
    public function call(Twig $twig, $arg)
    {
        $config_helper = pluginApp(ConfigHelper::class);
        $helper = pluginApp(LoyalistaHelper::class);

        $account_service = pluginApp(AccountService::class);
        $api = pluginApp(LoyalistaApiService::class);
        $lang = $config_helper->getCurrentLocale();
        $plenty_customer_id  = $account_service->getAccountContactId();
        $response =   $api->getCartCheckoutWidgetData($plenty_customer_id);

        $data = [
            'widget_border_width' => $helper->getWidgetBorderWidth(),
            'widget_border_color' => $helper->getWidgetBorderColor(),
            'widget_heading' => $helper->getWidgetHeading('checkout_widget_heading_text_')
        ];

        if (isset($response['success']) && $response['success'] && $response['data']['user_registered']) {
            $widgetdata = $response['data'];
            $text_registered = $config_helper->getVar('checkout_text_for_registered_user_' .$lang);
            $text_redeem_no =  $config_helper->getVar('checkout_text_for_no_redeem_the_points_' .$lang);
            $text_redeem_full =  $config_helper->getVar('checkout_text_for_full_redeeming_points_' .$lang);
            $text_redeem_partial =  $config_helper->getVar('checkout_text_partial_redemption_points_' .$lang);

            $html = '<span data-basket_total=""  data-total_redeemable_points="'. $widgetdata['total_redeemable_points'] .'" data-revenue_to_point="'. $widgetdata['revenue_to_point'] .'" data-point_to_value="'. $widgetdata['point_to_value'] .'" class="loyalista_co_num_of_points">[number_of_points_shopping_cart]</span>';

            $text_registered = str_ireplace("[account_balance]" ,$widgetdata['total_redeemable_points'], $text_registered);
            $text_registered = str_ireplace("[number_of_points_shopping_cart]" ,$html , $text_registered);
            $text_registered = $helper->replacePointsLabel($text_registered, $lang);

            // Full redeem.
            //$text_redeem_full = str_ireplace("[account_balance]" ,$widgetdata['total_redeemable_points'], $text_redeem_full);
            $text_redeem_full = str_ireplace("[account_balance]" ,'<span class="cow_account_balance">'.$widgetdata['total_redeemable_points'].'</span>', $text_redeem_full);
            $text_redeem_full = $helper->replacePointsLabel($text_redeem_full, $lang);

            $Point_value = ($widgetdata['total_redeemable_points'] * $widgetdata['point_to_value']);

            //$text_redeem_full = str_ireplace("[value_of_account_balance]" ,$Point_value , $text_redeem_full);

            $text_redeem_full = str_ireplace("[value_of_account_balance]" ,'<span class="cow_points_label">'.$Point_value.'</span>' , $text_redeem_full);

            $text_redeem_partial = $helper->replacePointsLabel($text_redeem_partial, $lang);


            $data['content_1'] = $text_registered ;
            $data['content_2'] = $text_redeem_no ;
            $data['content_3'] =  $text_redeem_full;
            $data['content_4'] =  $text_redeem_partial;
            $data['is_user_registered'] =  true;

            $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;
            $data['apply_redeem_btn_label'] = ($lang == 'de') ? 'Anwenden' : 'Apply' ;
            return $twig->render('LoyalistaIntegration::content.container.CheckoutWidget_registered', $data);

        } else {
            $disclaimer = $config_helper->getVar('checkout_text_unregistered_user_' .$lang );
            $disclaimer = $helper->replacePointsForSignup($disclaimer);
            $disclaimer = $helper->replacePointsLabel($disclaimer, $lang);

            // Hydrate Number of points text
            $html = '<span class="loyalista_co_num_of_points" data-total_redeemable_points="null" data-checkout_revenue_to_point="'. $config_helper->getVar('revenue_to_one_point') .'" data-point_to_value="'. $config_helper->getVar('one_point_to_value') .'">[number_of_points_shopping_cart]</span>';

            $disclaimer = str_ireplace("[number_of_points_shopping_cart]" , $html  ,$disclaimer);

            $data['is_user_registered'] =  false;
            $data['contents'] = $disclaimer;
            $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen!' : 'Participate' ;

            return $twig->render('LoyalistaIntegration::content.container.CheckoutWidget_unregistered', $data);
        }
    }
}