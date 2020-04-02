<?php
/**
 * ApiController.php
 *
 * API Controller
 *
 * @category   Controller
 * @package    Api
 * @author     Christian Acevedo
 */

namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ApiController
 *
 * @Route("/api")
 */
class ApiController extends FOSRestController
{
    /**
     * @Route("/v1/", name="api")
     */
    public function api()
    {
        return new Response(sprintf('Logged in as %s', $this->getUser()->getUsername()));
    }

}