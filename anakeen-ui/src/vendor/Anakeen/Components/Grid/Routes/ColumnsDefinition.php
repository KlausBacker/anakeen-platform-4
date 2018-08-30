<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 09/08/18
 * Time: 15:51
 */

namespace Anakeen\Components\Grid\Routes;


use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

class ColumnsDefinition
{
    protected $properties = [];
    protected $defaultFamilyId = false;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->properties = array_filter(\Anakeen\Core\Internal\SmartElement::$infofields, function ($item) {
            return isset($item["displayable"]) ? $item["displayable"] : false;
        });

        array_walk($this->properties, function (&$value, $key) {
            $value["id"] = $key;
            $value["type"] = $key;
            $value["label"] = _($value['label']);
            if (isset($value["displayable"])) {
                unset($value["displayable"]);
            }
            return $value;
        });

        $this->defaultFamilyId = $request->getQueryParam("famId", false);
        $needVisibleColumns = ($request->getQueryParam("familyColumns") === "true");
        /**
         * @var \Anakeen\Core\SmartStructure [] $famDef
         */
        $famDef = array();
        $displayedColumns = array();

        if ($this->defaultFamilyId !== false) {
            $famDef[$this->defaultFamilyId] = SEManager::getFamily($this->defaultFamilyId);
        }

        $elementsId = $request->getQueryParam("columns", "");
        $elementsId = explode(",", $elementsId);
        foreach ($elementsId as $currentColumn) {
            if (!isset($currentColumn)) {
                throw new Exception("Bad column definition : id not defined");
            }
            $displayedColumns[] = $this->getColumnDef($currentColumn, $famDef);
        }

        $userColumns = [];
        $visibleColumns = [];
        if ($needVisibleColumns) {
            $userFamPref = json_decode(\Anakeen\Core\Internal\ApplicationParameterManager::getUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS"), true);

            $userAttrids = [];
            if (!empty($userFamPref[$famDef[$this->defaultFamilyId]->name])) {
                $userAttrids = $userFamPref[$famDef[$this->defaultFamilyId]->name];
            }
            $attributes = $famDef[$this->defaultFamilyId]->getNormalAttributes();
            foreach ($attributes as $attrid => $attrdef) {
                if ($attrdef->type !== "array" && $attrdef->mvisibility !== "H" && $attrdef->mvisibility !== "I" && $attrdef->mvisibility !== "O") {
                    $attrInfo = $this->getAttributeDef($attrdef, $famDef[$this->defaultFamilyId]);

                    if ($userAttrids && in_array($attrdef->id, $userAttrids)) {
                        $attrInfo["userOrder"] = array_search($attrdef->id, $userAttrids);
                        $userColumns[] = $attrInfo;
                        $attrInfo["isUserVisible"] = true;
                    }
                    $visibleColumns[] = $attrInfo;
                }
            }
        }

        usort($visibleColumns, function ($a, $b) {
            return strcmp($a["label"], $b["label"]);
        });

        usort($userColumns, function ($a, $b) {
            return ($a["userOrder"] > $b["userOrder"]) ? 1 : (($a["userOrder"] > $b["userOrder"]) ? -1 : 0);
        });
        return ApiV2Response::withData($response, ["displayColumns" => $displayedColumns, "userColumns" => $userColumns, "visibleColumns" => $visibleColumns]);
    }

    protected function getColumnDef($columnId, &$famDef)
    {
        if (isset($this->properties[$columnId])) {
            $currentData = $this->properties[$columnId];
            if ($columnId == "state") {
                $currentData["urlSource"] = "?app=DOCUMENT_GRID_HTML5&action=GETSTATES";
                $currentFamDoc = $this->getCurrentFamDoc($columnId, $famDef);
                $currentData["urlSource"] .= "&famid=" . urlencode($currentFamDoc->name);
            }
            return $currentData;
        }
        $currentFamDoc = $this->getCurrentFamDoc($columnId, $famDef);

        $currentAttribute = $currentFamDoc->getAttribute($columnId);
        if (is_object($currentAttribute)) {
            return $this->getAttributeDef($currentAttribute, $currentFamDoc);
        } else {
            if (isset($this->properties[$columnId])) {
                return $this->properties[$columnId];
            } else {
                throw new Exception(sprintf("Unknown id %s, famId %s", $columnId, $currentFamDoc->name));
            }
        }
    }

    protected function getAttributeDef(\Anakeen\Core\SmartStructure\BasicAttribute $currentAttribute, \Anakeen\Core\SmartStructure $family)
    {
        $data = array(
            "id" => $currentAttribute->id,
            "type" => $currentAttribute->type,
            "label" => $currentAttribute->getLabel(),
            "sortable" => $this->isSortable($family, $currentAttribute->id),
            "filterable" => $this->isFilterable($currentAttribute)
        );
        if (($data["type"] == "docid" || $data["type"] == "account") && $data["filterable"]) {
            $data["doctitle"] = $currentAttribute->getOption("doctitle") == "auto" ? $currentAttribute->id . "_title" : $currentAttribute->getOption("doctitle");
        }
        return $data;
    }

    /**
     * Get currentFamDoc
     *
     * @param                                $currentColumn
     * @param \Anakeen\Core\SmartStructure[] $famDef
     * @param                                $this ->defaultFamilyId
     *
     * @return \Anakeen\Core\SmartStructure
     * @throws Exception
     */
    protected function getCurrentFamDoc($currentColumn, &$famDef)
    {
        /* @var \Anakeen\Core\Internal\SmartElement $currentFamDoc */
        if (isset($currentColumn["famId"])) {
            if (!isset($famDef[$currentColumn["famId"]])) {
                $famDef[$currentColumn["famId"]] = SEManager::getFamily($currentColumn["famId"]);
            }
            $currentFamDoc = $famDef[$currentColumn["famId"]];
        } else {
            if ($this->defaultFamilyId) {
                $currentFamDoc = $famDef[$this->defaultFamilyId];
            } else {
                throw new Exception(sprintf("No famId and no default fam for attribute %s", $currentColumn));
            }
        }
        if (!$currentFamDoc || !$currentFamDoc->isAlive()) {
            throw new Exception(sprintf("The current fam %s is not alive", isset($currentColumn["famId"]) ? $currentColumn["famId"] : $this->defaultFamilyId));
        }
        return $currentFamDoc;
    }

    public static function isSortable(\Anakeen\Core\Internal\SmartElement $tmpDoc, $attrId)
    {
        $sortable = $tmpDoc->getSortAttributes();
        return isset($sortable[$attrId]);
    }

    public static function isFilterable(\Anakeen\Core\SmartStructure\BasicAttribute $attr)
    {
        if ($attr->getAccess() === BasicAttribute::NONE_ACCESS) {
            return false;
        }
        $isFilterable = (in_array($attr->type, array(
                "text",
                "longtext",
                "htmltext",
                "docid",
                "enum",
                "account",
                "money",
                "int",
                "double",
                "date"
            )) && $attr->getOption("searchCriteria") != "hidden");

        if ($isFilterable && $attr->isMultiple() && ($attr->type === "money" || $attr->type === "int" || $attr->type === "double" || $attr->type === "date")) {
            // No operators for multiple numeric values
            $isFilterable = false;
        }
        if ($isFilterable && ($attr->type === "docid" || $attr->type === "account")) {
            $docTitle = $attr->getOption("doctitle");
            $isFilterable = !empty($docTitle);
        }

        return $isFilterable;
    }
}