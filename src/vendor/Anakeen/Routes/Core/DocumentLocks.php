<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Core\Settings;

/**
 * Class Lock
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/locks/{lockType}
 * @note    Used by route : GET /api/v2/families/{family}/documents/{docid}/locks/{lockType}
 * @package Anakeen\Routes\Core
 */
class DocumentLocks extends DocumentLock
{
    protected $baseURL = "documents";
    /**
     * @var \Anakeen\Core\Internal\SmartElement 
     */
    protected $_document = null;
    /**
     * @var \Anakeen\Core\SmartStructure 
     */
    protected $_family = null;

    protected $slice = -1;

    protected $offset = 0;

    protected $temporaryLock = false;
    protected $lockType = "permanent";
    protected $docid=0;

    //region CRUD part


    protected function doRequest()
    {
        $locks = array();
        if ($this->method === "DELETE") {
            $this->delete();
        }


        if ($this->_document->locked > 0 || $this->hasTemporaryLock()) {
            $locks[] =  $this->get();
        }
        return array(
            "uri" => URLUtils::generateURL(sprintf("%s%s/%s/locks/", Settings::ApiV2, $this->baseURL, $this->_document->name ? $this->_document->name : $this->_document->initid)) ,
            "locks" => $locks
        );
    }
}
