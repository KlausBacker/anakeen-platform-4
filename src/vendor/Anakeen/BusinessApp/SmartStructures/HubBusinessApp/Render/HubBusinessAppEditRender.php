<?php

namespace Anakeen\BusinessApp\SmartStructures\HubBusinessApp\Render;

use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubbusinessapp as HubBusinessAppFields;

class HubBusinessAppEditRender extends \Anakeen\Hub\SmartStructures\HubConfigurationVue\Render\HubConfigurationVueEditRender
{
    use THubBusinessAppRender;


    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): \Anakeen\Ui\RenderOptions
    {
        $options = $this->getCommonOptions(parent::getOptions($document));
        $options->arrayAttribute(HubBusinessAppFields::hba_titles)->disableRowAdd(true);
        $options->arrayAttribute(HubBusinessAppFields::hba_titles)->disableRowDel(true);
        $options->arrayAttribute(HubBusinessAppFields::hba_titles)->setCollapse("none");

        $options->htmltext(HubBusinessAppFields::hba_icon_lib)->setTemplate(
            "<select class='icon-selector' data-attrid='hba_icon_lib'>".$this->getOptionsTemplate()."</select>"
        );
        return $options;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences($document);
        return $this->getCommonJSReferences($js);
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubBusinessAppFields::hba_icon, RenderAttributeVisibilities::HiddenVisibility);
        $visibilities->setVisibility(HubBusinessAppFields::hba_language, RenderAttributeVisibilities::StaticWriteVisibility);
        return $visibilities;
    }

    protected function getOptionsTemplate()
    {
        $cssIconsRules = file_get_contents(PUBLIC_DIR.UIGetAssetPath::getCssBootstrap());
        $matches = [];
        $result = preg_match_all("/.fa-([a-z\-]+):before/", $cssIconsRules, $matches);
        if ($result) {
            sort($matches[1]);
            return implode("", array_map(function ($item) {
                return "<option value='$item'>$item</option>";
            }, $matches[1]));
        } else {
            return "";
        }
    }
}
