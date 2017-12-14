<?php
namespace Sample\BusinessApp\Renders;
use Dcp\Ui\ItemMenu;


class FeesView extends CommonView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->frame()->showEmptyContent("<div>Aucunes informations</div>");
        $options->date('fee_period')->setKendoDateConfiguration(
            array(
                "format" => "MMMM yyyy"
            )
        );
        $options->arrayAttribute()->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $options->htmltext()->setToolbar(\dcp\Ui\HtmltextRenderOptions::basicToolbar);
        $options->frame('fee_fr_viz')->setLabelPosition(\Dcp\ui\CommonRenderOptions::nonePosition);
        $imgFiles = $document->getMultipleRawValues('fee_exp_file');
        if ($imgFiles) {
            $options->frame('fee_fr_viz')->setTemplate(file_get_contents(__DIR__."/feesVisualization.mustache"),
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
