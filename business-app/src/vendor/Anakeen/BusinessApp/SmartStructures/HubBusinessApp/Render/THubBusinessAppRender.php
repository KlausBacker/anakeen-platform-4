<?php
namespace Anakeen\BusinessApp\SmartStructures\HubBusinessApp\Render;

use Anakeen\Ui\CreateDocumentOptions;
use Anakeen\Ui\EnumRenderOptions;
use Anakeen\Ui\FrameRenderOptions;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubbusinessapp as HubBusinessAppFields;

trait THubBusinessAppRender
{
    protected function getCommonOptions(RenderOptions $options)
    {
        $createOptions = new CreateDocumentOptions("REPORT");
        $createOptions->windowHeight = "100%";
        $createOptions->windowWidth = "100%";
        $options->docid(HubBusinessAppFields::hba_collection)->addCreateDocumentButton($createOptions);
        $options->frame(HubBusinessAppFields::hba_options)->setCollapse(FrameRenderOptions::collapseCollapsed);
        $options->enum(HubBusinessAppFields::hba_welcome_option)->setDisplay(EnumRenderOptions::boolDisplay);
        $options->docid(HubBusinessAppFields::hba_grid_collection)->addCreateDocumentButton($createOptions);

        $options->arrayAttribute(HubBusinessAppFields::hba_collections)->setDescription(file_get_contents(__DIR__."/../Layout/welcomeTabCollectionsDescription.html"));

        $options->enum(HubBusinessAppFields::hba_welcome_option)->setDescription(file_get_contents(__DIR__."/../Layout/welcomeTabDescription.html"));
        $options->arrayAttribute(HubBusinessAppFields::hba_structure_creation)->setDescription("
            Defines which Smart Structure can be instantiated in the <div class=\"welcome_tab_legend\">1</div> area
        ");
        $options->arrayAttribute(HubBusinessAppFields::hba_grid_collections)->setDescription("
            Defines which Report is displayed in the <div class=\"welcome_tab_legend\">2</div> area
        ");
        return $options;
    }

    protected function getCommonJSReferences($js)
    {
        $js["businessApp"] = UIGetAssetPath::getElementAssets(
            "businessAppRender",
            UIGetAssetPath::isInDebug() ? "dev" : "prod"
        )["businessApp"]["js"];
        return $js;
    }
}
