<?php

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Components\Grid\Routes\GridContent;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use SearchDoc;

/**
 * Get Profiles
 *
 * @note Used by route : GET api/v2/devel/security/elements/
 */
class Elements extends GridContent
{

    protected function parseUrlArgs($urlArgs = array())
    {
        $this->smartElementId = 0;
    }


    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new SearchDoc();
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }
}
