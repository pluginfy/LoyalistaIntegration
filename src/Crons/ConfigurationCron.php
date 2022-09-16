<?php

namespace LoyalistaIntergation\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use Plenty\Plugin\Log\Loggable;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

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


    public function __construct()
    {
        
    }

    /**
     * Handles Cron jobs.
     */
    public function handle()
    {
        $apiService = pluginApp(LoyalistaApiService::class);

        $rtn = $apiService->pullConfiguration();

        $this->getLogger(__FUNCTION__)->error('Configuration-Cron',$rtn);

        // $this->getLogger(__FUNCTION__)->info(self::ERROR_CODE_CRON, 'Cron is running...:)');
    }
}
