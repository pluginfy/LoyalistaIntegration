<?php

namespace LoyalistaIntegration\Crons;

use Plenty\Modules\Cron\Contracts\CronHandler as Cron;
use LoyalistaIntegration\Services\ExportOrderService;
use Plenty\Plugin\Log\Loggable;

class OrderExportCron extends Cron
{

    const ERROR_CODE_CRON = 1;
    use Loggable;

    private $orderExportServices;


    public function __construct(ExportOrderService $orderExportServices)
    {
        $this->orderExportServices = $orderExportServices;
    }

    /**
     * Handles Cron jobs.
     */
    public function handle()
    {
       echo 'ho';
    }


}