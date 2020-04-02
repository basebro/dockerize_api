<?php
/**
 * BoardController.php
 *
 * Board Controller
 *
 * @category   Controller
 * @package    Api
 * @author     Christian Acevedo
 */

namespace App\Controller;

use App\Entity\Board;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * Class BoardController
 *
 * @Route("/api")
 */
class BoardController extends FOSRestController
{
    /**
     * @Rest\Get("/v1/board.{_format}", name="board_list_all", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets all boards for current logged user."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error has occurred trying to get all user boards."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="string",
     *     description="The board ID"
     * )
     *
     *
     * @SWG\Tag(name="Board")
     */
    public function getAllBoardAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $boards = [];
        $message = "";

        try {
            $code = 200;
            $error = false;

            $userId = $this->getUser()->getId();
            $boards = $em->getRepository("App:Board")->findBy([
                "user" => $userId,
            ]);

            if (is_null($boards)) {
                $boards = [];
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get all Boards - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $boards : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Get("/v1/board/{id}.{_format}", name="board_list", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets board info based on passed ID parameter."
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="The board with the passed ID parameter was not found or doesn't exist."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The board ID"
     * )
     *
     *
     * @SWG\Tag(name="Board")
     */
    public function getBoardAction(Request $request, $id)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $board = [];
        $message = "";

        try {
            $code = 200;
            $error = false;

            $board_id = $id;
            $board = $em->getRepository("App:Board")->find($board_id);

            if (is_null($board)) {
                $code = 500;
                $error = true;
                $message = "The board does not exist";
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to get the current Board - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $board : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Post("/v1/board.{_format}", name="board_add", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=201,
     *     description="Board was added successfully"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error was occurred trying to add new board"
     * )
     *
     * @SWG\Parameter(
     *     name="name",
     *     in="body",
     *     type="string",
     *     description="The board name",
     *     schema={}
     * )
     *
     * @SWG\Tag(name="Board")
     */
    public function addBoardAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $board = [];
        $message = "";

        try {
            $code = 201;
            $error = false;
            $name = $request->request->get("name", null);
            $user = $this->getUser();

            if (!is_null($name)) {
                $board = new Board();
                $board->setName($name);
                $board->setUser($user);

                $em->persist($board);
                $em->flush();

            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to add new board - Error: You must to provide a board name";
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to add new board - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 201 ? $board : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Put("/v1/board/{id}.{_format}", name="board_edit", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="The board was edited successfully."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error has occurred trying to edit the board."
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The board ID"
     * )
     *
     * @SWG\Parameter(
     *     name="name",
     *     in="body",
     *     type="string",
     *     description="The board name",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="Board")
     */
    public function editBoardAction(Request $request, $id)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $board = [];
        $message = "";

        try {
            $code = 200;
            $error = false;
            $name = $request->request->get("name", null);
            $board = $em->getRepository("App:Board")->find($id);

            if (!is_null($name) && !is_null($board)) {
                $board->setName($name);

                $em->persist($board);
                $em->flush();

            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to add new board - Error: You must to provide a board name or the board id does not exist";
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to edit the current board - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $board : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Delete("/v1/board/{id}.{_format}", name="board_remove", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Board was successfully removed"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="An error was occurred trying to remove the board"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="The board ID"
     * )
     *
     * @SWG\Tag(name="Board")
     */
    public function deleteBoardAction(Request $request, $id)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        try {
            $code = 200;
            $error = false;
            $board = $em->getRepository("App:Board")->find($id);

            if (!is_null($board)) {
                $em->remove($board);
                $em->flush();

                $message = "The board was removed successfully!";

            } else {
                $code = 500;
                $error = true;
                $message = "An error has occurred trying to remove the currrent board - Error: The board id does not exist";
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to remove the current board - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }
    public function returnArray($array = []) : string
    {
        return $array;
    }

}