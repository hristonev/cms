<?php

namespace AppBundle\EventListener;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutListener implements LogoutHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {

        $this->container = $container;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $request->getSession()
            ->getFlashBag()
            ->add(
                'success',
                $this->container
                    ->get('translator')
                    ->trans('application.logout_success')
            );
    }
}