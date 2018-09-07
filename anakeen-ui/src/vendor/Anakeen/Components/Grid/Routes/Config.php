<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 07/08/18
 * Time: 13:53
 */

namespace Anakeen\Components\Grid\Routes;


use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use SmartStructure\Fields\Search;
use SmartStructure\Fields\Report;
use SmartStructure\Fields\Dir;

/**
 * Config for Smart Element Grid
 *
 * Class Config
 *
 * @note    Used by route : GET api/v2/grid/config
 * @package Anakeen\Routes\Authent
 */
class Config
{

    const DEFAULT_COLUMNS = ["icon", "title"];

    protected $gridFields = [];
    protected $urlFields = [];

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $collectionId = $args["collectionId"];
        $collectionDocument = SEManager::getDocument($collectionId);
        if (!$collectionDocument) {
            $exception = new Exception("GRID0001", $collectionId);
            $exception->setHttpStatus("404", "Smart Element not found");
            throw $exception;
        }
        $urlFieldsParam = $request->getQueryParam("fields", "");
        $this->gridFields = self::getGridFields($collectionDocument);
        if (!empty($urlFieldsParam)) {
            $urlFields = array_map("trim", explode(",", $urlFieldsParam));
            $this->gridFields = array_values(array_filter($this->gridFields, function ($item) use ($urlFields){
                return in_array($item["field"], $urlFields);
            }));
        }
        return ApiV2Response::withData($response, array(
            "smartFields" => $this->gridFields,
            "footer" => array(),
            "header" => array(),
            "contentURL" => sprintf("/api/v2/grid/content/%s%s", $collectionId, "?fields=".$this->getUrlFields())
        ));
    }

    protected function getUrlFields() {
        $filteredAttributes = array_filter($this->gridFields, function ($field) {
            return ($field["type"] !== 'array' && $field["type"] !== 'tab' && $field["type"] !== 'frame');
        });
        $result = implode(',', array_map(function ($field) {
            if ($field["property"]) {
                return "document.properties.".$field["field"];
            }
            return "document.attributes.".$field["field"];
        }, $filteredAttributes));
        return $result;
    }

    public static function getGridFields(\Anakeen\Core\Internal\SmartElement $smartElement)
    {
        $smartStructureId = null;
        switch ($smartElement->defDoctype) {
            case "C": // Smart Structure
                $smartStructureId = $smartElement->initid;
                break;
            case "D": // Dir
                $smartStructureId = $smartElement->getRawValue(Dir::fld_famids);
                break;
            case "S": // Search
                $smartStructureId = $smartElement->getRawValue(Search::se_famid);
                break;
        }
        $smartStructure = SEManager::getFamily($smartStructureId);
        if (!$smartStructure) {
            $exception = new Exception("GRID0002", $smartStructureId);
            $exception->setHttpStatus("404", "Searched Smart Structure not found");
            throw $exception;
        }
        return self::getFields($smartElement, $smartStructure);
    }

    protected static function getFields($smartElement, $smartStructure)
    {
        switch ($smartElement->defDoctype) {
            case "D": // Dir
                return self::getDirFields($smartElement, $smartStructure);
            case "S": // Search
                return self::getSearchFields($smartElement, $smartStructure);
            default:
                return self::getResumeFields($smartElement, $smartStructure);
        }
    }

    protected static function getReportFields(
        \Anakeen\Core\Internal\SmartElement $document,
        $smartStruct
    ) {
        $return = [];


        $cols = $document->getMultipleRawValues(Report::rep_idcols);
        if (empty($cols)) {
            $cols = self::DEFAULT_COLUMNS;
        }

        foreach ($cols as $attrid) {
            $config = ColumnsConfig::getColumnConfig($attrid, $smartStruct);
            if (!empty($config)) {
                $return[] = $config;
            }
        }
        return $return;
    }

    protected static function getSearchFields($searchSE, $smartStruct)
    {
        if (is_a($searchSE, \SmartStructure\Report::class)) {
            return self::getReportFields($searchSE, $smartStruct);
        }
        $return = [];
        return $return;
    }

    protected static function getDirFields($dirSE, $smartStruct)
    {

    }

    protected static function getResumeFields(\Anakeen\Core\Internal\SmartElement $smartEl, $smartStruct)
    {
        $return = array();
        foreach (self::DEFAULT_COLUMNS as $id) {
            $return[] = ColumnsConfig::getColumnConfig($id, $smartEl);
        }

        foreach ($smartEl->getAttributes() as $myAttribute) {
            if ($myAttribute->getAccess() !== NormalAttribute::NONE_ACCESS && $myAttribute->type !== "array" && $myAttribute->type !== "tab" && $myAttribute->type !== "frame") {
                $return[] = ColumnsConfig::getColumnConfig($myAttribute->id, $smartStruct);
            }
        }
        return $return;
    }
}