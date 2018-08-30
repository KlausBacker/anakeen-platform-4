<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 07/08/18
 * Time: 13:53
 */

namespace Anakeen\Components\Grid\Routes;


use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\BasicAttribute;
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

    protected $columnFields = [];

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $collectionId = $args["collectionId"];
        $collectionDocument = SEManager::getDocument($collectionId);
        if (!$collectionDocument) {
            $exception = new Exception("GRID0001", $collectionId);
            $exception->setHttpStatus("404", "Smart Element not found");
            throw $exception;
        }
        $this->columnFields = self::getGridFields($collectionDocument);
        return ApiV2Response::withData($response, array(
            "smartFields" => $this->columnFields,
            "footer" => array(),
            "header" => array(),
            "contentURL" => sprintf("/api/v2/grid/content/%s?fields=%s", $collectionId, $this->getUrlFields())
        ));
    }

    protected function getUrlFields() {
        $filteredAttributes = array_filter($this->columnFields, function ($field) {
            return ($field["type"] !== 'array' && $field["type"] !== 'tab' && $field["type"] !== 'frame');
        });
        $result = implode(',', array_map(function ($field) {
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
                return self::getResumeFields($smartStructure);
        }
    }

    protected static function formatField($field)
    {
        $return = null;
        if ($field) {
            $return = [
                "field" => $field->id,
                "title" => $field->labelText,
                "encoded" => false,
                "sortable" => ColumnsDefinition::isFilterable($field),
                "className" => sprintf("type--%s attr--%s", $field->type, $field->id),
                "type" => $field->type
            ];
            if ($field->type === "docid") {
                $return["withIcon"] = true;
            }
        }
        return $return;
    }

    protected static function getTypeTemplate($id, $type) {
        switch ($type) {
            case "image":
                return "<img src='#: $id#'></img>";
            default:
                return "<strong>#: $id#</strong>";
        }
    }

    protected static function formatFieldId($fieldId, \Anakeen\Core\Internal\SmartElement $smartEl)
    {
        $return = null;
        $attr = $smartEl->getAttribute($fieldId);
        if ($attr) {
            $return = self::formatField($attr);
        } elseif (!empty(\Anakeen\Core\Internal\SmartElement::$infofields[$fieldId])) {
            $return = [
                "field" => $fieldId,
                "encoded" => false,
                "title" => _(\Anakeen\Core\Internal\SmartElement::$infofields[$fieldId]['label']),
                "sortable" => \Anakeen\Core\Internal\SmartElement::$infofields[$fieldId]['sortable'],
                "filterable" => \Anakeen\Core\Internal\SmartElement::$infofields[$fieldId]['filterable'],
                "type" => \Anakeen\Core\Internal\SmartElement::$infofields[$fieldId]["type"],
                "template" => self::getTypeTemplate($fieldId, \Anakeen\Core\Internal\SmartElement::$infofields[$fieldId]["type"])
            ];
        }
        return $return;
    }

    protected static function getReportFields(
        \Anakeen\Core\Internal\SmartElement $document,
        \Anakeen\Core\Internal\SmartElement $_searchfamily
    ) {
        $return = [];


        $cols = $document->getMultipleRawValues(Report::rep_idcols);
        if (empty($cols)) {
            $cols = self::DEFAULT_COLUMNS;
        }
        //$return[] = array("id" => "title","withIcon" => "true");
        foreach ($cols as $attrid) {
            $config = self::formatFieldId($attrid, $_searchfamily);
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

    protected static function getResumeFields(\Anakeen\Core\Internal\SmartElement $smartEl)
    {
        $return = array();

        $return[] = array(
            "field" => "title",
            "encoded" => false,
            "title" => _(\Anakeen\Core\Internal\SmartElement::$infofields['title']['label']),
            "sortable" => \Anakeen\Core\Internal\SmartElement::$infofields['title']['sortable'],
            "filterable" => \Anakeen\Core\Internal\SmartElement::$infofields['title']['filterable'],
            "type" => \Anakeen\Core\Internal\SmartElement::$infofields['title']["type"],
            "template" => self::getTypeTemplate('title', \Anakeen\Core\Internal\SmartElement::$infofields['title']["type"]));
        foreach ($smartEl->getAttributes() as $myAttribute) {
            $return[] = self::formatField($myAttribute);
        }
        return $return;
    }
}