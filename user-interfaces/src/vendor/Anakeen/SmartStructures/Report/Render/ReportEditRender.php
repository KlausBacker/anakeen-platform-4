<?php


namespace Anakeen\SmartStructures\Report\Render;

use Anakeen\SmartStructures\Dsearch\Render\SearchEditRender;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use SmartStructure\Fields\Report as myAttr;
use Anakeen\Core\SEManager;

class ReportEditRender extends SearchEditRender
{

    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return "Report Edit";
    }

    public function setCustomClientData(\Anakeen\Core\Internal\SmartElement $document, $data)
    {
        parent::setCustomClientData($document, $data);
        if (!empty($data) && is_array($data)) {
            if (!$document->id) {
                $family = $data["familyName"];
                if ($family) {
                    $document->setValue(myAttr::se_famid, SEManager::getIdFromName($family));
                }
            }
        }
    }

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__ . "/reportHTML5_edit.mustache";
        return $templates;
    }

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->frame(myAttr::rep_fr_presentation)
            ->setOption("collapse", false)
            ->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);
        $options->frame(myAttr::se_crit)
            ->setOption("collapse", false)
            ->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::nonePosition);
        $options->frame(myAttr::rep_fr_presentation)->setResponsiveColumns(array(
            [
                "number" => 2,
                "minWidth" => "60rem",
                "grow" => true
            ]
        ));
        $options->int(myAttr::rep_limit)->setInputTooltip(\Anakeen\Core\Utils\Strings::xmlEncode(
            ___("Number of elements to display per page", "smart report")
        ));
        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $vis= parent::getVisibilities($document, $mask);
        $vis->setVisibility(myAttr::rep_displayoption, RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(myAttr::rep_colors, RenderAttributeVisibilities::HiddenVisibility);
        return $vis;
    }
}
