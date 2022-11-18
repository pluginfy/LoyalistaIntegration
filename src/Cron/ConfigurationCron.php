<?php
namespace LoyalistaIntegration\Cron;


use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use Plenty\Plugin\Log\Loggable;

/**
 * Class ConfigurationCron.
 */
class ConfigurationCron extends Cron
{
     use Loggable;

    /**
     * Error code types.
     */
    const ERROR_CODE_CRON = 'CronStatus';

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
           $response = $this->apiService->pushConfiguration();

           $this->getLogger(__CLASS__)->error(__FUNCTION__, $response);
    }
}
