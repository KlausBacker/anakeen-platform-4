<?php


namespace Anakeen\Hub\SmartStructures\HubInstanciation\Render;

use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Hubinstanciation as HubInstanciationFields;

class HubInstanciationEditRender extends \Anakeen\Ui\DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setRowMinDefault(2);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setRowMaxLimit(2);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->disableRowAdd(true);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->disableRowDel(true);
        $options->arrayAttribute(HubInstanciationFields::hub_instance_titles)->setCollapse("none");

        $options->text(HubInstanciationFields::hub_instance_title)->setMaxLength(50);
        $options->text(HubInstanciationFields::hub_instance_language)->setMaxLength(15);
        $options->frame(HubInstanciationFields::hub_security_frame)->setLabelPosition(CommonRenderOptions::nonePosition);

        $options->account(HubInstanciationFields::hub_access_roles)
            ->setDescription(
                "<p>Mandatory Roles to access to main page of this hub instance</p>" .
                "<p>User must have one of these roles describe here to access to user interface</p>"
            );
        $options->account(HubInstanciationFields::hub_super_role)
            ->setDescription(
                "<p>Mandatory Roles to access to all element of the interface. </p>" .
                "<p>All functionnalities are displayed when user has this role</p>"
            );
        return $options;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param \SmartStructure\Mask|null           $mask
     *
     * @return RenderAttributeVisibilities
     * @throws \Anakeen\Ui\Exception
     */
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(HubInstanciationFields::hub_instance_language, RenderAttributeVisibilities::StaticWriteVisibility);
        return $visibilities;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $parent = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("hubRender", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $parent["hubInstanciationRender"] = $path["hubInstanciationRender"]["js"];
        return $parent;
    }
}
