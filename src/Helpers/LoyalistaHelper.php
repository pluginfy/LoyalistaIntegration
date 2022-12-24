<?php

namespace LoyalistaIntegration\Helpers;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use Plenty\Modules\Frontend\Services\AccountService;


class LoyalistaHelper
{


    private $accountService;
    private $configHelper;


    public function __construct(AccountService $accountService ,ConfigHelper $configHelper )
    {
        $this->accountService = $accountService;
        $this->configHelper = $configHelper;
    }

    public function getCurrentContactId(): int
    {
        return $this->accountService->getAccountContactId();
    }


    public function hydrate_product_contents($isRegistered, $itemIdentifier, $itemPrice, $data = [])
    {
        $lang = $this->configHelper->getCurrentLocale();

        $out = NULL;
        if ($isRegistered){
            $out  = $this->configHelper->getVar('text_for_registered_users_for_the_product_page_' .$lang );
        }else{
            $out = $this->configHelper->getVar('text_for_unregistered_users_for_product_page_' . $lang);
            $out = $this->replacePointsForSignup($out);
        };

        $earningPoints = number_format(round($itemPrice / $this->configHelper->getVar('revenue_to_one_point')), 0, ',', '.');
        $out = str_ireplace("[points_for_product]", $earningPoints ,$out);
        $out = str_ireplace("[number_of_points]", $earningPoints ,$out);

        if(in_array($itemIdentifier, explode(',',$this->configHelper->getVar('product_ids')))) {
            $out .= ' ' .$this->configHelper->getVar('text_for_registered_users_for_extra_points_for_the_product_page_' .$lang );
            $out = str_ireplace("[number_of_extra_points]" ,number_format($this->configHelper->getVar('product_extra_points'), 0, ',', '.') ,$out);
        }

        $out = $this->replacePointsLabel($out, $lang);

        return $out;
    }


    public function hydrate_my_account_data()
    {
        $date_of_expiry = 30;

        $lang =  $this->configHelper->getCurrentLocale();
        $plenty_customer_id  = $this->accountService->getAccountContactId();

        $api = pluginApp(LoyalistaApiService::class);
        $response = $api->getMyMergeAccountWidgetData($plenty_customer_id);

        $data = [
            'plenty_customer_id' => $plenty_customer_id ,
            'is_user_registered' => false ,
            'widget_heading'  =>   $this->configHelper->getVar('my_account_widget_heading_text_' .$lang),
            'widget_border_width' => $this->getWidgetBorderWidth(),
            'widget_border_color' => $this->getWidgetBorderColor(),
        ];

        if (isset($response['success']) && $response['success'] && $response['data']['user_registered']) {
            $data['is_user_registered'] = true;
            $widgetdata = $response['data'];
                $point_label  =   $this->configHelper->getVar('account_points_label_text_' .$lang);
                $customer = $widgetdata['customer'];
                $points =  $widgetdata['points'];
                $point_to_conversion = $widgetdata['point_to_conversion'];
                $txt_redeem_points = $this->configHelper->getVar('my_account_text_for_exiting_the_participation_redeem_hint_text_' .$lang);
                $txt_locked_points = $this->configHelper->getVar('my_account_text_for_exiting_the_participation_locked_hint_text_' .$lang);
                $txt_expiry_points = $this->configHelper->getVar('my_account_text_for_exiting_the_participation_expiry_hint_text_' .$lang);
                $txt_merge_account = $this->configHelper->getVar('my_account_text_for_exiting_the_participation_join_request_hint_text_' .$lang);
                $disclaimer = $this->configHelper->getVar('my_account_text_for_exiting_the_participation_' .$lang);


                $txt_redeem_points = str_ireplace("[total_number_of_redeemable_points]" , number_format($widgetdata['total_number_of_redeemable_points'], 0, ',', '.'), $txt_redeem_points);
                $txt_redeem_points = $this->replacePointsLabel($txt_redeem_points, $lang);

                $txt_locked_points = str_ireplace("[total_number_of_locked_points]" , number_format($widgetdata['total_number_of_locked_points'], 0, ',', '.'), $txt_locked_points);
                $txt_locked_points = $this->replacePointsLabel($txt_locked_points, $lang);

                $txt_expiry_points = str_ireplace("[amount_of_points]"  ,number_format($widgetdata['expired_amount_of_points'], 0, ',', '.'), $txt_expiry_points);
                $txt_expiry_points = str_ireplace("[date_of_expiry]"  ,$date_of_expiry, $txt_expiry_points);
                $txt_expiry_points = $this->replacePointsLabel($txt_expiry_points, $lang);

                $disclaimer = str_ireplace("[value_of_account_balance]" ,number_format($points * $point_to_conversion, 2, ',', '.'), $disclaimer);

                $disclaimer = $this->replacePointsLabel($disclaimer, $lang);

                $data['disclaimer'] = $disclaimer;
                $data['txt_redeem_points'] = $txt_redeem_points;
                $data['txt_locked_points'] = $txt_locked_points;
                $data['txt_expiry_points'] = $txt_expiry_points;
                $data['txt_merge_account'] = $txt_merge_account;
                $data['loyalista_customer_id'] = $customer['id'];
                $data['join_btn_label'] = ($lang == 'de') ? 'Verbinden' : 'Merge' ;
                $data['btn_label'] = ($lang == 'de') ? 'Löschen' : 'Delete' ;
                $data['lang'] = $lang;

        } else {
            $data['offer'] = $this->configHelper->getVar('my_account_text_for_unregistered_user_' .$lang);
            $data['btn_label'] = ($lang == 'de') ? 'Teilnehmen' : 'Participate' ;
            $data['lang'] = $lang;

            $data['offer'] = $this->replacePointsForSignup($data['offer']);
        }

        return $data;
    }

    public function replacePointsLabel($content, $lang = 'de'): array|string
    {
        $point_label = $this->configHelper->getVar('account_points_label_text_' .$lang);
        return str_ireplace("[points_label]" ,$point_label ,$content);
    }

    public function replacePointsForSignup($content): array|string
    {
        $signupPoints = number_format($this->configHelper->getVar('signup_points'), 0, ',', '.');
        return str_ireplace("[points_for_signup]" ,$signupPoints ,$content);
    }

    public function getWidgetHeading($widget): string
    {
        $lang = $this->configHelper->getCurrentLocale();
        return $this->configHelper->getVar($widget . $lang);
    }

    public function getWidgetBorderWidth(): string
    {
        return $this->configHelper->getVar('widget_border_width');
    }

    public function getWidgetBorderColor(): string
    {
        return $this->configHelper->getVar('widget_border_color');
    }
}
