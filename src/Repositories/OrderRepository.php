<?php

namespace LoyalistaIntegration\Repositories;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Repositories\Models\PaginatedResult;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderRepository.
 */
class OrderRepository
{
    use Loggable;

    /**
     * OrderRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * Gets orders.
     *
     * @param int   $pageNum
     * @param array $filters
     *
     * @return array
     */
    public function getOrders($pageNum = 1, $filters = 1)
    {
        $orderRepo = pluginApp(OrderRepositoryContract::class);

        if ($orderRepo instanceof OrderRepositoryContract) {
           // $orderRepo->setFilters($filters);
            $paginatedResult = $orderRepo->searchOrders($pageNum, 50, $with = ['addresses', 'relation', 'reference']);
            if ($paginatedResult instanceof PaginatedResult) {
                if ($paginatedResult->getTotalCount() > 0) {
                    return $paginatedResult->getResult();
                }
            }
        }
        return array();
    }
}
