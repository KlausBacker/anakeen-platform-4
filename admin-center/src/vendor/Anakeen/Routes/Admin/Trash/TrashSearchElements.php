<?php


namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Search\SearchElements;

class TrashSearchElements extends SearchElements
{
    public function __construct($structureName = 0)
    {
        parent::__construct($structureName);
        $this->searchData = new TrashSearchSmartData();
        $this->searchData->setObjectReturn(true);
    }
}
