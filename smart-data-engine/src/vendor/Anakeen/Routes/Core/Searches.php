<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Core\Settings;

/**
 * Class Searches
 *
 * List all visible folders
 * @note Used by route : GET /api/v2/searches/
 * @package Anakeen\Routes\Core
 */
class Searches extends DocumentList
{
    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf("%s/searches/", Settings::ApiV2));
        return $data;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $this->_searchDoc->fromid = 5;
    }
}
