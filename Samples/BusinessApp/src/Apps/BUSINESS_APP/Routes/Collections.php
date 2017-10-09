<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 02/10/17
 * Time: 16:10
 */

namespace Anakeen\Sample\Routes;

use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\DocManager\DocManager;
use Anakeen\Sample\Routes\Exception;

class Collections extends Crud
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
        $return = [];
        if(null !== $this->_collectionRef)
        {
            $return['sample'] = $this->_collection;
        } else {
            $return['sample'] = json_decode(\ApplicationParameterManager::getParameterValue("BUSINESS_APP", "SAMPLE_CONFIG"));
        }
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

    public function setUrlParameters(array $parameters)
    {
        parent::setUrlParameters($parameters);
        if (isset($this->urlParameters['collectionRef'])) {
            $this->_collectionRef = $this->urlParameters['collectionRef'];
            $collections = json_decode(\ApplicationParameterManager::getParameterValue('BUSINESS_APP', 'SAMPLE_CONFIG'), TRUE);
            if (isset($collections['collections'])) {
                foreach ($collections['collections'] as $collection) {
                    if ($collection['ref'] === $this->_collectionRef) {
                        $this->_collection = $collection;
                        break;
                    }
                }
                if (null !== $this->_collection) {
                    $this->_apCollection = DocManager::getDocument($this->_collection['initid']);
                }
                if ((null === $this->_apCollection) || ('' !== $this->_apCollection->control('open'))) {
                    //FIXME: error message when collection does not exists
                    $exception = new Exception("FIXME");
                    $exception->setHttpStatus("404", "collection $this->_collectionRef does not exists.");
                    throw $exception;
                }
            } else {
                $exception = new Exception("FIXME");
                $exception->setHttpStatus("404", "collections not found");
                throw $exception;
            }
        }
    }

}