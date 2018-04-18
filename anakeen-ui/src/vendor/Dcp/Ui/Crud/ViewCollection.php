<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\Ui\Crud;

use Dcp\HttpApi\V1\Crud\Exception;
use Anakeen\Core\DocManager as DocManager;

class ViewCollection extends View
{
    /**
     * Create new ressource
     * @throws Exception
     * @return mixed
     */
    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create a view list with the API");
        throw $exception;
    }
    /**
     * Read a ressource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     */
    public function read($resourceId)
    {
        $document = $this->getDocument($resourceId);
        $info = array();
        if ($document->cvid) {
            $info = $this->getViews($document);
        }
        $info = array_merge($info, array_values($this->getCoreViews($document)));
        return array(
            "views" => $info
        );
    }
    
    protected function getViews(\Anakeen\Core\Internal\SmartElement $document)
    {
        $cv = DocManager::getDocument($document->cvid);
        if ($cv === null) {
            throw new Exception("CRUDUI0006", $document->cvid, $document->getTitle());
        }
        DocManager::cache()->addDocument($cv);
        /**
         * @var \SmartStructure\Cvdoc $cv
         */
        $cv->set($document);
        $views = $cv->getViews();
        $info = array();
        foreach ($views as $view) {
            if ($cv->isValidView($view, true)) {
                $vid = $view[\SmartStructure\Attributes\Cvdoc::cv_idview];
                if ($cv->control($vid) == "") {
                    $prop = $this->getViewProperties($cv, $view);
                    $prop["uri"] = $this->getUri($document, $vid);
                    $info[] = array(
                        "properties" => $prop
                    );
                }
            }
        }
        return $info;
    }
    /**
     * Update the ressource
     * @param string|int $resourceId Resource identifier
     * @throws Exception
     * @return mixed
     */
    public function update($resourceId)
    {
        
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update a view list with the API");
        throw $exception;
    }
    /**
     * Delete ressource
     * @param string|int $resourceId Resource identifier
     * @throws Exception
     * @return mixed
     */
    public function delete($resourceId)
    {
        
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete a view list with the API");
        throw $exception;
    }
}
