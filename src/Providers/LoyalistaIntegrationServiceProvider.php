<?php

namespace LoyalistaIntegration\Providers;

use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use LoyalistaIntegration\Contracts\ToDoRepositoryContract;
use LoyalistaIntegration\Repositories\ToDoRepository;
use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;
use LoyalistaIntegration\Repositories\OrderSyncedRepository;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use LoyalistaIntegration\EventProcedures\LoyalistaProcedures;
use LoyalistaIntegration\Cron\ConfigurationCron;
use LoyalistaIntegration\Cron\OrdersCron;
use LoyalistaIntegration\Cron\RevertRedeemCron;

/**
 * Class LoyalistaIntegrationServiceProvider
 * @package LoyalistaIntegration\Providers
 */
class LoyalistaIntegrationServiceProvider extends ServiceProvider
{
    use Loggable;
    /**
    * Register the route service provider
    */
    public function register()
    {
        $this->getApplication()->register(LoyalistaIntegrationRouteServiceProvider::class);
        $this->getApplication()->bind(ToDoRepositoryContract::class, ToDoRepository::class);
        $this->getApplication()->bind(OrderSyncedRepositoryContract::class, OrderSyncedRepository::class);
    }

    public function boot(EventProceduresService $eventProceduresService, CronContainer $cronContainer)
    {
        $cronContainer->add(CronContainer::EVERY_FIVE_MINUTES, ConfigurationCron::class);
        $cronContainer->add(CronContainer::EVERY_FIVE_MINUTES, OrdersCron::class);
        $cronContainer->add(CronContainer::EVERY_FIVE_MINUTES, RevertRedeemCron::class);

        $eventProceduresService->registerProcedure(
            'exportOrder',
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Loyalista - Export/Send Order',
                'en' => 'Loyalista - Export/Send Order'
            ],
            LoyalistaProcedures::class . '@exportOrder'
        );

         $eventProceduresService->registerProcedure(
            'refundOrder',
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Loyalista - Refund Order / Revert Points',
                'en' => 'Loyalista - Refund Order / Revert Points'
            ],
            LoyalistaProcedures::class . '@refundOrder'
        );
    }
}