<?php


namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Core\DocManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Routes\DocumentGrid\ColumnsDefinition;
use SmartStructure\Attributes\Report;

class SearchGridAttributes
{
    const maxSlice = 1000;
    protected $_collection = null;
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $_searchfamily = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $searchId=$args["searchId"];
        $searchDocument = DocManager::getDocument($searchId);
        if (!$searchDocument) {
            $exception = new Exception("CRUD0103", __METHOD__);
            $exception->setHttpStatus("404", "Doc not found");

            throw $exception;
        }

        $this->_searchfamily = DocManager::getFamily($searchDocument->getRawValue(Report::se_famid));
        if (!$this->_searchfamily) {
            $exception = new Exception("CRUD0103", __METHOD__);
            $exception->setHttpStatus("404", "search family not found");

            throw $exception;
        }
        $attributes = self::getGridAttributes($searchDocument);
        if (is_a($searchDocument, '\SmartStructure\Report')) {
            $footer = self::getReportFooter($searchDocument);
            $config = self::getReportConfig($searchDocument);
        } else {
            $footer = [];
            $config = self::getDefaultConfig();
        }

        //$attributes[] = array("type" => "openDoc");
        array_unshift($attributes, array("type" => "openDoc"));

        return ApiV2Response::withData($response, array(
            "attributes" => $attributes,
            "footer" => $footer,
            "config" => $config
        ));
    }

    public static function getGridAttributes(\Anakeen\Core\Internal\SmartElement $searchDocument)
    {
        $_searchfamily = DocManager::getFamily($searchDocument->getRawValue(Report::se_famid));
        if (!$_searchfamily) {
            $exception = new Exception("CRUD0103", __METHOD__);
            $exception->setHttpStatus("404", "search family not found");

            throw $exception;
        }

        if (is_a($searchDocument, \SmartStructure\Report::class)) {
            $attributes = self::getReportAttributes($searchDocument, $_searchfamily);
        } else {
            $attributes = self::getResumeAttributes($_searchfamily);
        }
        return $attributes;
    }

    protected static function getResumeAttributes(\Anakeen\Core\Internal\SmartElement $document)
    {
        $return = array();

        $return[] = array("id" => "title", "withIcon" => "true");
        foreach ($document->getAbstractAttributes() as $myAttribute) {
            $return[] = array(
                "id" => $myAttribute->id,
                "className" => sprintf("type--%s attr--%s", $myAttribute->type, $myAttribute->id)
            );
        }
        return $return;
    }

    protected static function getReportAttributes(\Anakeen\Core\Internal\SmartElement $document, \Anakeen\Core\Internal\SmartElement $_searchfamily)
    {
        $return = [];


        $cols = $document->getMultipleRawValues(Report::rep_idcols);
        if (empty($cols)) {
            $cols = ["title"];
        }
        //$return[] = array("id" => "title","withIcon" => "true");
        foreach ($cols as $attrid) {
            $attr = $_searchfamily->getAttribute($attrid);
            if ($attr && $attr->mvisibility !== "I") {
                $attrConfig = [
                    "id" => $attrid,
                    "sortable" => ColumnsDefinition::isFilterable($attr),
                    "className" => sprintf("type--%s attr--%s", $attr->type, $attr->id)
                ];
                if ($attr->type === "docid") {
                    $attrConfig["withIcon"] = true;
                }
                $return[] = $attrConfig;
            } elseif (!empty(\Anakeen\Core\Internal\SmartElement::$infofields[$attrid])) {
                $attrConfig = [
                    "id" => $attrid,
                    "sortable" => true,
                    "className" => sprintf("type--%s attr--%s", \Anakeen\Core\Internal\SmartElement::$infofields[$attrid]["type"], $attrid)
                ];
                $return[] = $attrConfig;
            }
        }
        return $return;
    }


    protected function getDefaultConfig()
    {
        $config["paging"] = self::maxSlice;
        $config["family"] = $this->_searchfamily->name;
        return $config;
    }

    protected function getReportConfig(\Anakeen\Core\Internal\SmartElement $document)
    {
        $config = $this->getDefaultConfig();
        $limit = $document->getRawValue(Report::rep_limit);
        if ($limit) {
            $config["paging"] = intval($limit);
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
                    $return[] = $s->onlyCount();
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
                    $return[] = $document->getHtmlValue($this->_searchfamily->getAttribute($attrid), $sum);
                    break;
                default:
                    $return[] = "";
            }
        }


        return $return;
    }
}
