<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 02/10/17
 * Time: 16:10
 */

namespace Anakeen\Sample\Routes;

use Dcp\HttpApi\V1\Crud\Crud;
use Anakeen\Sample\Routes\Exception;

class Collections extends Crud
{
    /**
     * @var \Doc current dcp collection
     */
    protected $_dcpCollection = null;

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
        if(null !== $this->_collectionRef)
        {
            $return['data'] = $this->_collection;
        }
        return json_decode(\ApplicationParameterManager::getParameterValue("BUSINESS_APP", "SAMPLE_CONFIG"));
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

}