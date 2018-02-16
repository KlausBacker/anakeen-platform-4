<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\Exception;
use Dcp\Core\ContextManager;

/**
 * Class WelcomePage
 *
 * Welcome Page
 *
 * @note    Used by route : GET /
 * @package Anakeen\Routes\Core
 */
class LayoutAsset
{

    /**
     * Return all visible documents
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $ref = $args["asset"];

        if (!preg_match("/\\.(css|js)$/", $ref, $reg)) {
            throw new Exception(sprintf("Ref \"%s\" not an asset", $ref));
        }
        $assetType = $reg[1];

        if (preg_match("/([A-Z_0-9-]+):([^:]+):{0,1}[A-Z]{0,1}/", $ref, $reg)) {
            $lfile = getLayoutFile($reg[1], strtolower($reg[2]));
            if (file_exists($lfile)) {
                $response->write(file_get_contents($lfile));
            } else {
                header(sprintf("HTTP/1.1 404 ref [%s] not found", $ref));
                $response=$response->withStatus(404, sprintf("Ref [%s] not found", $ref));
            }
        } else {
            throw new Exception(sprintf("Ref \"%s\" not an valid reference", $ref));
        }


        switch ($assetType) {
            case "css":
                $response=$response->withHeader("content-type", "text/css");
                break;
            case "js":
                $response=$response->withHeader("content-type", "application/javascript");
                break;
        }

        return $response;
    }
}
