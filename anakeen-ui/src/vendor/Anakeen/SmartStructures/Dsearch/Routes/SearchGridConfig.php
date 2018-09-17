<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 14/09/18
 * Time: 09:16
 */

namespace Anakeen\SmartStructures\Dsearch\Routes;


use Anakeen\Components\Grid\Routes\GridConfig;
use SmartStructure\Fields\Report;

class SearchGridConfig extends GridConfig
{
    protected function getConfig()
    {
        $config = parent::getConfig();
        $config["toolbar"] = [];
        $config["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "consult", "title" => ___("View", "smart:dsearch") ]
            ]
        ];
        if (is_a($this->collectionDoc, \SmartStructure\Report::class)) {
            $config["footer"] = $this->getReportFooter($this->collectionDoc);
        }
        return $config;
    }

    protected function getReportFooter(\Anakeen\Core\Internal\SmartElement $document)
    {
        $return = [];


        $cols = $document->getMultipleRawValues(Report::rep_idcols);
        $foots = $document->getMultipleRawValues(Report::rep_foots);
        //$return[] = array("id" => "title","withIcon" => "true");
        foreach ($foots as $k => $function) {
            switch ($function) {
                case "CARD":
                    $s = new \SearchDoc();
                    $s->useCollection($document->initid);
                    $return[$cols[$k]] = $s->onlyCount();
                    break;

                case "MOY":
                case "SUM":
                    $attrid = $cols[$k];

                    $s = new \SearchDoc("", $document->getRawValue("se_famid"));
                    $s->useCollection($document->initid);
                    $s->returnsOnly([$attrid]);
                    $results = $s->search();

                    $sum = 0;
                    foreach ($results as $result) {
                        $sum += floatval($result[$attrid]);
                    }
                    // $return[]="$function $attrid $sum";
                    if ($function === "MOY") {
                        $sum = $sum / count($results);
                    }
                    $return[$cols[$k]] = $document->getHtmlValue($this->structureRef->getAttribute($attrid), $sum);
                    break;
                default:
                    $return[$cols[$k]] = null;
            }
        }


        return $return;
    }
}