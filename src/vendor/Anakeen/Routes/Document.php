<?php

namespace Dcp\Routes;
class Document
{

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public static function get(\Slim\Http\request $request, \Slim\Http\response $response, $args): \Slim\Http\response
    {

        error_log(__METHOD__);
 return $response->withJson(["success"=>$args["docid"]]);
        // $response->write("Doc Yo");
        $docid = $args["docid"];
        //  $response = $response->withJson($this->read($docid), 200);

        $mb = microtime(true);
        $crudDocument = new \Dcp\HttpApi\V1\Crud\Document();

        $docData = $crudDocument->read($docid);

        $data = ["data" => $docData,
            "duration" => sprintf("%.04f", microtime(true) - $mb)];


        return $response->withJson($data);
    }

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public static function put(\Slim\Http\request $request, \Slim\Http\response $response, $args): \Slim\Http\response
    {

        // $response->write("Doc Yo");
        $docid = $args["docid"];
        //  $response = $response->withJson($this->read($docid), 200);

        $mb = microtime(true);
        $crudDocument = new Crud\Document();

        $docData = $crudDocument->update($docid);

        $data = ["data" => $docData,
            "duration" => sprintf("%.04f", microtime(true) - $mb)];


        return $response->withJson($data);
    }
}