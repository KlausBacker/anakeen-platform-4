<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 14/09/18
 * Time: 09:16
 */

namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Components\Grid\Routes\GridConfig;
use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Core\Utils\Postgres;
use SmartStructure\Fields\Report;

class SearchGridConfig extends GridConfig
{
    protected function getConfig()
    {
        $config = parent::getConfig();

        $config["actions"] = [
            "title" => "Actions",
            "actionConfigs" => [
                [ "action" => "consult", "title" => ___("Display", "smart:dsearch") ]
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

       // print_r($foots);
        foreach ($foots as $k => $function) {
            switch ($function) {
                case "CARD":
                    $s = new \Anakeen\Search\Internal\SearchSmartData();
                    $s->useCollection($document->initid);
                    $return[$cols[$k]] = $s->onlyCount();
                    break;

                case "MOY":
                case "SUM":
                    $attrid = $cols[$k];

                    $s = new \Anakeen\Search\Internal\SearchSmartData("", $document->getRawValue("se_famid"));
                    $s->useCollection($document->initid);
                    $s->returnsOnly([$attrid]);
                    $results = $s->search();

                    $oa=$this->structureRef->getAttribute($attrid);
                    $sum = 0;
                    foreach ($results as $result) {
                        if ($result[$attrid]) {
                            if ($oa && $oa->isMultiple()) {
                                $sum += array_sum(Postgres::stringToArray($result[$attrid]));
                            } else {
                                $sum += floatval($result[$attrid]);
                            }
                        }
                    }

                    // $return[]="$function $attrid $sum";
                    if ($function === "MOY") {
                        $sum = $sum / count($results);
                    }
                    if ($oa) {
                        $sum = $document->getHtmlValue($this->structureRef->getAttribute($attrid), $sum);
                    }
                    $return[$cols[$k]] =$sum;
                    break;
                default:
                    $return[$cols[$k]] = null;
            }
        }


        return $return;
    }
}
