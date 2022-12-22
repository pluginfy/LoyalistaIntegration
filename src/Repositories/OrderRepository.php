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

    private $orderRepo;
    /**
     * OrderRepository constructor.
     */
    public function __construct(OrderRepositoryContract $orderRepo)
    {
        $this->orderRepo = $orderRepo;
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
            $this->orderRepo->setFilters($filters);
            $paginatedResult = $this->orderRepo->searchOrders($pageNum, 50);
            if ($paginatedResult instanceof PaginatedResult) {
                if ($paginatedResult->getTotalCount() > 0) {
                    return $paginatedResult->getResult();
                }
            }

        return array();
    }

    public function getSingleOrder($id)
    {
        return $this->orderRepo->findOrderById($id);
    }
}
