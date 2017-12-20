<?php
namespace Sample\BusinessApp\Renders;
use Dcp\AttributeIdentifiers\Ba_fees as FeesAttr;
use Dcp\Ui\ItemMenu;


class FeesView extends CommonView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->showEmptyContent("<div>Aucunes informations</div>");
        $options->date(FeesAttr::fee_period)->setKendoDateConfiguration(
            array(
                "format" => "MMMM yyyy"
            )
        );
        $options->arrayAttribute()->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);
        $options->frame(FeesAttr::fee_fr_viz)->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $options->arrayAttribute(FeesAttr::fee_t_all_exp)->setTemplate(file_get_contents(__DIR__.'/feesArray.mustache'));
        $options->image(FeesAttr::fee_exp_file)->setThumbnailSize('50x50c');

        $imgFiles = $document->getMultipleRawValues(FeesAttr::fee_exp_file);
        if ($imgFiles) {
            $options->frame(FeesAttr::fee_fr_viz)->setTemplate(file_get_contents(__DIR__."/feesVisualization.mustache"),
                array(
                    "expensesLabel"=>$this->getExpensesDisplayTitles(count($imgFiles)),
                ));
        }
        return $options;
    }

    public function getMenu(\Doc $document)
    {
        $menu = parent::getMenu($document);
        $item = new ItemMenu('fee_preview', 'Prévisualiser la note de frais');
        $item->setBeforeContent('<i class="fa fa-eye"></i>');
        $item->setUrl("#action/preview");
        $menu->insertAfter('modify', $item);
        return $menu;
    }

    public function getCssReferences(\Doc $document = null)
    {

        $css = parent::getCssReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css[__CLASS__] = "BUSINESS_APP/Families/Fees/Renders/fees.css"."?ws=$ws";
        $css['feesMap'] = "https://unpkg.com/leaflet@1.2.0/dist/leaflet.css";
        return $css;
    }

    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences($document);
        $ws=\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js[__CLASS__] = "BUSINESS_APP/Families/Fees/Renders/fees.js"."?ws=$ws";
        $js['feesMap'] = "https://unpkg.com/leaflet@1.2.0/dist/leaflet.js";
        $js['feesLoadMap'] = "BUSINESS_APP/Families/Fees/Renders/feesMap.js"."?ws=$ws";
        return $js;
    }

    protected function getExpensesDisplayTitles($expensesLength) {
        $titles = [];
        for ($i = 1; $i <= $expensesLength; $i++) {
            $titles[] = array("displayTitle" => "Dépense $i");
        }
        return $titles;
    }
}
