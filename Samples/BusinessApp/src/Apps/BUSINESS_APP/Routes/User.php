<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 09/10/17
 * Time: 15:57
 */

namespace Anakeen\Sample\Routes;
use Dcp\HttpApi\V1\Crud\Crud;

class User extends Crud
{
    /**
     * @var \Doc current user
     */
    protected $_userId;
    /**
     * @var string reference of current collection
     */
    protected $_userRef;

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
        try {
            $return = [];
            $return['user'] = [
                "id"=>intval(getCurrentUser()->id),
                "fid"=>intval(getCurrentUser()->fid),
                "login" => strval(getCurrentUser()->login),
                "firstName" => strval(getCurrentUser()->firstname),
                "lastName" => strval(getCurrentUser()->lastname)
            ];
            return $return;
        } catch (Exception $e)  {
            $exception = new Exception("FIXME");
            $exception->setHttpStatus(
                "404", "User not found"
            );
            throw $exception;
        }
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
            "405", "You cannot create"
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
            "405", "You cannot create"
        );
        throw $exception;
    }
}