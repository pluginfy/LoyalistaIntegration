<?php
namespace LoyalistaIntegration\Cron;

use LoyalistaIntegration\Helpers\CouponHelper;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use Plenty\Plugin\Log\Loggable;

/**
 * Class ConfigurationCron.
 */
class RevertRedeemCron extends Cron
{
     use Loggable;
    public $apiService;
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

        $this->getLogger('RevertRedeemCron')->error(__FUNCTION__, $response);
    }
}
