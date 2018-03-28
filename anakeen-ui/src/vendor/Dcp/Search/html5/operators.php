<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21/04/15
 * Time: 09:04
 */

namespace Dcp\Search\html5;

use Dcp\HttpApi\V1\Crud\DocumentCollection;
use Dcp\HttpApi\V1\Crud\Exception;

class operators extends DocumentCollection
{
    
    protected $_collection = null;
    
    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create");
        
        throw $exception;
    }
    
    public function read($resourceId)
    {
        $return = array();
        $arrayTypeArray = array(
            "text[]",
            "longtext[]",
            "image[]",
            "file[]",
            "enum[]",
            "date[]",
            "int[]",
            "double[]",
            "money[]",
            "password[]",
            "xml[]",
            "thesaurus[]",
            "time[]",
            "timestamp[]",
            "color[]",
            "docid[]",
            "htmltext[]",
            "account[]"
        );
        $doccollection = new \DocCollection();
        
        foreach ($doccollection->top as & $tmptop) {
            
            $tmpTypedLabel = array();
            $tmpTypedTitle = array();
            $tmpTypeArray = array();
            $tmpCompatibleTypes = isset($tmptop["type"])?$tmptop["type"]:null;
            $tmpId = array_search($tmptop, $doccollection->top);
            
            if (is_array($tmpCompatibleTypes)) {
                foreach ($tmpCompatibleTypes as $k => $type) {
                    if ($type == "array") {
                        unset($tmpCompatibleTypes[$k]);
                        $tmpCompatibleTypes = array_values($tmpCompatibleTypes);
                        $tmpCompatibleTypes = array_merge($tmpCompatibleTypes, $arrayTypeArray);
                    }
                }
            }
            
            if (!empty($tmptop["sdynlabel"])) {
                foreach ($tmptop["sdynlabel"] as $k => $label) {
                    $tmpTypedLabel[$k] = _($label);
                }
                foreach ($tmpTypedLabel as $type => $label) {
                    if ($type == "array") {
                        foreach ($arrayTypeArray as $k) {
                            $tmpTypeArray[$k] = $label;
                        }
                        unset($tmpTypedLabel[$type]);
                        $tmpTypedLabel = array_merge($tmpTypedLabel, $tmpTypeArray);
                    }
                }
            }
            $tmpTypeArray = array();
            
            if (!empty($tmptop["slabel"])) {
                foreach ($tmptop["slabel"] as $k => $label) {
                    $tmpTypedTitle[$k] = _($label);
                }
                foreach ($tmpTypedTitle as $type => $label) {
                    if ($type == "array") {
                        foreach ($arrayTypeArray as $k) {
                            $tmpTypeArray[$k] = $label;
                        }
                        unset($tmpTypedTitle[$type]);
                        $tmpTypedTitle = array_merge($tmpTypedTitle, $tmpTypeArray);
                    }
                }
            }
            
            if (($tmpId == "=") || ($tmpId == "!=")) {
                $tmpCompatibleTypes[] = "wid";
            }
            
            $return[] = array(
                "id" => $tmpId,
                "title" => _($tmptop["label"]) ,
                "label" => _($tmptop["dynlabel"]) ,
                "typedTitle" => $tmpTypedTitle,
                "typedLabel" => $tmpTypedLabel,
                "compatibleTypes" => $tmpCompatibleTypes,
                "operands" => $tmptop["operand"]
            );
        }
        
        return $return;
    }
    
    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update");
        
        throw $exception;
    }
    
    public function delete($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete");
        
        throw $exception;
    }
    
    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new \SearchDoc("", -1);
        
        $this->_searchDoc->setObjectReturn();
    }
    /**
     * Return etag info
     *
     * @return null|string
     */
    public function getEtagInfo()
    {
        $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        
        return implode(",", $result);
    }
}
