<?php

namespace Anakeen\Routes\Devel\Security;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;

/**
 * Get Right Accesses
 *
 * @note Used by route : GET api/v2/devel/security/profile/{id}/accesses/
 */
class Profile
{
    protected $documentId;
    /**
     * @var SmartElement
     */
    protected $_document;
    protected $completeGroup = false;
    protected $completeRole = false;
    protected $onlyAcl = false;

    /**
     * Return right accesses for a profil element
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->documentId = $args["id"];
        $this->setDocument($this->documentId);
        $this->onlyAcl = ($request->getQueryParam("acls") === "only");
        $this->completeGroup = ($request->getQueryParam("group") === "all");
        $this->completeRole = ($request->getQueryParam("role") === "all");
    }


    public function doRequest()
    {
        $data["properties"] = ProfileUtils::getProperties($this->_document);
        if ($this->onlyAcl === false) {
            $data["accesses"] = ProfileUtils::getGreenAccesses($this->_document);

            if ($this->completeGroup) {
                // add all groups in response even they has no accesses
                ProfileUtils::completeGroupAccess($data["accesses"]);
            }
            if ($this->completeRole) {
                // add all roles in response even they has no accesses
                ProfileUtils::completeRoleAccess($data["accesses"]);
            }

            ProfileUtils::getGreyAccesses($data["accesses"], $this->_document);
        }
        return $data;
    }



    /**
     * Find the current document and set it in the internal options
     *
     * @param $resourceId
     *
     * @throws Exception
     * @throws \Anakeen\Core\DocManager\Exception
     */
    protected function setDocument($resourceId)
    {
        $this->_document = SmartElementManager::getDocument($resourceId);
        if (!$this->_document) {
            $exception = new Exception("ROUTES0100", $resourceId);
            $exception->setHttpStatus("404", "Element not found");
            throw $exception;
        }
        if ($this->_document->defDoctype !== "P") {
            if ($this->_document->id !== $this->_document->profid) {
                throw new Exception("DEV0100", $resourceId);
            }
        }
    }
}
