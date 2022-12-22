<?php

namespace LoyalistaIntegration\Services;

use LoyalistaIntegration\Helpers\ConfigHelper;
use LoyalistaIntegration\Repositories\OrderRepository;
use LoyalistaIntegration\Services\API\LoyalistaApiService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;

/**
 * Class EkomiServices.
 */
class ExportServices
{
    use Loggable;

    /**
     * Static values.
     */
    const PAGE_NUMBER = 1;

    /**
     * @var ConfigRepository
     */
    private $configHelper;
    private $orderRepository;
    private $api;

    /**
     * EkomiServices constructor.
     *
     * @param ConfigHelper    $configHelper
     * @param OrderRepository $orderRepo
     */
    public function __construct(ConfigHelper $configHelper, OrderRepository $orderRepo, LoyalistaApiService $api)
    {
        $this->configHelper = $configHelper;
        $this->orderRepository = $orderRepo;
        $this->api = $api;
    }

    public function exportPreviousOrders()
    {
        $response = [];

        $shopId = $this->configHelper->getShopID();
        $orderIds = $this->configHelper->getOrderIds();
        if(!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $order = $this->orderRepository->getSingleOrder($orderId);
                $this->api->exportOrder($order);
                $response[$order->id] = [
                    'typeId' => $order->typeId,
                    'statusId' => $order->statusId,
                    'plentyId' => $order->plentyId,
                ];
            }
        } else {
            $dateFrom = $this->configHelper->getVar('date_from');
            $orderTypes = $this->configHelper->getOrderTypes();
            $orderStatuses = $this->configHelper->getOrderStatuses();

            $filters = $this->prepareFilter($dateFrom);

            $pageNum = self::PAGE_NUMBER;
            $fetchOrders = true;

            while ($fetchOrders) {
                $orders = $this->orderRepository->getOrders($pageNum, $filters);
                $this->getLogger('ExportServices')->error('exportPreviousOrders-page-' . $pageNum, 'count:' . count($orders));
                if ($orders && count($orders) > ConfigHelper::VALUE_NO) {
                    foreach ($orders as $key => $order) {
                        $this->exportOrder($order, $orderTypes, $orderStatuses, $shopId);
                        $response[$order['id']] = [
                            'typeId' => $order['typeId'],
                            'statusId' => $order['statusId'],
                            'plentyId' => $order['plentyId'],
                        ];
                    }
                } else {
                    $fetchOrders = false;
                }

                $pageNum = $pageNum + 1;
            }
        }

        return $response;
    }


    /**
     * Exports order data.
     *
     * @param array  $order
     * @param array  $orderStatuses
     * @param string $referrerIds
     * @param array  $plentyIDs
     */
    public function exportOrder($order, $orderTypes, $orderStatuses, $shopId)
    {
        $orderId = $order['id'];
        $plentyID = $order['plentyId'];
        if (!in_array($order['statusId'], $orderStatuses)) {
            $this->getLogger('ExportServices')->error('exportOrder-staus', ['order' => $order['statusId'], 'config'=> $orderStatuses]);

            return false;
        }

        if (!in_array($order['typeId'], $orderTypes)) {
            $this->getLogger('ExportServices')->error('exportOrder-type', ['order' => $order['typeId'], 'config'=> $orderTypes]);

            return false;
        }

        $this->api->exportOrder($this->orderRepository->getSingleOrder($orderId));
    }

    /**
     * Prepares filter to be applied in fetching orders.
     *
     * @param int $turnaroundTime
     *
     * @return array
     */
    public function prepareFilter($dateFrom)
    {
        $updatedAtFrom = date('Y-m-d\TH:i:s+00:00', strtotime(date($dateFrom)));
        $updatedAtTo = date('Y-m-d\TH:i:s+00:00');

        return ['updatedAtFrom' => $updatedAtFrom, 'updatedAtTo' => $updatedAtTo];
    }
}
