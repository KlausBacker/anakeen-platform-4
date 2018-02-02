<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 05/10/17
 * Time: 11:50
 */

namespace Anakeen\Routes\DocumentsList;

use Dcp\HttpApi\V1\Crud\DocumentCollection;
use Dcp\HttpApi\V1\Crud\DocumentUtils;
use Dcp\HttpApi\V1\DocManager\DocManager;
use Dcp\Core\ContextManager;
use Dcp\HttpApi\V1\Api\Exception;

class DocumentsList extends DocumentCollection
{
    /**
     * @var array definition of current collection
     */
    protected $_collectionDoc = null;
    protected $_familyDoc = null;
    /**
     * @var string default value for order
     */
    protected $_defaultOrder = 'title asc';

    protected $_filter = null;


    /**
     * Create new ressource
     * @return mixed
     */
    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus(
            "405", "You cannot create"
        );
        throw $exception;
    }

    /**
     * Read a ressource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     */
    public function read($resourceId)
    {
        $return = parent::read($resourceId);
        $return["resultMax"] = $this->_searchDoc->onlyCount();
        $return['paginationState'] = $this->getPaginationState();
        $return['user'] = ["id"=>intval(ContextManager::getCurrentUser()->id), "fid"=>intval(ContextManager::getCurrentUser()->fid)];
        return $return;
    }

    /**
     * Update the ressource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     */
    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus(
            "405", "You cannot update"
        );
        throw $exception;
    }

    /**
     * Delete ressource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     */
    public function delete($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus(
            "405", "You cannot delete"
        );
        throw $exception;
    }

    public function setUrlParameters(Array $array)
    {
        $this->urlParameters = $array;
        $familyId = isset($this->urlParameters["familyId"]) ? $this->urlParameters["familyId"] : false;
        $doc = DocManager::getDocument($familyId);
        switch ($doc->defDoctype) {
            case 'C':
                $this->_familyDoc = $doc;
                if (!$this->_familyDoc) {
                    $exception = new Exception('DOCLIST0001', $familyId);
                    $exception->setHttpStatus("404", "Family not found");
                    throw $exception;
                }
                break;
            case 'F':
            case 'S':
                $this->_collectionDoc = $doc;
                if (!$this->_collectionDoc) {
                    $exception = new Exception("DOCLIST0002", $familyId);
                    $exception->setHttpStatus("404", "Collection not found");
                    throw $exception;
                }
                break;
            default:
                $exception = new Exception("DOCLIST0003", $familyId);
                $exception->setHttpStatus("400", "Document is not a family or collection");
                throw $exception;
        }
    }

    protected function prepareSearchDoc() {
        $this->_searchDoc = new \SearchDoc();
        if ($this->_collectionDoc) {
            $this->_searchDoc->useCollection($this->_collectionDoc->id);
        } else if ($this->_familyDoc) {
            $this->_searchDoc->fromid = $this->_familyDoc->id;
        }
        $this->_searchDoc->setObjectReturn();

        if (!empty($this->contentParameters['filter'])) {
            $this->_filter = $this->contentParameters['filter'];
            $this->_searchDoc->addFilter("title ~* '%s'", preg_quote($this->_filter));
        }

    }

    protected function extractOrderBy()
    {
        $orderBy = isset($this->contentParameters["orderBy"]) ? $this->contentParameters["orderBy"] : "title:asc";
        if ($this->_familyDoc) {
            return DocumentUtils::extractOrderBy($orderBy, $this->_familyDoc);

        } else if ($this->_collectionDoc) {
            $familyOfCollectionId = $this->_collectionDoc->getValue("se_famid");
            if (isset($familyOfCollectionId)) {
                return DocumentUtils::extractOrderBy($orderBy, DocManager::getFamily($familyOfCollectionId));
            }
        }

    }

    protected function getPaginationState()
    {
        return ["page" => intval($this->contentParameters['page']) , "slice" => intval($this->contentParameters['slice']) , "total_entries" => $this->_searchDoc->onlyCount() ];
    }
}