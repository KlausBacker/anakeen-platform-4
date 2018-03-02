<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Dcp\Core\Settings;

/**
 * Class TrashContent
 *
 * List deleted documents
 * @note Used by route : GET /api/v2/trash/
 * @package Anakeen\Routes\Core
 */
class TrashContent extends DocumentList
{
    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(sprintf("%s/trash/", Settings::ApiV2));
        return $data;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();

        $this->_searchDoc->trash = "only";
    }
}
