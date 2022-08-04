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


    public function hydrate_check_out_contents($loyalista_customer_id , $lang = 'de' , $data = [])
    {
        $out = NULL;
        if ($loyalista_customer_id > 0){
            $out['checkout_text_for_registered_user']  =  $this->configHelper->getVar('checkout_text_for_registered_user_' .$lang );
            $out['checkout_text_for_no_redeem_the_points'] =  $this->configHelper->getVar('checkout_text_for_no_redeem_the_points_' .$lang );
            $out['checkout_text_partial_redemption_points'] =  $this->configHelper->getVar('checkout_text_partial_redemption_points_' .$lang );
            $out['checkout_text_for_full_redeeming_points'] =  $this->configHelper->getVar('checkout_text_for_full_redeeming_points_' .$lang );
        }else{
            $out['checkout_text_unregistered_user'] = $this->configHelper->getVar('checkout_text_unregistered_user_' .$lang );
        }

        return $out;
    }

    public function hydrate_product_contents($user_id , $lang = 'de' , $data = [])
    {
        $out = NULL;
        if ($user_id > 0){
            $out  = $this->configHelper->getVar('text_for_registered_users_for_the_product_page_' .$lang );
            $out .= '--------------------' . $this->configHelper->getVar('text_for_registered_users_for_extra_points_for_the_product_page_' .$lang );
        }else{
            $out = '';
        }
        return $out;
    }

    public function hydrate_cart_product_contents($user_id , $lang = 'de' , $data = [])
    {
        $out = NULL;
        if ($user_id > 0){
            $out  = $this->configHelper->getVar('text_for_unregistered_users_for_product_and_shopping_cart_' .$lang );

        }else{
            $out = '';
        }
        return $out;
    }

    public function hydrate_my_account_contents($plenty_customer_id , $loyalista_customer_id , $lang = 'de' , $data = [])
    {
        $out = NULL;
        if ($loyalista_customer_id > 0){
            $out['my_account_text_for_exiting_the_participation']  = $this->configHelper->getVar('my_account_text_for_exiting_the_participation_' .$lang );
        }else{
            $out['checkout_text_unregistered_user'] =  $this->configHelper->getVar('checkout_text_unregistered_user_' .$lang );
        }
        return $out;
    }
}
