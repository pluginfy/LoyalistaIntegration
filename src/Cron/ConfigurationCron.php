<?php
namespace LoyalistaIntegration\Cron;


use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

/**
 * Class ConfigurationCron.
 */
class ConfigurationCron extends Cron
{
    /**
     * Error code types.
     */
    const ERROR_CODE_CRON = 'CronStatus';

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
        $this->apiService->pushConfiguration();
    }
}
