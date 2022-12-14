<?php

namespace LoyalistaIntegration\Helpers;
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


    public function hydrate_product_contents($user_id, $itemIdentifier, $itemPrice, $data = [])
    {
        $lang = $this->configHelper->getCurrentLocale();

        $out = NULL;
        if ($user_id > 0){
            $out  = $this->configHelper->getVar('text_for_registered_users_for_the_product_page_' .$lang );
        }else{
            $out = $this->configHelper->getVar('text_for_unregistered_users_for_product_page_' . $lang);
            $out =$this->replacePointsForSignup($out);
        };

        $out = $this->replacePointsLabel($out, $lang);
        $earningPoints = intval(floor($itemPrice / $this->configHelper->getVar('revenue_to_one_point')));

        $out = str_ireplace("[points_for_product]", $earningPoints ,$out);
        $out = str_ireplace("[number_of_points]", $earningPoints ,$out);

        if(in_array($itemIdentifier, explode(',',$this->configHelper->getVar('product_ids')))) {
            $out .= ' ' .$this->configHelper->getVar('text_for_registered_users_for_extra_points_for_the_product_page_' .$lang );
            $out = str_ireplace("[number_of_extra_points]" ,$this->configHelper->getVar('product_extra_points') ,$out);
        }

        return $out;
    }


    public function replacePointsLabel($content, $lang): array|string
    {
        $point_label = $this->configHelper->getVar('account_points_label_text_' .$lang);
        return str_ireplace("[points_label]" ,$point_label ,$content);
    }

    public function replacePointsForSignup($content): array|string
    {
        $point_label = $this->configHelper->getVar('signup_points');
        return str_ireplace("[points_for_signup]" ,$point_label ,$content);
    }

    public function getWidgetHeading($widget): string
    {
        $lang = $this->configHelper->getCurrentLocale();
        return $this->configHelper->getVar($widget . $lang);
    }
}
