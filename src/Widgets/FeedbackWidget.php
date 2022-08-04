<?php

namespace LoyalistaIntegration\Widgets;

use Ceres\Widgets\Helper\BaseWidget;

class FeedbackWidget extends BaseWidget
{
    protected $template = "LoyalistaIntegration::Widgets.FeedbackWidget";

    protected function getTemplateData($widgetSettings, $isPreview)
    {
        return [
            "widgetData" => [
                'title' => $widgetSettings["title"]["mobile"],
                'description' => $widgetSettings["description"]["mobile"],
                'show_as' => $widgetSettings["show_as"]["mobile"],
                'tick_box_options' => $widgetSettings["tick_box_options"]["mobile"],
                'comment_placeholder' => $widgetSettings["comment_placeholder"]["mobile"],
            ],
        ];
    }
}
