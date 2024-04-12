<?php
namespace LoyalistaIntegration\EventProcedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;

/**
 * Filters Class
 */
class Filters
{
    /**
     * @param EventProceduresTriggered $event
     * @return boolean
     */
    public function orderLocked(EventProceduresTriggered $event)
    {
        return $event->getOrder()->lockStatus != 'unlocked';
    }
}