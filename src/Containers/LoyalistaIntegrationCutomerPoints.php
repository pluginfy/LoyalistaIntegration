<?php

namespace LoyalistaIntegration\Containers;

use Plenty\Plugin\Templates\Twig;
use LoyalistaIntegration\Helper\ConfigHelper;

/**
 * Ekomi Feedback Reviews Container.
 */
class LoyalistaIntegrationCutomerPoints
{
    /**
     * Renders HTML content for newly created tab on the product page.
     *
     * @param Twig  $twig
     * @param array $arg
     *
     * @return string
     */
    public function call(Twig $twig, $arg)
    {
        $configHelper = pluginApp(ConfigHelper::class);
        $data = array(
            'customerId' => 111,
        );

        return $twig->render('LoyalistaIntegration::content.pointsWidget', $data);
    }
}
