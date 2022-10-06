<?php
namespace LoyalistaIntegration\EventProcedures;

use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Log\Reportable;
use LoyalistaIntegration\Services\API\LoyalistaApiService;

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
        $this->report('order hit loyalista',
            'some code',
            ['orderID' => 12 ],
            ['orDId' => 18 ]);

        try {
            $order = $event->getOrder();
            if ($order && $order->typeId == 1)
            {
                $api = pluginApp(LoyalistaApiService::class);
                $api->createOrder($order);
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger('exportOrder')
                ->error('Error while get order', ['message'=> $e->getMessage() ]);
        }
        finally {
            // Do something to log this information

        }
    }



    public function revertPointLoyalista(EventProceduresTriggered $event)
    {
        try {
            $order = $event->getOrder();

            $this->getLogger('revert point')
                ->error('Hit revert', ['order'=> $order ]);

            if ($order && $order->typeId == 1)
            {
              //  $api = pluginApp(LoyalistaApiService::class);
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger('revert point')
                ->error('Error while get order', ['message'=> $e->getMessage() ]);
        }
        finally {
            // TODO count to external api log service
            $this->report('Pluginfy.com-ABCD12d',
                'some code',
                ['orderID' => 12 ],
                ['orDId' => 18 ]);
        }
    }

}