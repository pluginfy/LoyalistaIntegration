<?php
namespace LoyalistaIntegration\EventProcedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;

class Procedures
{

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
        $order = $event->getOrder();
        $orderRepository = pluginApp(OrderRepositoryContract::class);
        $orderRepository->updateOrder(['statusId' => 3], $order->id);
    }


    public function pushOrder(EventProceduresTriggered $event){
        $order_id = -1;
        try {
            /** @var Order $order */
            $order = $event->getOrder();
            // https://developers.plentymarkets.com/en-gb/developers/main/rest-api-guides/order-data.html
            if ($order)
            {
                $order_id = $order->id;
            }

            // TODO LOG INFO runOrderStatusChangeEvent

            if ( true ) {

                // we believe the ASN was already sent - another will NOT be sent.
                // // TODO LOG INFO already sent Sent
                // $externalLogs->addInfoLog("ASN already sent for order with ID " . $order->id . " so another will NOT be sent.");
            }
            else
             {

                 // TODO LOG INFO


                 //saveorder($order);


                 // TODO LOGE IFNOT finishedShipmentNotification

            }
        }

        catch (\Exception $e)
        {
            // TODO add to external log service

        }
        finally {

            // TODO count to external log service

            // TODO send external log to concern

        }

    }
}