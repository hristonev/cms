<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    /**
     * @Route("/data")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $headers = json_encode($request->headers->all());
        $data = json_encode($request->request->all());
        $param = $this->getParameter('api_config');
        if(array_key_exists('football_data_org', $param) && array_key_exists('event_save', $param['football_data_org'])){
            file_put_contents(
                $param['football_data_org']['event_save']. 'event_'. time(),
                $headers. PHP_EOL. PHP_EOL. $data
            );
        }

        $response = new JsonResponse();
        $response->setStatusCode(201);

        return $response;
    }
}


function asd($asd,$qwe,$zxc){

}