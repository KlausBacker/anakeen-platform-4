<?php

namespace Dcp\Ui\Crud;

use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\Crud\DocumentUtils;
use Dcp\HttpApi\V1\Crud\Exception as HttpException;
use Dcp\HttpApi\V1\Crud\FamilyDocumentCollection;

class CreationView extends Crud {

    /**
     * Create new ressource
     * @return mixed
     */
    public function create()
    {
        $crud = new FamilyDocumentCollection();
        $crud->setUrlParameters($this->urlParameters);
        $crud->setContentParameters($this->contentParameters);
        $document = $crud->create();
        $id = $document["document"]["properties"]["initid"];
        $view = new View();
        $view->setUrlParameters(array("identifier" => $id, "viewIdentifier" => View::defaultViewEditionId));
        return $view->read($id);
    }

    /**
     * Read a ressource
     * @param string|int $resourceId Resource identifier
     * @throws HttpException
     * @return mixed
     */
    public function read($resourceId)
    {
        $exception = new HttpException("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot read a view with families API");
        throw $exception;
    }

    /**
     * Update the ressource
     * @param string|int $resourceId Resource identifier
     * @throws HttpException
     * @return mixed
     */
    public function update($resourceId)
    {
        $exception = new HttpException("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update a view with families API");
        throw $exception;
    }

    /**
     * Delete ressource
     * @param string|int $resourceId Resource identifier
     * @throws HttpException
     * @return mixed
     */
    public function delete($resourceId)
    {
        $exception = new HttpException("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete a view with families API");
        throw $exception;
    }

    public function analyseJSON($jsonString)
    {
        $values = DocumentUtils::analyzeDocumentJSON($jsonString);
        return $values;
    }
}