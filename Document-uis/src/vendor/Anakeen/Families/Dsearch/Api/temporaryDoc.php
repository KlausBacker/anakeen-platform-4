<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 04/06/15
 * Time: 12:53
 */
namespace Dcp\Search\html5;

use Dcp\HttpApi\V1\Crud\Exception;
use Dcp\HttpApi\V1\DocManager\DocManager;
use \Dcp\HttpApi\V1\DocManager\Exception as DocManagerException;

class temporaryDoc extends \Dcp\HttpApi\V1\Crud\FamilyDocumentCollection
{


    public function create()
    {

        try {
            $this->_document = DocManager::createTemporaryDocument($this->_family->id);
        }
        catch(DocManagerException $exception) {
            if ($exception->getDcpCode() === "APIDM0003") {
                $exception = new Exception("API0204", $this->_family->name);
                $exception->setHttpStatus(403, "Forbidden");
                throw $exception;
            } else {
                throw $exception;
            }
        }

        $newValues = $this->contentParameters;
        foreach ($newValues as $attrid => $value) {
            $err = $this->_document->setValue($attrid, $value);
            if ($err) {
                throw new Exception("CRUD0205", $this->_family->name, $attrid, $err);
            }
        }

        $err = $this->_document->store($info);

        if ($err) {
            $exception = new Exception("CRUD0206", $this->_family->name, $err);
            $exception->setData($info);
            throw $exception;
        }
        $familyDocument = new \Dcp\HttpApi\V1\Crud\FamilyDocument();

        return $familyDocument->getInternal($this->_document);
    }


    public function read($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You can only create a temporary document");

        throw $exception;

    }


    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You can only create a temporary document");

        throw $exception;
    }


    public function delete($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405","You can only create a temporary document");

        throw $exception;
    }


}