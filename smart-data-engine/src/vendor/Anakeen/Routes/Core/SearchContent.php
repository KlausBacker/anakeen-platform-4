<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Router\Exception;
use Anakeen\Core\Settings;
use Anakeen\SmartElementManager;
use Anakeen\SmartStructures\Search\SearchHooks;

/**
 * Class SearchContent
 *
 * List document find by a search
 *
 * @note    Used by route : GET /api/v2/searches/{search}/smart-elements/
 * @package Anakeen\Routes\Core
 */
class SearchContent extends DocumentList
{
    /**
     * @var SearchHooks
     */
    protected $_search = null;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $folderId = $args["search"];

        $this->_search = SmartElementManager::getDocument($folderId);
        if (!$this->_search) {
            $exception = new Exception("ROUTES0105", $folderId);
            $exception->setHttpStatus("404", "Folder not found");
            $exception->setUserMessage(sprintf(___("Family \"%s\" not found", "ank"), $folderId));
            throw $exception;
        }
        if ($this->_search->defDoctype !== "S") {
            $exception = new Exception("ROUTES0126", $folderId);
            $exception->setHttpStatus("404", "Not a search");
            throw $exception;
        }

        if ($this->_search->control("execute")) {
            $exception = new Exception("CRUD0201", $folderId, "Exec not granted");
            $exception->setHttpStatus("403", "Cannot exec search");
            throw $exception;
        }
    }

    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf(
            "%s/searches/%s/smart-elements/",
            Settings::ApiV2,
            $this->_search->name
        ));

        if (!$this->_search->control("view")) {
            $data["properties"] = $this->getProperties();
        }

        return $data;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $this->_searchDoc->useCollection($this->_search->id);
    }

    protected function getProperties()
    {
        return [
            "initid" => $this->_search->id,
            "title" => $this->_search->getTitle()
        ];
    }
}
