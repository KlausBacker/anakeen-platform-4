<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21/04/15
 * Time: 11:44
 */

namespace Dcp\Search\html5;

use Dcp\HttpApi\V1\Crud\DocumentCollection;
use Dcp\HttpApi\V1\Crud\Exception;

class attributes extends DocumentCollection
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
        
        if (!isset($resourceId)) {
            $resourceId = 0;
        }
        
        $return = array();
        //Propriétés
        $internals = array(
            "title" => ___("doctitle", "searchui") ,
            "revdate" => ___("revdate", "searchui") ,
            "cdate" => ___("cdate", "searchui") ,
            "revision" => ___("revision", "searchui") ,
            "owner" => ___("id owner", "searchui") ,
            "locked" => ___("id locked", "searchui") ,
            "allocated" => ___("id allocated", "searchui") ,
            "svalues" => ___("any values", "searchui")
        );
        
        if ($resourceId) {
            $tmpDoc = \Dcp\HttpApi\V1\DocManager\DocManager::createTemporaryDocument($resourceId);
        } else $tmpDoc = \Dcp\HttpApi\V1\DocManager\DocManager::createTemporaryDocument(1);
        
        foreach ($internals as $k => $v) {
            if ($k == "revdate") $type = "date";
            else if ($k == "owner") $type = "uid";
            else if ($k == "locked") $type = "uid";
            else if ($k == "allocated") $type = "uid";
            else if ($k == "cdate") $type = "date";
            else if ($k == "revision") $type = "int";
            else if ($k == "state") $type = "docid";
            else $type = "text";
            
            $methods = $tmpDoc->getSearchMethods("__properties__", $type);
            
            $return[] = array(
                "id" => $k,
                "label" => $v,
                "type" => $type,
                "methods" => $methods,
                "parent" => array(
                    "id" => "__properties__",
                    "label" => ___("Properties", "searchui")
                )
            );
        }
        
        if ($resourceId) {
            $fdoc = \Dcp\HttpApi\V1\DocManager\DocManager::getFamily($resourceId);
            if (!$fdoc) {
                $exception = new Exception("CRUD0103", __METHOD__);
                $exception->setHttpStatus("404", "Family not found");
                
                throw $exception;
            }
            //Attributs
            $tmpDoc = \Dcp\HttpApi\V1\DocManager\DocManager::createTemporaryDocument($resourceId);
            
            foreach ($fdoc->getNormalAttributes() as $myAttribute) {
                if ($myAttribute->type == "array" || $myAttribute->type == "password") {
                    continue;
                }
                $optSearchable = $myAttribute->getOption("searchcriteria", "");
                if ($optSearchable == "hidden" || $optSearchable == "restricted") {
                    continue;
                }
                $type = $myAttribute->type;
                if ($myAttribute->isMultiple()) $type = $myAttribute->type . "[]";
                
                $methods = $tmpDoc->getSearchMethods($myAttribute->id, $type);
                $return[] = array(
                    "id" => $myAttribute->id,
                    "label" => $myAttribute->getLabel() ,
                    "type" => $type,
                    "methods" => $methods,
                    "parent" => array(
                        "id" => $myAttribute->fieldSet->id,
                        "label" => $myAttribute->fieldSet->getLabel()
                    )
                );
            }
            
            if (isset($fdoc->wid)) {
                $return[] = array(
                    "id" => "state",
                    "methods" => [],
                    "label" => array(
                        ___("activity") ,
                        ___("step") ,
                        ___("state")
                    ) ,
                    "type" => "wid",
                    "parent" => array(
                        "id" => "workflow",
                        "label" => ___("workflow")
                    )
                );
            }
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
        if (isset($this->urlParameters["identifier"])) {
            
            $result[] = $this->urlParameters["identifier"];
            $result[] = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
            $result[] = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
            
            return implode(",", $result);
        }
        return null;
    }
}
