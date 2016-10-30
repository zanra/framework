<?php

namespace Controller;

use Zanra\Framework\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('Default/hello.html.twig', array('name' => $name));
    }

    public function helpAction()
    {
        return $this->render('Default/help.html.twig');
    }
}
