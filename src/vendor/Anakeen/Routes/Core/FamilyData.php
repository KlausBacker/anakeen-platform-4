<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;

/**
 * Class FamilyData
 *
 * @note Used by route : GET /api/v2/families/{family}
 * @package Anakeen\Routes\Core
 */
class FamilyData extends DocumentData
{
    /**
     * @var \Anakeen\Core\SmartStructure 
     */
    protected $_family = null;
    /**
     * Get ressource
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $famName=$args["family"];

        $this->_family = SEManager::getFamily($famName);
        if (!$this->_family) {
            $exception = new Exception("ROUTES0105", $famName);
            $exception->setHttpStatus("404", "Family not found");
            $exception->setUserMessage(sprintf(___("Family \"%s\" not found", "ank"), $famName));
            throw $exception;
        }


        return parent::__invoke($request, $response, ["docid"=>$this->_family->id]);
    }
}
