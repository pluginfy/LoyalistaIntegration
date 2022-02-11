<?php
namespace LoyalistaIntegration\Repositories;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;
use LoyalistaIntegration\Models\OrderSynced;

class OrderSyncedRepository implements OrderSyncedRepositoryContract
{

    public function createOrderSync(array $data): OrderSynced
    {

        /**
         * @var DataBase $database
         */
        $database = pluginApp(DataBase::class);
        $orderSynced = pluginApp(OrderSynced::class);
        $orderSynced->orderId = $data['orderId'];
        $orderSynced->isSynced  = $data['isSynced'];
        $database->save($orderSynced);

        return $orderSynced;

    }

    public function getOrderSyncedList(): array
    {
        $database = pluginApp(DataBase::class);
        /**
         * @var OrderSynced[] $orderSyncedList
         */
        $orderSyncedList = $database->query(OrderSynced::class)->where('isSynced', '=', FALSE)->get();
        return $orderSyncedList;
    }

}