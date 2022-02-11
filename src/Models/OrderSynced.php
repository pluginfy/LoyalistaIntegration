<?php
namespace LoyalistaIntegration\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class OrderSynced
 *
 * @property int     $id
 * @property int     $orderId
 * @property boolean $isSynced

 */

class OrderSynced extends Model
{
    /**
     * @var int
     */
    public $id              = 0;
    public $orderId         = 0;
    public $isSynced        = false;

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'LoyalistaIntegration::OrderSynced';
    }
}