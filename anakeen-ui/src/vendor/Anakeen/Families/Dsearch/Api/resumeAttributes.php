<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 10/07/15
 * Time: 09:18
 */

namespace Dcp\Search\html5;


use Dcp\HttpApi\V1\Crud\DocumentCollection;
use Dcp\HttpApi\V1\Crud\Exception;

class resumeAttributes extends DocumentCollection
{
    protected $_collection = null;

    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You cannot create");

        throw $exception;
    }


    public function read($resourceId)
    {
        $fdoc = \Dcp\HttpApi\V1\DocManager\DocManager::getFamily($resourceId);
        if (!$fdoc) {
            $exception = new Exception("CRUD0103", __METHOD__);
            $exception->setHttpStatus("404", "Family not found");

            throw $exception;
        }

        $return = array();

        $return[] = array("id" => "title","withIcon" => "true");
        foreach ($fdoc->getAbstractAttributes() as $myAttribute) {
            $return[] = array("id" => $myAttribute->id);
        }
        $return[] = array("type" => "openDoc");

        return $return;
    }


    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You cannot update");

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
        $this->_searchDoc = new \SearchDoc("",-1);

        $this->_searchDoc->setObjectReturn();


    }
}