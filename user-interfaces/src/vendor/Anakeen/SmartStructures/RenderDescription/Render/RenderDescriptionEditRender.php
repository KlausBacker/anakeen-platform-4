<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\RenderDescription\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\EnumRenderOptions;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Renderdescription as DescriptionFields;

class RenderDescriptionEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $template
            = <<< 'HTML'
<table class="dcpArray__table rd-custom-fields">
  <thead>
    <tr>
      {{#attribute.toolsEnabled}}
      <th></th>
      {{/attribute.toolsEnabled}}
      <th></th>
    </tr>
  </thead>
  <tbody>
    {{#attribute.rows}}
    <tr>
      {{#attribute.toolsEnabled}}
      <td>{{{rowTools}}}</td>
      {{/attribute.toolsEnabled}}
      <td>
        <div class="rd-field-description">
            <div class="rd-vertical">
            <button type="button" class="btn btn-default"><i class="fa fa-angle-double-up"/></button>
                <div class="rd-description rd-field-label">Hello world</div>
            </div>
            <div class="rd-field-content">
                <div class="rd-description-header">
                   <div class="rd-description-labels">
                      <div class="rd-description rd-description-parentlabel">{{{content.rd_fieldparentlabel.htmlContent}}}</div>
                      <div class="rd-description rd-description-label">{{{content.rd_fieldlabel.htmlContent}}}</div>
                  </div>
                  <div class="rd-description"> {{content.rd_placement.label}}:  {{{content.rd_placement.htmlContent}}}</div>
                </div>
               
                <div class="rd-descriptions">
                    <div class="rd-description">
                        <b>{{content.rd_description.label}}</b>
                        {{{content.rd_description.htmlContent}}}</div>
                    <div class="rd-description">
                        <b>{{content.rd_subdescription.label}}</b>
                        {{{content.rd_subdescription.htmlContent}}}
                        {{{content.rd_collapsable.htmlContent}}}
                    </div>
                </div>
            </div>
        </div>
      </td>
    </tr>
    {{/attribute.rows}}
  </tbody>
</table>
<div>{{{attribute.tableTools}}}</div>

HTML;
        $options->arrayAttribute(DescriptionFields::rd_t_fields)->setTemplate($template);


        $options->enum(DescriptionFields::rd_collapsable)->setDisplay(EnumRenderOptions::boolDisplay);
        $options->htmltext(DescriptionFields::rd_description)->setHeight("8.6rem");
        $options->htmltext(DescriptionFields::rd_subdescription)->setHeight("6rem");
        $options->text(DescriptionFields::rd_fieldlabel)->setPlaceHolder(___("Choose field to describe", "renderdescription"));

        $buttonConfig = new \Anakeen\Ui\ButtonOptions();
        $buttonConfig->url = "#action/document.load:{{value}}";
        $buttonConfig->title = "Display example";
        $buttonConfig->htmlContent = '<i class="fa fa-eye rd-example"/>';
        $buttonConfig->target = "_blank";
        $buttonConfig->class="rd-example";
        $options->docid(DescriptionFields::rd_example)->addButton($buttonConfig);

        return $options;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["renderDescriptionEdit"] = $path["RenderDescriptionEdit"]["js"];

        return $js;
    }

    public function getVisibilities(
        \Anakeen\Core\Internal\SmartElement $document,
        \SmartStructure\Mask $mask = null
    ): RenderAttributeVisibilities {
        $visibilities = parent::getVisibilities($document, $mask);
        $visibilities->setVisibility(
            DescriptionFields::rd_field,
            RenderAttributeVisibilities::HiddenVisibility
        );
        $visibilities->setVisibility(
            DescriptionFields::rd_fieldparentlabel,
            RenderAttributeVisibilities::StaticWriteVisibility
        );
        return $visibilities;
    }
}
