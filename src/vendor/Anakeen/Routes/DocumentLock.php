<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DocManager;
use Anakeen\Router\URLUtils;
use Anakeen\Router\Exception;
use Dcp\Core\Settings;
use Anakeen\Router\ApiV2Response;

/**
 * Class Lock
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/locks/{lockType}
 * @note    Used by route : GET /api/v2/families/{family}/documents/{docid}/locks/{lockType}
 * @package Anakeen\Routes\Core
 */
class DocumentLock
{
    protected $baseURL = "documents";
    /**
     * @var \Doc
     */
    protected $_document = null;
    /**
     * @var \DocFam
     */
    protected $_family = null;

    protected $slice = -1;

    protected $offset = 0;

    protected $temporaryLock = false;
    protected $lockType = "permanent";
    protected $docid = 0;
    protected $method;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->method = $request->getMethod();

        $this->initParameters($request, $args);

        if (isset($args["family"])) {
            DocumentUtils::verifyFamily($args["family"], $this->_document);
        }

        $data = $this->doRequest();

        if ($this->method === "POST") {
            $response = $response->withStatus(201);
        }
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        if (!empty($args["lockType"])) {
            $this->lockType = $args["lockType"];
        }
        $this->temporaryLock = ($this->lockType === "temporary");
        $this->docid = $args["docid"];
        $this->setDocument($this->docid);
    }

    protected function doRequest()
    {
        $data = [];
        switch ($this->method) {
            case "GET":
                $data = $this->get();
                break;
            case "POST":
                $data = $this->create();
                break;
            case "PUT":
                $data = $this->create();
                break;
            case "DELETE":
                $data = $this->delete();
                break;
        }
        return $data;
    }

    /**
     * Create new tag ressource
     *
     * @throws Exception
     * @return mixed
     */
    public function create()
    {
        $err = $this->_document->lock($this->temporaryLock);

        if ($err) {
            $exception = new Exception("CRUD0231", $err);
            throw $exception;
        }
        return $this->getLockInfo();
    }

    /**
     * Get lock info
     *
     *
     * @return array
     */
    public function get()
    {

        return $this->getLockInfo();
    }

    /**
     * Update or create a lock
     *
     *
     * @throws Exception
     * @return array
     */
    public function update()
    {
        return $this->create();
    }

    /**
     * Delete lock
     *
     * @throws Exception
     * @return array
     */
    public function delete()
    {

        if ($this->temporaryLock && $this->_document->locked > 0) {
            $exception = new Exception("CRUD0233", $this->_document->getTitle());
            throw $exception;
        }
        if (!$this->temporaryLock && $this->hasTemporaryLock()) {
            $exception = new Exception("CRUD0234", $this->_document->getTitle());
            throw $exception;
        }

        $err = $this->_document->unlock($this->temporaryLock);

        if ($err) {
            $exception = new Exception("CRUD0232", $err);
            throw $exception;
        }

        return $this->getLockInfo();
    }

    protected function hasTemporaryLock()
    {
        return ($this->_document->locked < -1);
    }

    protected function getLockInfo()
    {
        $info = array();

        if ($this->_document->locked == -1) {
            $lock = null;
        } elseif ($this->_document->locked == 0) {
            $lock = null;
        } else {
            $lock = array(

                "lockedBy" => array(
                    "id" => abs($this->_document->locked),
                    "title" => \Anakeen\Core\Account::getDisplayName(abs($this->_document->locked))
                ),
                "isMyLock" => (abs($this->_document->locked) == ContextManager::getCurrentUser()->id),
                "temporary" => $this->hasTemporaryLock(),
                "fixed" => false
            );
        }
        $info["uri"] = URLUtils::generateURL(sprintf(
            "%sdocuments/%s/locks/%s",
            Settings::ApiV2,
            $this->_document->name ? $this->_document->name : $this->_document->initid,
            ($this->hasTemporaryLock()) ? "temporary" : "permanent"
        ));

        $info["lock"] = $lock;
        return $info;
    }

    /**
     * Find the current document and set it in the internal options
     *
     * @param $resourceId
     *
     * @throws Exception
     */
    protected function setDocument($resourceId)
    {
        $this->_document = DocManager::getDocument($resourceId);
        if (!$this->_document) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }


        if ($this->_document->doctype === "Z") {
            $exception = new Exception("CRUD0219", $resourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $exception->setURI(URLUtils::generateUrl(sprintf(
                "%s/trash/%d",
                Settings::ApiV2,
                $this->_document->initid
            )));
            throw $exception;
        }
        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $resourceId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }
    }
}
