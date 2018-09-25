<?php

namespace Anakeen\Components\Grid\Routes;


use Anakeen\Components\Grid\Operators;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartElementManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use SmartStructure\Fields\Dir;
use SmartStructure\Fields\Search;
use SmartStructure\Report;
use SmartStructure\Fields\Report as ReportFields;

class ColumnsConfig
{

    protected $requestFields = [];
    protected $availableColumns = [];
    protected $structureId = -1;
    const DEFAULT_COLUMNS = ["icon", "title"];

    /**
     * @var SmartStructure
     */
    protected $structureRef = null;

    /**
     * @var SmartElement
     */
    protected $collection = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $collectionId = $args["collectionId"];
        $this->collection = SmartElementManager::getDocument($collectionId);
        if (!$this->collection) {
            $exception = new Exception("GRID0001", $collectionId);
            $exception->setHttpStatus("404", "Smart Element not found");
            throw $exception;
        }
        $queryFieldsParam = $request->getQueryParam("fields");
        if ($queryFieldsParam) {
            $this->requestFields = array_map("trim", explode(",", $request->getQueryParam("fields", "")));
        }

        $this->availableColumns = $this->getAvailableColumns();
        return ApiV2Response::withData($response, $this->availableColumns);
    }

    protected function getAvailableColumns()
    {
        switch ($this->collection->defDoctype) {
            case "C": // Smart Structure
                $this->structureId = $this->collection->initid;
                break;
            case "D": // Dir
                $this->structureId = $this->collection->getRawValue(Dir::fld_famids);
                break;
            case "S": // Search
                $this->structureId = $this->collection->getRawValue(Search::se_famid);
                break;
        }
        if (!empty($this->structureId) && $this->structureId !== -1) {
            $this->structureRef = SmartElementManager::getFamily($this->structureId);
            if (!$this->structureRef) {
                $exception = new Exception("GRID0002", $this->structureId);
                $exception->setHttpStatus("404", "Searched Smart Structure not found");
                throw $exception;
            }
        }
        return self::getCollectionAvailableFields($this->collection, $this->structureRef, $this->requestFields);
    }

    public static function getCollectionAvailableFields(SmartElement $collection, SmartStructure $structRef = null, $returnsOnly = [])
    {
        switch ($collection->defDoctype) {
            case "C":
                return self::getStructureColumns($collection, $structRef, $returnsOnly);
            case "D":
                return self::getFolderColumns($collection, $structRef, $returnsOnly);
            case "S":
                return self::getSearchColumns($collection, $structRef, $returnsOnly);
        }
    }

    private static function getStructureColumns(SmartElement $famDoc, SmartStructure $struct, array $returnsOnly)
    {
        $return = array();
        if (count($returnsOnly)) {
            foreach ($returnsOnly as $attrId) {
                $return[] = self::getColumnConfig($attrId, $struct);
            }
        } else {
            // Display default columns (icon + title + abstract fields)
            foreach (self::DEFAULT_COLUMNS as $id) {
                $return[] = self::getColumnConfig($id, $struct);
            }
            foreach ($famDoc->getAbstractAttributes() as $myAttribute) {
                if ($myAttribute->getAccess() !== NormalAttribute::NONE_ACCESS && ($myAttribute->isNormal && $myAttribute->type !== "array")) {
                    $return[] = self::getColumnConfig($myAttribute->id, $struct);
                }
            }
        }
        return $return;
    }

    private static function getFolderColumns(SmartElement $dir, SmartStructure $struct, array $returnsOnly)
    {
        $return = array();
        // Display default columns (icon + title + abstract fields)
        foreach (self::DEFAULT_COLUMNS as $id) {
            $return[] = self::getColumnConfig($id, $struct);
        }
        return $return;
    }

    private static function getSearchColumns(SmartElement $collection, SmartStructure $structRef = null, array $returnsOnly = [])
    {
        if (is_a($collection, Report::class)) {
            return self::getReportColumns($collection, $structRef, $returnsOnly);
        }
        $return = [];
        return $return;
    }

    private static function getReportColumns(SmartElement $collection, SmartStructure $structRef = null, array $returnsOnly = [])
    {
        $return = [];
        $cols = $collection->getMultipleRawValues(ReportFields::rep_idcols);
        if (empty($cols)) {
            $cols = self::DEFAULT_COLUMNS;
        }

        if (count($returnsOnly)) {
            $cols = array_filter($cols, function ($item) use ($returnsOnly) {
                return in_array($item, $returnsOnly);
            });
        }

        foreach ($cols as $attrid) {
            $config = self::getColumnConfig($attrid, $structRef);
            if (!empty($config)) {
                $return[] = $config;
            }
        }
        return $return;
    }


    protected static function getDisplayableProperties()
    {
        $properties = array_filter(\Anakeen\Core\Internal\SmartElement::$infofields, function ($item) {
            return isset($item["displayable"]) ? $item["displayable"] : false;
        });
        array_walk($properties, function (&$value, $key) {
            $value["field"] = $key;
            $value["smartType"] = $value['type'];
            $value["title"] = _($value['label']);
            $value["property"] = true;
            $value["filterable"] = self::getFilterable($value["type"]);

            if (isset($value["displayable"])) {
                unset($value["displayable"]);
            }
            return $value;
        });
        return $properties;
    }

    public static function isSortable(\Anakeen\Core\Internal\SmartElement $tmpDoc, $attrId)
    {
        $sortable = $tmpDoc->getSortAttributes();
        return isset($sortable[$attrId]);
    }

    protected static function getFilterable($type)
    {
        $operators = Operators::getTypeOperators($type);

        if (!$operators) {
            return false;
        }

        $stringsOperators=[];
        foreach ($operators as $k=>$operator) {
            $stringsOperators[$k]= $operator["label"];
        }


        $filterable = [
            "operators" => [
                "string" => $stringsOperators,
                "date" => $stringsOperators,
            ],
            "cell" => [
                "enable" => true,
                "delay" => 9999999999 // Wait 115 days : only way to have the clear button easyly
            ]
        ];
        return $filterable;
    }

    public static function getColumnFilterConfig(\Anakeen\Core\SmartStructure\BasicAttribute $attr)
    {
        if ($attr->getAccess() === BasicAttribute::NONE_ACCESS) {
            return false;
        }
        if ($attr->getOption("searchCriteria") === "hidden") {
            return false;
        }

        return self::getFilterable($attr->type . ($attr->isMultiple() ? '[]' : '') . ($attr->isMultipleInArray() ? '[]' : ''));
    }

    protected static function getAttributeConfig(\Anakeen\Core\SmartStructure\BasicAttribute $currentAttribute, \Anakeen\Core\SmartStructure $family)
    {
        $data = array(
            "field" => $currentAttribute->id,
            "multiple" => $currentAttribute->isMultiple(),

            "smartType" => $currentAttribute->type,
            "title" => $currentAttribute->getLabel(),
            "context" => self::getContextLabels($currentAttribute),
            "relation" => $currentAttribute->format,
            "withContext" => true,
            "encoded" => false,
            "sortable" => self::isSortable($family, $currentAttribute->id),
            "filterable" => self::getColumnFilterConfig($currentAttribute),

        );
        if (($data["type"] == "docid" || $data["type"] == "account") && $data["filterable"]) {
            $data["doctitle"] = $currentAttribute->getOption("doctitle") == "auto" ? $currentAttribute->id . "_title" : $currentAttribute->getOption("doctitle");
        }
        return $data;
    }

    protected static function getContextLabels(BasicAttribute $attribute, $contextLabels = [])
    {
        if ($attribute && $attribute->fieldSet && $attribute->fieldSet->id != \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
            array_unshift($contextLabels, $attribute->fieldSet->getLabel());
            return self::getContextLabels($attribute->fieldSet, $contextLabels);
        }
        return $contextLabels;
    }

    public static function getColumnConfig($fieldId, \Anakeen\Core\Internal\SmartElement $smartEl = null)
    {
        $properties = self::getDisplayableProperties();
        if (isset($properties[$fieldId])) {
            $currentData = $properties[$fieldId];
            return $currentData;
        }

        if (!empty($smartEl)) {
            $currentAttribute = $smartEl->getAttribute($fieldId);
            if (is_object($currentAttribute)) {
                return self::getAttributeConfig($currentAttribute, $smartEl);
            } else {
                if (isset($properties[$fieldId])) {
                    return $properties[$fieldId];
                } else {
                    throw new Exception(sprintf("Unknown id %s, famId %s", $fieldId, $smartEl->id));
                }
            }
        }
    }
}