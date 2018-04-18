<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui\Crud;

class FamilyStructure extends \Dcp\HttpApi\V1\Crud\Document
{
    
    public function __construct(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        parent::__construct();
        
        $this->defaultFields = self::GET_STRUCTURE;
    }
    
    public function read($resourceId)
    {
        $this->setDocument($resourceId);
        if ($this->_document->doctype !== "C") {
            throw new \Dcp\Ui\Exception("CRUDUI0013", $resourceId);
        }
        return $this->getDocumentData();
    }
    
    public function update($resourceId)
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update structure");
        throw $exception;
    }
    public function delete($resourceId)
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete structure");
        throw $exception;
    }
    /**
     * Compute etag from an id
     *
     * @param $id
     *
     * @return string
     * @throws \Dcp\Db\Exception
     */
    protected function extractEtagDataFromId($id)
    {
        $result = array();
        $sql = sprintf("select revdate from docfam where id = %d", $id);
        simpleQuery(getDbAccess() , $sql, $result, false, true);
        // Necessary only when use family.structure
        $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        return join(" ", $result);
    }
    /**
     * Get the attribute info
     *
     * @param \Anakeen\Core\SmartStructure\BasicAttribute $attribute
     * @param int $order
     * @return array
     */
    public function getAttributeInfo(\Anakeen\Core\SmartStructure\BasicAttribute $attribute, $order = 0)
    {
        $info = parent::getAttributeInfo($attribute, $order);
        if ($attribute->format) {
            $info["typeFormat"] = $attribute->format;
        }
        return $info;
    }
}
