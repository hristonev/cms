<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LinkedInController extends Controller
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/linkedin", name="connect_linkedin")
     */
    public function connectAction()
    {
        // will redirect to LinkedIn!
        $scopes = ['r_basicprofile', 'r_emailaddress'];
        return $this->get('oauth2.registry')
            ->getClient('linkedin_main') // key used in config.yml
            ->redirect($scopes);
    }

    /**
     * After going to LinkedIn, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config.yml
     *
     * @Route("/connect/linkedin/check", name="connect_linkedin_check")
     */
    public function connectCheckAction(Request $request)
    {
        return $this->redirectToRoute('homepage');
    }
}