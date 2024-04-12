<?php
namespace LoyalistaIntegration\Repositories;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;
use LoyalistaIntegration\Models\OrderSynced;

/**
 * Order Synced Repository Class
 */
class OrderSyncedRepository implements OrderSyncedRepositoryContract
{
    /**
     * @param array $data
     * @return OrderSynced
     */
    public function createOrderSync(array $data): OrderSynced
    {
        /**
         * @var DataBase $database
         */
        $database = pluginApp(DataBase::class);
        $orderSynced = pluginApp(OrderSynced::class);
        $orderSynced->orderId = $data['orderId'];
        $orderSynced->isSynced  = false;
        $database->save($orderSynced);

        return $orderSynced;

    }

    /**
     * @param $id
     * @return OrderSynced
     */
    public function markSyncedOrder($id): OrderSynced
    {
        $database = pluginApp(DataBase::class);

        $orderSynced = $database->query(OrderSynced::class)->where('id', '=', $id)->get();
        $orderSynced = $orderSynced[0];
        $orderSynced->isSynced = true;
        $database->save($orderSynced);

        return $orderSynced;
    }

    /**
     * @return OrderSynced[]
     */
    public function getOrderSyncedList(): array
    {
        $database = pluginApp(DataBase::class);
        /**
         * @var OrderSynced[] $orderSyncedList
         */
        $orderSyncedList = $database->query(OrderSynced::class)->orderBy('id','DESC')->get();

        return $orderSyncedList;
    }

    /**
     * @param $orderId
     * @return false
     */
    public function getOrderSync($orderId)
    {
        $database = pluginApp(DataBase::class);
        $order = $database->query(OrderSynced::class)->where('orderId', '=', $orderId)->get();

        if(isset($order[0])) {
            return $order[0];
        }

        return false;
    }
}