<?php

namespace Anakeen\Routes\Admin\Account\User;

use Anakeen\SmartElementManager;

class Disable
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        /* @var $smartElement \SmartStructure\Iuser */
        $smartElement = SmartElementManager::getDocument($args["userSmartId"]);
        $err = $smartElement->deactivateAccount();
        if ($err) {
            return $response->withStatus(500, $err)->write($err);
        }
        return $response->withStatus(200);
    }

}