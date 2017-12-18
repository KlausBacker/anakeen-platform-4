<?php


namespace Dcp\Search\html5;


use Dcp\AttributeIdentifiers\Report;
use Dcp\HttpApi\V1\DocManager\DocManager;

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
                    $document->setValue(Report::se_famid, DocManager::getIdFromName($family));
                    $document->setValue(Report::se_fam, DocManager::getTitle($family));
                }
                if (!empty($data["memo"])) {
                    $document->setValue(Report::se_memo, "yes");
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

        $options->frame(Report::rep_fr_presentation)
            ->setOption("collapse", false)
            ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);
        $options->frame(Report::se_crit)
            ->setOption("collapse", false)
           ->setLabelPosition(\Dcp\Ui\CommonRenderOptions::nonePosition);

        return $options;
    }

    public function getVisibilities(\Doc $document)
    {
        $vis= parent::getVisibilities($document);
        $vis->setVisibility(Report::rep_style, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(Report::rep_coloreven, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(Report::rep_colorodd, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(Report::rep_colors, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        $vis->setVisibility(Report::rep_colorhf, \Dcp\Ui\RenderAttributeVisibilities::HiddenVisibility);
        return $vis;
    }


}