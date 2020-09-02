<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\SEManager;
use Anakeen\Router\URLUtils;
use Anakeen\Router\Exception;
use Anakeen\Core\Settings;

/**
 * Class FamilyDocumentList
 *
 * List all visible documents of a family
 * @note Used by route : GET /api/v2/smart-structures/{family}/smart-elements/
 * @package Anakeen\Routes\Core
 */
class FamilyDocumentList extends DocumentList
{
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $_family = null;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $famName = $args["family"];

        $this->_family = SEManager::getFamily($famName);
        if (!$this->_family) {
            $exception = new Exception("ROUTES0105", $famName);
            $exception->setHttpStatus("404", "Family not found");
            $exception->setUserMessage(sprintf(___("Family \"%s\" not found", "ank"), $famName));
            throw $exception;
        }
    }

    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf("%s/smart-structures/%s/smart-elements/", Settings::ApiV2, $this->_family->name));
        return $data;
    }

    /**
     * Extract orderBy
     *
     * @return string
     */
    protected function extractOrderBy()
    {
        $createTmpDoc = SEManager::createTemporaryDocument($this->_family->name);
        return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($this->orderBy, $createTmpDoc);
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
