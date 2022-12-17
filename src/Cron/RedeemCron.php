<?php
namespace LoyalistaIntegration\Cron;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use Plenty\Plugin\Log\Loggable;

/**
 * Class ConfigurationCron.
 */
class RedeemCron extends Cron
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
           $this->getLogger('RedeemCron')->error(__FUNCTION__, 'Cron is working');
    }
}
