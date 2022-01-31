<?php
/**
 * Created by PhpStorm.
 * User: Toheed
 * Date: 1/28/2022
 * Time: 3:55 PM
 */

namespace LoyalistaIntegration\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\Cron\Services\CronContainer;

use LoyalistaIntegration\EventProcedures\Procedures;


class ProcedurePluginServiceProvider extends ServiceProvider
{
    /**
     * @param EventProceduresService $eventProceduresService
     * @return void
     */
    public function boot(CronContainer $cronContainer, EventProceduresService $eventProceduresService)
    {


        // register crons
        /*
          $cronContainer->add(CronContainer::DAILY, FullInventorySyncCron::class);
          $cronContainer->add(CronContainer::EVERY_FIFTEEN_MINUTES, InventorySyncCron::class);
          $cronContainer->add(CronContainer::EVERY_FIFTEEN_MINUTES, OrderImportCron::class);
          $cronContainer->add(CronContainer::EVERY_FIFTEEN_MINUTES, OrderAcceptCron::class);
         **/

        $eventProceduresService->registerProcedure(
            'setStatus',
            ProcedureEntry::EVENT_TYPE_ORDER , ['de' => 'Setze Status auf 3', 'en' => 'Set status to 3'],
            Procedures::class . '@setStatus'
        );



    }
}