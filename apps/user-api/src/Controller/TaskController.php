<?php
/**
 * TaskController.php
 *
 * API Controller
 *
 * @category   Controller
 * @package    Api
 * @author     Christian Acevedo
 */

namespace App\Controller;

use App\Entity\Task;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * Class TaskController
 *
 * @Route("/api")
 */
class TaskController extends FOSRestController
{
    // TASK URI's

    /**
     * @Rest\Get("/v1/task.{_format}", name="task_list_all", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets all task for current logged user."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error has occurred trying to get all user tasks."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="string",
     *     description="The task ID"
     * )
     *
     *
     * @SWG\Tag(name="Task")
     */
    public function getAllTaskAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $tasks = [];
        $message = "";

        try {
            $code = 200;
            $error = false;

            $userId = $this->getUser()->getId();
            $tasks = $em->getRepository("App:Task")->findAll();

            if (is_null($tasks)) {
                $tasks = [];
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Tasks - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $tasks : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Post("/v1/task.{_format}", name="task_add", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=201,
     *     description="Task was added successfully"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error was occurred trying to add new task"
     * )
     *
     * @SWG\Parameter(
     *     name="title",
     *     in="body",
     *     type="string",
     *     description="The task title",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="description",
     *     in="body",
     *     type="string",
     *     description="The task description",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="status",
     *     in="body",
     *     type="string",
     *     description="The task status. Allowed values: Backlog, Working, Done",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="priority",
     *     in="body",
     *     type="string",
     *     description="The task priority. Allowed values: High, Medium, Low",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="board_id",
     *     in="body",
     *     type="string",
     *     description="The board id of the new task",
     *     schema={}
     * )
     *
     * @SWG\Tag(name="Task")
     */
    public function addTaskAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $task = [];
        $message = "";

        try {
            $code = 201;
            $error = false;
            $title = $request->request->get("title", null);
            $description = $request->request->get("description", null);
            $status = $request->request->get("status", null);
            $priority = $request->request->get("priority", null);
            $boardId = $request->request->get("board_id", null);

            if (!is_null($title) && !is_null($description) && !is_null($status) && !is_null($priority) && !is_null($boardId)) {
                $task = new Task();
                $board = $em->getRepository("App:Board")->find($boardId);
                $task->setBoard($board);
                $task->setTitle($title);
                $task->setDescription($description);
                $task->setStatus($status);
                $task->setPriority($priority);

                $em->persist($task);
                $em->flush();

            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to add new task - Error: You must to provide all the required fields";
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to add new task - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 201 ? $task : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Put("/v1/task/{id}.{_format}", name="task_edit", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="The task was edited successfully."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error has occurred trying to edit the task."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The task ID"
     * )
     *
     * @SWG\Parameter(
     *     name="title",
     *     in="body",
     *     type="string",
     *     description="The task title",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="description",
     *     in="body",
     *     type="string",
     *     description="The task description",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="status",
     *     in="body",
     *     type="string",
     *     description="The task status. Allowed values: Backlog, Working, Done",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="priority",
     *     in="body",
     *     type="string",
     *     description="The task priority. Allowed values: High, Medium, Low",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="Task")
     */
    public function editTaskAction(Request $request, $id)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $task = [];
        $message = "";

        try {
            $code = 200;
            $error = false;
            $title = $request->request->get("title", null);
            $description = $request->request->get("description", null);
            $status = $request->request->get("status", null);
            $priority = $request->request->get("priority", null);
            $task = $em->getRepository("App:Task")->find($id);

            if (!is_null($task)) {
                if (!is_null($title)) {
                    $task->setTitle($title);
                }

                if (!is_null($description)) {
                    $task->setDescription($description);
                }

                if (!is_null($status)) {
                    $task->setStatus($status);
                }

                if (!is_null($priority)) {
                    $task->setPriority($priority);
                }

                $em->persist($task);
                $em->flush();

            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to edit the current task - Error: The task id does not exist";
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to edit the current task - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $task : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Delete("/v1/task/{id}.{_format}", name="task_remove", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Task was successfully removed"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="An error was occurred trying to remove the task"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The board ID"
     * )
     *
     * @SWG\Tag(name="Task")
     */
    public function deleteTaskAction(Request $request, $id)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        try {
            $code = 200;
            $error = false;
            $task = $em->getRepository("App:Task")->find($id);

            if (!is_null($task)) {
                $em->remove($task);
                $em->flush();

                $message = "The task was removed successfully!";

            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to remove the currrent task - Error: The task id does not exist";
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to remove the current task - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }


}