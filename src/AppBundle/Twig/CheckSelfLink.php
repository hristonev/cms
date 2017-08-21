<?php

namespace AppBundle\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

class CheckSelfLink extends \Twig_Extension
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('check_self_link', [$this, 'checkLink'])
        ];
    }

    public function checkLink($path)
    {
        if($this->router->getContext()->getPathInfo() == $path){
            $path = '#';
        }

        return $path;
    }

    public function getName()
    {
        return 'check_self_link';
    }
}