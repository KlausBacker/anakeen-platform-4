<?php

namespace Anakeen\Routes\TransformationEngine;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Anakeen\TransformationEngine\Client;

/**
 *
 * @use     by route /api/transformationengine/test/{id}
 */
class TestCallback
{
    protected $idfile;
    protected $taskid;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->idfile = $args["id"];
        $this->taskid = $request->getQueryParam("tid");
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    protected function doRequest()
    {
        $data = [];
        if (!$this->taskid) {
            throw new Exception("No task identifier found");
        } else {
            $te=new Client();
            $err=$te->getInfo($this->taskid, $info);
            if ($err) {
                throw new Exception($err);
            }

            $resultFile = sprintf("%s/%s", ContextManager::getTmpDir(), $this->idfile);
            if (!file_put_contents($resultFile, json_encode($info, JSON_PRETTY_PRINT))) {
                throw new Exception("Cannot write $resultFile");
            }
        }
        $data["file"]=$resultFile;
        return $data;
    }
}
