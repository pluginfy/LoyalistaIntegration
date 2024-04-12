<?php
namespace LoyalistaIntegration\EventProcedures;

use LoyalistaIntegration\Helpers\OrderHelper;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Log\Reportable;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

/**
 * Procedures CLass
 */
class LoyalistaProcedures
{
    use Loggable;
    use Reportable;

    /**
     * @param EventProceduresTriggered $event
     * @return void
     */
    public function exportOrder(EventProceduresTriggered $event)
    {
        try {
            $order = $event->getOrder();
            if ($order && $order->typeId == 1)
            {
                $api = pluginApp(LoyalistaApiService::class);
                $api->exportOrder($order, OrderHelper::ORDER_TYPE_NEW);
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger(__FUNCTION__)->error('Error while get order', ['message'=> $e->getMessage() ]);
        }
        finally {}
    }

    /**
     * @param EventProceduresTriggered $event
     * @return void
     */
    public function refundOrder(EventProceduresTriggered $event)
    {
        try {
            $order = $event->getOrder();
            if ($order && $order->typeId != 1)
            {
                $api = pluginApp(LoyalistaApiService::class);
                $api->exportOrder($order, OrderHelper::ORDER_TYPE_REFUND);
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger(__FUNCTION__)->error('Error while refund order', ['message'=> $e->getMessage() ]);
        }
        finally {}
    }
}