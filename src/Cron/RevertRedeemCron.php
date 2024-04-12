<?php
namespace LoyalistaIntegration\Cron;

use LoyalistaIntegration\Helpers\CouponHelper;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

/**
 * Class ConfigurationCron.
 */
class RevertRedeemCron extends Cron
{
    public $apiService;

    /**
     * @param LoyalistaApiService $apiService
     */
    public function __construct(LoyalistaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Handles Cron jobs.
     */
    public function handle()
    {
        $api = pluginApp(LoyalistaApiService::class);
        $response = $api->revertUnusedPoints('coupon');

        $couponHelper = pluginApp(CouponHelper::class);
        if($response['success'] && !empty($response['data'])) {
            foreach ($response['data'] as $customerPoint) {
                $couponHelper->deleteCampaignByCoupon($customerPoint);
            }
        }
    }
}
