<?php
namespace LoyalistaIntegration\Cron;


use LoyalistaIntegration\Helpers\ConfigHelper;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use Plenty\Plugin\Log\Loggable;

/**
 * Class ConfigurationCron.
 */
class OrdersCron extends Cron
{
     use Loggable;
    private LoyalistaApiService $apiService;
    private ConfigHelper $configHelper;

    public function __construct(LoyalistaApiService $apiService, ConfigHelper $configHelper)
    {
        $this->apiService = $apiService;
        $this->configHelper = $configHelper;
    }

    /**
     * Handles Cron jobs.
     */
    public function handle()
    {
        $config = [
            $this->configHelper->getVar('order_ids'),
            $this->configHelper->getVar('date_from'),
            $this->configHelper->getVar('order_types'),
            $this->configHelper->getVar('order_statuses'),
        ];

        $this->getLogger('OrdersCron')->error(__FUNCTION__, $config);
    }
}
