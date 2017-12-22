<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 02/10/17
 * Time: 16:10
 */

namespace Anakeen\Sample\Routes;

use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\Crud\DocumentCollection;
use Dcp\HttpApi\V1\Crud\FamilyDocumentCollection;
use Dcp\HttpApi\V1\DocManager\DocManager;
use Dcp\Core\ContextManager;
use Anakeen\Sample\Routes\Exception;

class Collections extends DocumentCollection
{
    /**
     * @var \Doc current dcp collection
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
     * Create new ressource
     *
     * @return mixed
     * @throws Exception
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
     *
     * @param string|int $resourceId Resource identifier
     * @return mixed
     * @throws Exception
     */
    public function read($resourceId)
    {
//        $bdlConfig = Utils::getBdlConfig($this->_bdlInstance);
        $return = parent::read($resourceId);
        $return["resultMax"] = $this->_searchDoc->onlyCount();
        $user = ContextManager::getCurrentUser();
        $return['user'] = [ 'id' => $user->id, 'firstName' => $user->firstname, 'lastName' => $user->lastname, 'fid' => $user->fid, 'roles' => $user->getAllRoles()];
        $getCollectionInfo = function ($c) {
            return array(
                "ref"=>$c['properties']['name'],
                "initid"=>$c['properties']['name'],
                "image_url"=>$c['properties']['icon'],
                "html_label"=>$c['properties']['title']
            );
        };
        $return['collections'] = array_map($getCollectionInfo, $return['documents']);
        return $return;
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
        $exception->setHttpStatus(
            "405", "You cannot update"
        );
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
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus(
            "405", "You cannot delete"
        );
        throw $exception;
    }

    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $this->_searchDoc->fromid = -1;
        $families = json_decode(\ApplicationParameterManager::getParameterValue("BUSINESS_APP", "SAMPLE_CONFIG"))->showcase_families;
        $this->_searchDoc->addFilter(sprintf("name IN ('%s')", implode("','", $families)));

    }
}