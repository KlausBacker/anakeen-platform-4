<?php

namespace Anakeen\Routes\Core;

use Dcp\Core\DocManager;
use Anakeen\Router\URLUtils;
use Anakeen\Router\Exception;
use Dcp\Core\Settings;

/**
 * Class FamilyDocumentList
 *
 * List all visible documents of a family
 * @note Used by route : GET /api/v2/families/{family}/documents/
 * @package Anakeen\Routes\Core
 */
class FamilyDocumentList extends DocumentList
{
    /**
     * @var \DocFam
     */
    protected $_family = null;


    /**
     * Return all visible documents
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $famName = $args["family"];
        $this->request = $request;

        $this->_family = DocManager::getFamily($famName);
        if (!$this->_family) {
            $exception = new Exception("ROUTES0105", $famName);
            $exception->setHttpStatus("404", "Family not found");
            $exception->setUserMessage(sprintf(___("Family \"%s\" not found", "ank"), $famName));
            throw $exception;
        }


        /**
         * @var \Slim\Http\response $response
         */
        return parent::__invoke($request, $response, $args);
    }

    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf("%s/families/%s/documents/", Settings::ApiV2, $this->_family->name));
        return $data;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $this->_searchDoc->fromid = $this->_family->id;
    }
}