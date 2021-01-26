<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\Exception;
use Anakeen\Router\URLUtils;
use Anakeen\Core\Settings;

/**
 * Class Lock
 *
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/locks/{lockType}
 * @note    Used by route : GET /api/v2/smart-structures/{family}/smart-elements/{docid}/locks/{lockType}
 * @package Anakeen\Routes\Core
 */
class DocumentLocks extends DocumentLock
{
    protected $baseURL = "smart-elements";
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
    protected $lockType = "all";
    protected $docid = 0;

    //region CRUD part


    protected function doRequest()
    {
        if ($this->method === "DELETE") {
            $this->delete();
        }

        $locksInfo = $this->getLockInfo();
        $locksInfo["uri"] = URLUtils::generateURL(sprintf(
            "%s%s/%s/locks/",
            Settings::ApiV2,
            $this->baseURL,
            $this->_document->name ? $this->_document->name : $this->_document->initid
        ));
        return $locksInfo;
    }

    public function delete()
    {
        $err = $this->_document->unlock($this->temporaryLock);

        if ($err) {
            throw new Exception("CRUD0232", $err);
        }

        return $this->getLockInfo();
    }
}
