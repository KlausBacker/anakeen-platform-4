<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Dcp\Core\Settings;

/**
 * Class Folders
 *
 * List all visible folders
 * @note Used by route : GET /api/v2/folders/
 * @package Anakeen\Routes\Core
 */
class Folders extends DocumentList
{
    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf("%s/folders/", Settings::ApiV2));
        return $data;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $this->_searchDoc->fromid = 2;
    }
}
