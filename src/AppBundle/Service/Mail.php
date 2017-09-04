<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 24.08.17
 * Time: 10:44
 */

namespace AppBundle\Service;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Mail
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function registration($recipient, $name, $password)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->container->get('translator')->trans('register_via_email', [], 'email'))
            ->setFrom($this->container->getParameter('mailer_system_email'))
            ->setTo($recipient)
            ->setBody(
                $this->container->get('templating')->render(
                    'emails/registration_standart.html.twig',
                    [
                        'name' => $name,
                        'password' => $password
                    ]
                ),
                'text/html'
            );
        $this->container->get('mailer')->send($message);
    }
}