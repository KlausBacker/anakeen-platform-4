<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 05/11/15
 * Time: 15:10
 */

namespace Dcp\DocumentGrid\HTML5\REST;

use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\DocManager\DocManager;

class ColumnsDefinition extends Crud
{
    protected $properties = [];
    protected $defaultFamilyId = false;
    /**
     * Create new ressource
     *
     * @return mixed
     * @throws Exception
     */
    public function create()
    {
        $famId = $this->contentParameters["famId"];
        $attrId = $this->contentParameters["attrid"];
        
        $userCol = json_decode(\Anakeen\Core\Internal\ApplicationParameterManager::getUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS") , true);
        if (!$userCol) {
            $userCol = [];
        }
        
        $family = DocManager::getFamily($famId);
        $attribute = $family->getAttribute($attrId);
        $userCol[$family->name][] = $attribute->id;
        $userCol[$family->name] = array_unique($userCol[$family->name]);
        
        \Anakeen\Core\Internal\ApplicationParameterManager::setUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS", json_encode($userCol));
        
        return ["family" => $famId, "attrid" => $attrId];
    }
    /**
     * Read a ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public function read($resourceId)
    {
        $this->properties = array_filter(\Doc::$infofields, function ($item)
        {
            return isset($item["displayable"]) ? $item["displayable"] : false;
        });
        
        array_walk($this->properties, function (&$value, $key)
        {
            $value["id"] = $key;
            $value["type"] = $key;
            $value["label"] = _($value['label']);
            if (isset($value["displayable"])) {
                unset($value["displayable"]);
            }
            return $value;
        });
        
        $this->defaultFamilyId = isset($this->contentParameters["famId"]) ? $this->contentParameters["famId"] : false;
        
        $needVisibleColumns = ($this->contentParameters["familyColumns"] === "true");
        /**
         *  @var \DocFam[] $famDef
         */
        $famDef = array();
        $displayedColumns = array();
        
        if ($this->defaultFamilyId !== false) {
            $famDef[$this->defaultFamilyId] = new_Doc("", $this->defaultFamilyId);
        }
        
        $elementsId = isset($this->contentParameters["columns"]) ? $this->contentParameters["columns"] : "";
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
            $userFamPref = json_decode(\Anakeen\Core\Internal\ApplicationParameterManager::getUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS") , true);
            
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
        
        usort($visibleColumns, function ($a, $b)
        {
            return strcmp($a["label"], $b["label"]);
        });
        
        usort($userColumns, function ($a, $b)
        {
            return ($a["userOrder"] > $b["userOrder"]) ? 1 : (($a["userOrder"] > $b["userOrder"]) ? -1 : 0);
        });
        return ["displayColumns" => $displayedColumns, "userColumns" => $userColumns, "visibleColumns" => $visibleColumns];
    }
    
    protected function getColumnDef($columnId, &$famDef)
    {
        if (isset($this->properties[$columnId])) {
            $currentData = $this->properties[$columnId];
            if ($columnId == "state") {
                $currentData["urlSource"] = "?app=DOCUMENT_GRID_HTML5&action=GETSTATES";
                $currentFamDoc = $this->getCurrentFamDoc($columnId, $famDef);
                $currentData["urlSource"].= "&famid=" . urlencode($currentFamDoc->name);
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
    
    protected function getAttributeDef(\Anakeen\Core\SmartStructure\BasicAttribute $currentAttribute, \DocFam $family)
    {
        $data = array(
            "id" => $currentAttribute->id,
            "type" => $currentAttribute->type,
            "label" => $currentAttribute->getLabel() ,
            "sortable" => $this->isSortable($family, $currentAttribute->id) ,
            "filterable" => $this->isFilterable($currentAttribute)
        );
        if (($data["type"] == "docid" || $data["type"] == "account") && $data["filterable"]) {
            $data["doctitle"] = $currentAttribute->getOption("doctitle") == "auto" ? $currentAttribute->id . "_title" : $currentAttribute->getOption("doctitle");
        }
        return $data;
    }
    /**
     * Update the ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update the column definition");
        throw $exception;
    }
    /**
     * Delete ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function delete($resourceId)
    {
        $this->contentParameters = \Dcp\HttpApi\V1\Api\Router::extractContentParameters("UPDATE", $this);
        
        $famId = $this->contentParameters["famId"];
        $attrId = $this->contentParameters["attrid"];
        $userCol = json_decode(\Anakeen\Core\Internal\ApplicationParameterManager::getUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS") , true);
        if (!$userCol) {
            $userCol = [];
        }
        
        $family = DocManager::getFamily($famId);
        $attribute = $family->getAttribute($attrId);
        
        $famPref = $userCol[$family->name];
        
        unset($famPref[array_search($attribute->id, $famPref) ]);
        $userCol[$family->name] = array_unique(array_values($famPref));
        
        \Anakeen\Core\Internal\ApplicationParameterManager::setUserParameterValue("DOCUMENT_GRID_HTML5", "DG_USERFAMILYCOLS", json_encode($userCol));
        
        return ["family" => $famId, "attrid" => $attrId];
    }
    /**
     * Get currentFamDoc
     *
     * @param $currentColumn
     * @param \DocFam[] $famDef
     * @param $this->defaultFamilyId
     *
     * @return \DocFam
     * @throws Exception
     */
    protected function getCurrentFamDoc($currentColumn, &$famDef)
    {
        /* @var \Doc $currentFamDoc */
        if (isset($currentColumn["famId"])) {
            if (!isset($famDef[$currentColumn["famId"]])) {
                $famDef[$currentColumn["famId"]] = new_Doc('', $currentColumn["famId"]);
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
    
    protected function isSortable(\Doc $tmpDoc, $attrId)
    {
        $sortable = $tmpDoc->getSortAttributes();
        return isset($sortable[$attrId]);
    }
    
    public static function isFilterable(\Anakeen\Core\SmartStructure\BasicAttribute $attr)
    {
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
    public function getEtagInfo()
    {
        if (isset($this->urlParameters["identifier"])) {
            $result[] = $this->urlParameters["identifier"];
            $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
            $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
            return implode(",", $result);
        }
        return null;
    }
}
