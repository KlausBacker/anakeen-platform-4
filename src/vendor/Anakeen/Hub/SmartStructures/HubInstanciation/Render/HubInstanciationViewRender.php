<?php


namespace Anakeen\Hub\SmartStructures\HubInstanciation\Render;

use Anakeen\Ui\BarMenu;
use Anakeen\Ui\ItemMenu;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Hubinstanciation as HubInstanciationFields;

class HubInstanciationViewRender extends \Anakeen\Ui\DefaultConfigViewRender
{
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        return $visibilities;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setRowMinLimit(1);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setCollapse("none");

        return $options;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $menu =parent::getMenu($document);

        $item = new ItemMenu("adminconfig", ___("Configuration", "hub"));
        $item->setUrl(sprintf("/hub/admin/%s", $document->name?:$document->id));

        $menu->appendElement($item);
        return $menu;
    }
}
