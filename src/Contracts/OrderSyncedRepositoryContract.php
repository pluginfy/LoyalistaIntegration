<?php
namespace LoyalistaIntegration\Contracts;

use LoyalistaIntegration\Models\OrderSynced;

/**
 * Class ToDoRepositoryContract
 * @package ToDoList\Contracts
 */
interface OrderSyncedRepositoryContract
{


    /**
     * Create Record
     * @param array $data
     * @return OrderSynced
     */
    public function createOrderSync(array $data): OrderSynced;


    /**
     * Get Record List
     * @return array
     */
    public function markSyncedOrder($id): OrderSynced;


    public function getOrderSyncedList(): array;
    public function getOrderSync($orderId);


}