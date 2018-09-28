<?php

namespace Anakeen\Components\Grid\Routes;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Settings;
use Anakeen\Router\URLUtils;
use Anakeen\Ui\DataSource;

class GridContent extends DataSource
{
    protected function getData()
    {
        $data = parent::getData();
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("grid/content/%s", $this->smartElementId));

        if (ContextManager::getParameterValue("Ui",  "MODE_DEBUG")) {
            $data["debug"]=$this->_searchDoc->getSearchInfo();
        }
        $data["requestParameters"]["pager"]=array(
            "page" => intval($this->page),
            "skip" => intval($this->_searchDoc->start),
            "take" => intval($this->_searchDoc->slice),
            "pageSize" => intval($this->pageSize),
            "total" => intval($this->_searchDoc->onlyCount()),
        );


        return $data;
    }
}