<?php

namespace LoyalistaIntegration\Widgets;
use Ceres\Widgets\Helper\BaseWidget;


class MyAccountWidget extends BaseWidget
{
    protected $template = "LoyalistaIntegration::Widgets.MyAccountWidget";

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