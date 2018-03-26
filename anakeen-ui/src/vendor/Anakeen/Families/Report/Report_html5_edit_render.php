<?php


namespace Dcp\Search\html5;


use Dcp\AttributeIdentifiers\Report as myAttr;
use Dcp\Core\DocManager;

class Report_html5_edit_render extends Search_html5_edit_render
{

    public function getLabel(\Doc $document = null) {
        return "Report Edit";
    }

    public function setCustomClientData(\Doc $document, $data)
    {
        parent::setCustomClientData($document, $data);
        if (!empty($data) && is_array($data)) {
            if (!$document->id) {
                $family = $data["familyName"];
                if ($family) {
                    $document->setValue(myAttr::se_famid, DocManager::getIdFromName($family));
                    $document->setValue(myAttr::se_fam, DocManager::getTitle($family));
                }
                if (!empty($data["memo"])) {
                    $document->setValue(myAttr::se_memo, "yes");
                }
            }
        }
    }

    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"]["file"]
            = __DIR__."/reportHTML5_edit.mustache";
        return $templates;
    }

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame(myAttr::rep_fr_presentation)
            ->setOption("collapse", false)
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->frame(myAttr::se_crit)
            ->setOption("collapse", false)
           ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->frame(myAttr::rep_fr_presentation)->setResponsiveColumns(array([
            "number" => 2,
            "minWidth" => "60rem",
            "grow" => true
        ]));
        return $options;
    }

    public function getVisibilities(\Doc $document)
    {
        $vis= parent::getVisibilities($document);
        $vis->setVisibility(myAttr::rep_style, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(myAttr::rep_coloreven, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(myAttr::rep_colorodd, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(myAttr::rep_colors, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(myAttr::rep_colorhf, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        return $vis;
    }
}