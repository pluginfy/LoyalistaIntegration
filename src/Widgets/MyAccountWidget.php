<?php

namespace LoyalistaIntegration\Widgets;
use Ceres\Widgets\Helper\BaseWidget;

/**
 * My Account WI=idget Class
 */
class MyAccountWidget extends BaseWidget
{
    protected $template = "LoyalistaIntegration::Widgets.MyAccountWidget";

    /**
     * @param $widgetSettings
     * @param $isPreview
     * @return array[]
     */
    protected function getTemplateData($widgetSettings, $isPreview)
    {
        return [
            "widgetData" => [
                'title' => $widgetSettings["title"]["mobile"],
                'show_as' => $widgetSettings["show_as"]["mobile"]
            ],
        ];
    }
}