<?php
namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Twig;
use LoyalistaIntegration\Contracts\ToDoRepositoryContract;

/**
 * Class ContentController
 * @package ToDoList\Controllers
 */
class ContentController extends Controller
{
    /**
     * @param Twig                   $twig
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    public function showToDo(Twig $twig, ToDoRepositoryContract $toDoRepo): string
    {
        $toDoList = $toDoRepo->getToDoList();
        $templateData = array("tasks" => $toDoList);
        return $twig->render('LoyalistaIntegration::content.todo', $templateData);
    }

    /**
     * @param  \Plenty\Plugin\Http\Request $request
     * @param ToDoRepositoryContract       $toDoRepo
     * @return string
     */
    public function createToDo(Request $request, ToDoRepositoryContract $toDoRepo): string
    {

        //$newToDo = $toDoRepo->createTask($request->all());
        return json_encode('asdfasdfasdf');
    }

    /**
     * @param int                    $id
     * @param ToDoRepositoryContract $toDoRepo
     * @return string
     */
    public function updateToDo(int $id, ToDoRepositoryContract $toDoRepo): string
    {
        $updateToDo = $toDoRepo->updateTask($id);
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