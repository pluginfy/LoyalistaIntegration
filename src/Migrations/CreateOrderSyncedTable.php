<?php

namespace LoyalistaIntegration\Migrations;

use LoyalistaIntegration\Models\OrderSynced;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;


class CreateOrderSyncedTable
{
    /**
     * @param Migrate $migrate
     */
    public function run(Migrate $migrate)
    {
        $migrate->createTable(OrderSynced::class);
    }

}