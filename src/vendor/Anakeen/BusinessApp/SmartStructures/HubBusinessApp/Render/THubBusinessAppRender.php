<?php
namespace Anakeen\BusinessApp\SmartStructures\HubBusinessApp\Render;


use Anakeen\Ui\CreateDocumentOptions;
use Anakeen\Ui\EnumRenderOptions;
use Anakeen\Ui\FrameRenderOptions;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Hubbusinessapp as HubBusinessAppFields;
trait THubBusinessAppRender
{
    protected function getCommonOptions(RenderOptions $options) {
        $createOptions = new CreateDocumentOptions("DSEARCH");
        $options->docid(HubBusinessAppFields::hba_collection)->addCreateDocumentButton($createOptions);
        $options->frame(HubBusinessAppFields::hba_options)->setCollapse(FrameRenderOptions::collapseCollapsed);
        $options->enum(HubBusinessAppFields::hba_welcome_option)->setDisplay(EnumRenderOptions::boolDisplay);
        $options->docid(HubBusinessAppFields::hba_grid_collection)->addCreateDocumentButton($createOptions);
        return $options;
    }
}