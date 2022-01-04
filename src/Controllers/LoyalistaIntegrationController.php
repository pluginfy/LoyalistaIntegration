<?php

namespace LoyalistaIntegration\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;

class LoyalistaIntegrationController extends Controller
{
    /**
     * @param Twig $twig
     * @return string
     */
    public function getHelloWorldPage(Twig $twig)
    {
        return $twig->render('LoyalistaIntegration::content.hello');
    }
}