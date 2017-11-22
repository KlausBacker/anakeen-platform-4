<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 05/10/17
 * Time: 11:50
 */

namespace Anakeen\Sample\Routes;

use Dcp\HttpApi\V1\Crud\FamilyDocumentCollection;
use Dcp\HttpApi\V1\DocManager\DocManager;
use Dcp\Core\ContextManager;
use Anakeen\Sample\Routes\Exception;

class DocumentsList extends FamilyDocumentCollection
{

    /**
     * @var \Doc current anakeen platform collection
     */
    protected $_apCollection = null;

    /**
     * @var string reference of current collection
     */
    protected $_collectionRef;
    /**
     * @var array definition of current collection
     */
    protected $_collection;
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

    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        if (!empty($this->contentParameters['filter'])) {
            $this->_filter = $this->contentParameters['filter'];
            $this->_searchDoc->addFilter("title ~* '%s'", preg_quote($this->_filter));
        }
    }

    protected function getPaginationState()
    {
        return ["page" => intval($this->contentParameters['page']) , "slice" => intval($this->contentParameters['slice']) , "total_entries" => $this->_searchDoc->onlyCount() ];
    }
}