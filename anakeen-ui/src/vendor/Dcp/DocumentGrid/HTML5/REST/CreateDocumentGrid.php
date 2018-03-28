<?php


namespace Dcp\DocumentGrid\HTML5\REST;


use Dcp\DocumentGrid2\SearchCriteria;
use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\DocManager\DocManager;

class CreateDocumentGrid extends Crud
{

    /**
     * Create new ressource
     *
     * @throws Exception
     * @return mixed
     */
    public function create()
    {
        if (!isset($this->contentParameters["collection"])
        ) {
            $exception = new Exception("You need a collection", __METHOD__);
            $exception->setHttpStatus(
                "400", "You need a collection id to create a searchDoc search"
            );
            throw $exception;
        }

        $collection= DocManager::getDocument($this->contentParameters["collection"]);
        if (!$collection) {
            throw new Exception(sprintf(___("Collection \"%s\" not found", "docgrid"),$this->contentParameters["collection"] ));
        }
        $searchDoc = new \SearchDoc();
        $searchDoc->useCollection($this->contentParameters["collection"]);

        if (isset($this->contentParameters["criterias"])) {
            $searchCriteria = new SearchCriteria($searchDoc);
            $searchCriteria->addCriterias(
                $this->contentParameters["criterias"]
            );
            $searchDoc = $searchCriteria->getSearchDoc();
        }

        $sql = $searchDoc->getOriginalQuery();

        $tmpReport = createTmpDoc("", "REPORT");
        /* @var $tmpReport \Dcp\Core\Report */
        $tmpReport->setValue("se_famid", $collection->getRawValue("se_famid"));
        $tmpReport->store();
        $tmpReport->addStaticQuery($sql);
        $tmpReport->store();
        $restDocumentGrid = new DocumentGrid();
        $restDocumentGrid->setUrlParameters(
            ["collection" => $tmpReport->id]
        );
        $restDocumentGrid->setContentParameters($_GET);
        return $restDocumentGrid->read($tmpReport->id);
    }

    /**
     * Read a ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     */
    public function read($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot read document grid");
        throw $exception;
    }

    /**
     * Update the ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @throws Exception
     * @return mixed
     */
    public function update($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update document grid");
        throw $exception;
    }

    /**
     * Delete ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @throws Exception
     * @return mixed
     */
    public function delete($resourceId)
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete document grid");
        throw $exception;
    }

}