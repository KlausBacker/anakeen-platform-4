<?php


namespace Anakeen\SmartStructures\Report\Render;

use Anakeen\SmartStructures\Dsearch\Render\SearchEditRender;
use Dcp\Ui\RenderOptions;
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
                    $document->setValue(myAttr::se_fam, SEManager::getTitle($family));
                }
                if (!empty($data["memo"])) {
                    $document->setValue(myAttr::se_memo, "yes");
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
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->frame(myAttr::se_crit)
            ->setOption("collapse", false)
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->frame(myAttr::rep_fr_presentation)->setResponsiveColumns(array(
            [
                "number" => 2,
                "minWidth" => "60rem",
                "grow" => true
            ]
        ));
        $options->int(myAttr::rep_limit)->setInputTooltip(xml_entity_encode(
            ___("Number maximum of displayed elements", "smart report")
        ));
        return $options;
    }
}