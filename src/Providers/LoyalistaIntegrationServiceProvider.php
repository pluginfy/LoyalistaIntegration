<?php

namespace LoyalistaIntegration\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
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
    /**
    * Register the route service provider
    */
    public function register()
    {
        $this->getApplication()->register(LoyalistaIntegrationRouteServiceProvider::class);
        $this->getApplication()->bind(OrderSyncedRepositoryContract::class, OrderSyncedRepository::class);
    }

    public function boot(EventProceduresService $eventProceduresService, CronContainer $cronContainer)
    {
        $cronContainer->add(CronContainer::HOURLY, ConfigurationCron::class);
        $cronContainer->add(CronContainer::HOURLY, OrdersCron::class);
        $cronContainer->add(CronContainer::EVERY_FIFTEEN_MINUTES, RevertRedeemCron::class);

        $eventProceduresService->registerProcedure(
            'exportOrder',
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Loyalista: Auftrag an Loyalista senden',
                'en' => 'Loyalista: Send order to Loyalista'
            ],
            LoyalistaProcedures::class . '@exportOrder'
        );

         $eventProceduresService->registerProcedure(
            'refundOrder',
            ProcedureEntry::EVENT_TYPE_ORDER,
            [
                'de' => 'Loyalista: Gutschrift an Loyalista senden',
                'en' => 'Loyalista: Send credit to Loyalista'
            ],
            LoyalistaProcedures::class . '@refundOrder'
        );
    }
}