<?php

namespace Dcp\Routes;

use Dcp\Exception;
use Dcp\HttpApi\V1\Api\RecordReturnMessage;
use Dcp\HttpApi\V1\Crud\Response;

class Document
{

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     */
    public static function get(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        error_log(__METHOD__);
        $docid = $args["docid"];

        $crudCall=function (&$crudObject) use ($docid) {
            $crudObject = new \Dcp\HttpApi\V1\Crud\Document();
            Response::initRequest($crudObject);
            return $crudObject->read($docid);
        };

        $response = Response::withCrud($request, $response, $crudCall);

        return $response;
    }

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     */
    public static function put(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $docid = $args["docid"];

        $crudCall=function (&$crudObject) use ($docid) {
            $crudObject = new \Dcp\HttpApi\V1\Crud\Document();
            Response::initRequest($crudObject);
            return $crudObject->update($docid);
        };

        $response = Response::withCrud($request, $response, $crudCall);
        return $response;
    }
}
