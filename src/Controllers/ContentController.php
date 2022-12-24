<?php
namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use LoyalistaIntegration\Contracts\ToDoRepositoryContract;

use LoyalistaIntegration\Contracts\OrderSyncedRepositoryContract;


use Plenty\Plugin\Log\Reportable;

use Plenty\Plugin\Log\Loggable;

/**
 * Class ContentController
 *
 */
class ContentController extends Controller
{
    use Loggable;
    use Reportable;

    /**
     * @param Twig                   $twig
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    public function showSyncOrder(Request $request, Twig $twig, ToDoRepositoryContract $toDoRepo, OrderSyncedRepositoryContract $osr): string
    {
        $orderSyncList = $osr->getOrderSyncedList();
        $this->getLogger(__FUNCTION__)->error('getOrderSyncedList', $orderSyncList);

//        $order = $osr->getOrderSync($request->get('order_id'));
//        $this->getLogger(__FUNCTION__)->error('getOrderSync', $order);
//        echo json_encode($order);
//
//
//        $order = $osr->createOrderSync(['orderId' => $request->get('new_order_id')]);
//        $this->getLogger(__FUNCTION__)->error('getOrderSync2', $order);
//        echo json_encode($order);
//
//        $order = $osr->markSyncedOrder($order->id);
//        $this->getLogger(__FUNCTION__)->error('markSyncedOrder', $order);
//        echo json_encode($order);

       return json_encode($orderSyncList);

        return $twig->render('LoyalistaIntegration::content.todo', $orderSyncList);
    }

    /**
     * @param  \Plenty\Plugin\Http\Request $request
     * @param ToDoRepositoryContract       $toDoRepo
     * @return string
     */
    public function createToDo(Request $request, ToDoRepositoryContract $toDoRepo): string
    {
        $newToDo = $toDoRepo->createTask($request->all());
        $additionalInfo = ['toDoId' => $newToDo->id , 'desc' => $newToDo->taskDescription];
        $this->getLogger('ContentController_createToDo')
           ->setReferenceType('toDoId') // optional
           ->setReferenceValue($newToDo->id ) // optional
            ->info(
                'LoyalistaIntegration::Migration.createToDoInformation',
                [
                    'additionalInfo' => $additionalInfo,
                    'method' => __METHOD__
                ]
       );
        return json_encode($newToDo);
    }

    /**
     * @param int                    $id
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    public function updateToDo(int $id, ToDoRepositoryContract $toDoRepo): string
    {
        $updateToDo = $toDoRepo->updateTask($id);

        $this->getLogger('ContentController_updateToDo')
            ->setReferenceType('toDoId') // optional
            ->setReferenceValue($updateToDo->id ) // optional
            ->info(
                'LoyalistaIntegration::Migration.updateToDoInformation',
                [
                    'additionalInfo' => [],
                    'method' => __METHOD__
                ]
            );

        return json_encode($updateToDo);
    }

    /**
     * @param int                    $id
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    public function deleteToDo(int $id, ToDoRepositoryContract $toDoRepo): string
    {
        $deleteToDo = $toDoRepo->deleteTask($id);
        return json_encode($deleteToDo);
    }
}