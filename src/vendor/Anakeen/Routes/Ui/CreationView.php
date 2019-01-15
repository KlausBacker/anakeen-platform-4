<?php


namespace Anakeen\Routes\Ui;

use Anakeen\Routes\Core\FamilyDocumentCreation;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;

/**
 * Class CreationView
 * @note    Used by route : POST /api/v2/family/{family}/documentsViews/
 * @package Anakeen\Routes\Ui
 */
class CreationView
{
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $_family = null;

    /**
     * Create new document
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     * @return mixed
     * @throws Exception
     * @throws \Dcp\Core\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $familyId = $args["family"];

        $this->_family = SEManager::getFamily($familyId);
        if (!$this->_family) {
            $exception = new Exception("CRUD0200", $familyId);
            $exception->setHttpStatus("404", "Family not found");
            throw $exception;
        }
        $crud = new FamilyDocumentCreation();
        $document = $crud->create($request, $this->_family, $messages);

        $view = new DocumentView();

        $args["docid"] = $document->initid;
        $args["view"] = DocumentView::defaultViewEditionId;
        $response = $view->__invoke($request, $response, $args);
        $response = $response->withStatus(201);
        return $response;
    }
}
