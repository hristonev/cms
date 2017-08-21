<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 13.08.17
 * Time: 14:44
 */

namespace AppBundle\Controller\API;


use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

class PersonController extends FOSRestController
{
    /**
     * @Rest\Get("/api/person")
     */
    public function getAction()
    {
        $result = $this->getDoctrine()->getRepository('AppBundle:Person')->findAll();
        if ($result === null) {
            return new View("there are no users exist", Response::HTTP_NOT_FOUND);
        }
        return $result;
    }
}