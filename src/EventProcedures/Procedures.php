<?php
namespace LoyalistaIntegration\EventProcedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Log\Reportable;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;

class Procedures
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
            // https://developers.plentymarkets.com/en-gb/developers/main/rest-api-guides/order-data.html
            $order = $event->getOrder();
            if ($order && $order->typeId == 1)
            {
                $api = pluginApp(LoyalistaApiService::class);
                $api->createOrder($order);
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger('exportOrder')->error('Error while get order', ['message'=> $e->getMessage() ]);
        }
        finally {
            // TODO count to external apli log service
        }
    }
}