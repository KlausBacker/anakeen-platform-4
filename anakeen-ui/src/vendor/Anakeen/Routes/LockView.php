<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Router\Exception;
use Dcp\Core\ContextManager;
use Dcp\Core\DocManager;

/**
 * Class LockView
 * @note    Used by route : GET /api/v2/documents/{docid}/views/{view}/locks/{lockType}
 * @package Anakeen\Routes\Ui
 */
class LockView extends \Anakeen\Routes\Core\DocumentLock
{
    protected $viewId = "!defaultConsultation";
    protected $lockType = "temporary";
    protected $temporaryLock = true;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->viewId = $args["view"];
        return parent::__invoke($request, $response, $args);
    }

    /**
     * Add temporary
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @return mixed
     */
    public function create()
    {
        if ($this->needLock()) {
            $err = $this->_document->lock($this->temporaryLock);

            if ($err) {
                $exception = new Exception("CRUD0231", $err);
                $exception->setHttpStatus(403, "Forbidden");
                throw $exception;
            }
            return $this->getLockInfo();
        } else {
            //$this->setHttpStatus("200 Lock not necessary");
        }
        return "";
    }

    protected function needLock()
    {

        if ($this->lockType === "temporary" && ContextManager::getCurrentUser()->id == 1) {
            // Admin cannot use temporary locks
            return false;
        }
        if (in_array($this->viewId, array(
            DocumentView::coreViewConsultationId,
            DocumentView::defaultViewConsultationId,
            DocumentView::defaultViewCreationId,
            DocumentView::coreViewCreationId
        ))) {
            return false;
        }
        if (in_array($this->viewId, array(
            DocumentView::defaultViewEditionId,
            DocumentView::coreViewEditionId
        ))) {
            return true;
        }
        if (!$this->_document->cvid) {
            $exception = new \Dcp\UI\Exception("CRUDUI0011", $this->viewId);
            throw $exception;
        }
        /**
         * @var \Dcp\Family\CVDoc $cv
         */
        $cv = \Dcp\HttpApi\V1\DocManager\DocManager::getDocument($this->_document->cvid);
        $cv->set($this->_document);
        $viewInfo = $cv->getView($this->viewId);
        if (!$viewInfo) {
            $exception = new Exception("CRUDUI0012", $this->viewId, $cv->getTitle());
            $exception->setHttpStatus(404, "Undefined view");
            throw $exception;
        }

        if ($cv->control($this->viewId) !== "") {
            $exception = new Exception("CRUDUI0013", $this->viewId, $cv->getTitle());
            $exception->setHttpStatus(403, "Forbidden view");
            throw $exception;
        }
        return $viewInfo["cv_kview"] === "VEDIT";
    }

    /**
     * Set the family of the current request
     *
     * @param array $array
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     */
    public function setUrlParameters(array $array)
    {
        parent::setUrlParameters($array);
        if (isset($this->urlParameters["viewIdentifier"])) {
            $this->viewId = $this->urlParameters["viewIdentifier"];
        }
    }
}
