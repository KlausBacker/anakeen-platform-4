<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Router\Exception;
use Anakeen\Core\Settings;
use Anakeen\SmartElementManager;

/**
 * Class FolderContent
 *
 * List folder content
 *
 * @note    Used by route : GET /api/v2/folders/{folder}/documents/
 * @package Anakeen\Routes\Core
 */
class FolderContent extends DocumentList
{
    /**
     * @var \Anakeen\SmartStructures\Dir\DirHooks
     */
    protected $_folder = null;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
        $folderId = $args["folder"];

        $this->_folder = SmartElementManager::getDocument($folderId);
        if (!$this->_folder) {
            $exception = new Exception("ROUTES0105", $folderId);
            $exception->setHttpStatus("404", "Folder not found");
            $exception->setUserMessage(sprintf(___("Family \"%s\" not found", "ank"), $folderId));
            throw $exception;
        }
        if ($this->_folder->defDoctype !== "D") {
            $exception = new Exception("ROUTES0125", $folderId);
            $exception->setHttpStatus("404", "Not a folder");
            throw $exception;
        }

        if ($this->_folder->control("open")) {
            $exception = new Exception("CRUD0201", $folderId, "Open not granted");
            $exception->setHttpStatus("403", "Cannot open folder");
            throw $exception;
        }
    }

    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf(
            "%s/folders/%s/documents/",
            Settings::ApiV2,
            $this->_folder->name
        ));

        if (!$this->_folder->control("view")) {
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
        $this->_searchDoc->useCollection($this->_folder->id);
    }

    protected function getProperties()
    {
        return [
            "initid" => $this->_folder->id,
            "title" => $this->_folder->getTitle()
        ];
    }
}
