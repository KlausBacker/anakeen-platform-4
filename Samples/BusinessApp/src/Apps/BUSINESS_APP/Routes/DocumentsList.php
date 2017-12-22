<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 05/10/17
 * Time: 11:50
 */

namespace Anakeen\Sample\Routes;

use Dcp\HttpApi\V1\Crud\DocumentUtils;
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
        $return['debug'] = $this->_searchDoc->getSearchInfo();
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
        if ($familyId === 'BA_FEES_TO_VALIDATE' || $familyId === 'BA_FEES_TO_INTEGRATE') {
            $familyId = 'BA_FEES';
        }
        DocumentUtils::checkFamilyId($familyId, "families/%s/documents/");
        $this->_family = DocManager::getFamily($familyId);
        if (!$this->_family) {
            $exception = new Exception("CRUD0200", $familyId);
            $exception->setHttpStatus("404", "Family not found");
            throw $exception;
        }
    }

    protected function prepareSearchDoc() {

        if ($this->urlParameters['familyId'] === 'BA_FEES_TO_VALIDATE') {
            $this->_searchDoc = new \SearchDoc("", "BA_FEES");
            $this->_searchDoc->addFilter("state = 'e_ba_filled'");
        } elseif ($this->urlParameters['familyId'] === 'BA_FEES_TO_INTEGRATE') {
            $this->_searchDoc = new \SearchDoc("", "BA_FEES");
            $this->_searchDoc->addFilter("state = 'e_ba_validated'");
        } else {
            $this->_searchDoc = new \SearchDoc("", $this->_family->name);
        }
        $this->_searchDoc->setObjectReturn();

        if ($this->urlParameters['familyId'] === 'BA_FEES') {
            $this->_searchDoc->addFilter("fee_account = '%s'", ContextManager::getCurrentUser()->fid);
        }

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