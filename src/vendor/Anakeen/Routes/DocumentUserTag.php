<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Dcp\Core\ContextManager;
use Dcp\Core\DocManager;
use Dcp\Core\Settings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class DocumentUserTag
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/usertags/{tag}
 * @package Anakeen\Routes\Core
 */
class DocumentUserTag
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

    protected $tagIdentifier = "";
    protected $tagValue;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $docid = $args["docid"];
        $this->tagIdentifier = $args["tag"];
        $method = $request->getMethod();

        $this->setDocument($docid);


        $data = [];
        switch ($method) {
            case "GET":
                $data = $this->read();
                break;
            case "POST":
                $this->tagValue = $this->getTabValue($request);
                $data = $this->create();
                $response = $response->withStatus(201);
                break;
            case "PUT":
                $this->tagValue = $this->getTabValue($request);
                $data = $this->update();
                break;
            case "DELETE":
                $data = $this->delete();
                break;
        }

        return ApiV2Response::withData($response, $data);
    }


    /**
     * Create new tag ressource
     *
     * @throws Exception
     * @return mixed
     */
    public function create()
    {
        $userTag = $this->_document->getUTag($this->tagIdentifier, false);

        if ($userTag) {
            $exception = new Exception("CRUD0225", $this->tagIdentifier);
            throw $exception;
        }

        $err = $this->_document->addUTag(
            ContextManager::getCurrentUser()->id,
            $this->tagIdentifier,
            $this->tagValue
        );
        if ($err) {
            $exception = new Exception("CRUD0224", $this->tagIdentifier, $err);
            throw $exception;
        }
        return $this->getUserTagInfo();
    }

    /**
     * Gettag ressource
     *
     * @throws Exception
     * @return mixed
     */
    public function read()
    {
        return $this->getUserTagInfo();
    }

    /**
     * Update or create a tag  ressource
     *
     * @throws Exception
     * @return mixed
     */
    public function update()
    {

        $err = $this->_document->addUTag(
            ContextManager::getCurrentUser()->id,
            $this->tagIdentifier,
            $this->tagValue
        );
        if ($err) {
            $exception = new Exception("CRUD0224", $this->tagIdentifier, $err);
            throw $exception;
        }
        return $this->getUserTagInfo();
    }

    /**
     * Delete user tag
     *
     * @throws Exception
     * @return mixed
     */
    public function delete()
    {
        $userTag = $this->_document->getUTag($this->tagIdentifier, false);
        if (!$userTag) {
            $exception = new Exception("CRUD0223", $this->tagIdentifier);
            throw $exception;
        }
        $err = $this->_document->delUTag(ContextManager::getCurrentUser()->id, $this->tagIdentifier);
        if ($err) {
            $exception = new Exception("CRUD0224", $this->tagIdentifier, $err);
            throw $exception;
        }
        return null;
    }

    protected function getUserTagInfo()
    {
        $info = array();

        $userTag = $this->_document->getUTag($this->tagIdentifier, false);

        if (!$userTag) {
            $exception = new Exception("CRUD0223", $this->tagIdentifier);
            $exception->setHttpStatus("404", "Not found");
            throw $exception;
        }
        /**
         * @var \DocUTag $userTag
         */

        $value = '';
        if ($userTag->comment) {
            if ($json = json_decode($userTag->comment)) {
                $value = $json;
            } else {
                $value = $userTag->comment;
            }
        }

        $tags = array(
            "id" => $userTag->tag,
            "date" => $userTag->date,
            "value" => $value
        );

        $info["uri"] = URLUtils::generateURL(sprintf(
            "%s%s/%s/usertags/%s",
            Settings::ApiV2,
            $this->baseURL,
            $this->_document->name ? $this->_document->name : $this->_document->initid,
            $userTag->tag
        ));

        $info["userTag"] = $tags;
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

        if ($this->_family && !is_a($this->_document, sprintf("\\Dcp\\Family\\%s", $this->_family->name))) {
            $exception = new Exception("CRUD0220", $resourceId, $this->_family->name);
            $exception->setHttpStatus("404", "Document is not a document of the family " . $this->_family->name);
            throw $exception;
        }

        if ($this->_document->doctype === "Z") {
            $exception = new Exception("CRUD0219", $resourceId);
            $exception->setHttpStatus("404", "Document deleted");
            $exception->setURI(DocumentUtils::getURI($this->_document));
            throw $exception;
        }
        $err = $this->_document->control("view");
        if ($err) {
            $exception = new Exception("CRUD0201", $resourceId, $err);
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }
    }


    protected function getTabValue(\Slim\Http\request $request)
    {
        $content = $request->getParsedBody();
        if ($content) {
            return json_encode($content);
        }

        $content = (string)$request->getBody();
        $content = trim($content, '"');
        return $content;
    }
}
