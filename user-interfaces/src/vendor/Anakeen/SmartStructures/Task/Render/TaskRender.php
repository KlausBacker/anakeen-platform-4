<?php

namespace Anakeen\SmartStructures\Task\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Routes\Ui\CallMenuResponse;
use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\CallableMenu;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\TextRenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Task as TaskFields;

trait TaskRender
{
    public function addCommonOptions(RenderOptions $options, \Anakeen\Core\Internal\SmartElement $document)
    {
        $options->frame(TaskFields::task_fr_route)->setResponsiveColumns(
            [
                ["number" => 3, "minWidth" => "50rem", "grow" => true]
            ]
        );
        $options->frame(TaskFields::task_fr_ident)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => "50rem", "grow" => true]
            ]
        );

        $options->frame(TaskFields::task_fr_schedule)->setResponsiveColumns(
            [
                ["number" => 2, "minWidth" => "80rem", "grow" => true]
            ]
        );
        $options->text(TaskFields::task_humancrontab)->setLabelPosition(TextRenderOptions::nonePosition);
    }

    public function getJsReferences(SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["task"] = $path["Task"]["js"];
        return $js;
    }
}
