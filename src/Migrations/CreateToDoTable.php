<?php
namespace LoyalistaIntegration\Migrations;

use LoyalistaIntegration\Models\ToDo;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

class CreateToDoTable
{
    /**
     * @param Migrate $migrate
     */
    public function run(Migrate $migrate)
    {
        $migrate->createTable(ToDo::class);
        //$migrate->createTable(ToDo::class);
    }
}