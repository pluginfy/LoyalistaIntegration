<?php
namespace LoyalistaIntegration\Cron;

use LoyalistaIntegration\Helpers\ConfigHelper;
use LoyalistaIntegration\Services\ExportServices;
use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

/**
 * Class ConfigurationCron.
 */
class OrdersCron extends Cron
{
    private LoyalistaApiService $apiService;
    private ConfigHelper $configHelper;

    /**
     * @param LoyalistaApiService $apiService
     * @param ConfigHelper $configHelper
     */
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
        $exportService = pluginApp(ExportServices::class);
        $exportService->exportPreviousOrders();
    }
}
