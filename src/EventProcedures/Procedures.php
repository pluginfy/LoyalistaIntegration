<?php
namespace LoyalistaIntegration\EventProcedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Log\Reportable;
use LoyalistaIntegration\Services\API\LoyalistaApiService;




class Procedures
{
    use Loggable;
    use Reportable;

    // Event Handler
    // https://developers.plentymarkets.com/en-gb/developers/main/how-to-event-procedures.html

    // Custom Table
    // https://developers.plentymarkets.com/en-gb/developers/main/data-storage/how-to-store-data.html


    /**
     * @param EventProceduresTriggered $event
     * @return void
     */
    public function setStatus(EventProceduresTriggered $event)
    {
        try {
            // https://developers.plentymarkets.com/en-gb/developers/main/rest-api-guides/order-data.html
            $order = $event->getOrder();
            if ($order)
            {
                $api = pluginApp(LoyalistaApiService::class);
                $api->createOrder($order);
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger('SetStatus')
                ->error('Error while get order', ['message'=> $e->getMessage() ]);

            // TODO add to external log api service

        }
        finally {
            // TODO count to external apli log service


        }

    }



}