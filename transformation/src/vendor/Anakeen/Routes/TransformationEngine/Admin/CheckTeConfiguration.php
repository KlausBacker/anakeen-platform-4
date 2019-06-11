<?php

namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Exception;
use Anakeen\TransformationEngine\Client;
use Anakeen\TransformationEngine\Manager;

/**
 *
 * @use     by route PUT /api/admin/transformationengine/check/{step}
 */
class CheckTeConfiguration
{
    const maxStep = 6;
    const url = "/api/admin/transformationengine/check/";
    const CheckId = "CheckTeConfigurationId";
    const CheckTaskId = "CheckTeTaskId";
    protected $config;
    protected $step;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->config = $request->getParsedBody();
        $this->step = intval($args["step"]);
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    protected function doRequest()
    {

        $data = [];


        foreach ($this->config as $name => $value) {
            if ($name === "TE_ACTIVATE") {
                $value = ($value === true) ? "yes" : "no";
            }
            ContextParameterManager::setVolatile("TE", $name, $value);
        }

        switch ($this->step) {
            case 0:
                // Return step numbers
                // Next step is connecting
                $data["progressText"] = sprintf("Connecting to server \"%s:%s\"", $this->config["TE_HOST"], $this->config["TE_PORT"]);

                $this->initTe();
                break;
            case 1:
                // Connection the server
                $info = $this->connectServer();
                // Next step is to send file

                $data["message"] = sprintf("Server reached");
                $data["progressText"] = sprintf("Send file to the server");
                $data["version"]=sprintf("%s-%s", $info["version"], $info["release"]);
                break;
            case 2:
                // Send file to the server
                $data["message"] = $this->sendTask();
                // Next Step waiting
                $data["progressText"] = sprintf("Waiting server has finished conversion");
                break;
            case 3:
                // Waiting conversion done
                usleep(500000);
                $info = $this->getTaskInfo();

                $status = $info["status"];
                $data["message"] = sprintf("Status \"%s\"", $status);

                if ($status !== Client::TASK_STATE_SUCCESS && $status !== Client::TASK_STATE_ERROR) {
                    $this->step--;

                    $data["progressText"] = sprintf("Waiting server has done conversion");
                } else {
                    $data["progressText"] = sprintf("Waiting callback request");
                }

                break;
            case 4:
                // Waiting callback done
                $data["progressText"] = sprintf("Waiting callback request");

                $info = $this->getTaskInfo();
                $returns = $info["callreturn"];
                if ($returns) {
                    $parseReturn = json_decode($returns, true);
                    if (!$parseReturn || !isset($parseReturn["data"]["key"])) {
                        throw new Exception(sprintf("Unexpected callback returns : %s", $returns));
                    }

                    $tid = ContextManager::getSession()->read(self::CheckId);
                    if ($parseReturn["data"]["key"] !== $tid) {
                        throw new Exception(sprintf("Not the good key expected \"%s\" has \"%s\"", $tid, $parseReturn["data"]["key"]));
                    }
                    $data["progressText"] = sprintf("Deleting conversion results");
                    $data["message"] = "Callback received";
                } else {
                    $this->step--;
                }

                break;
            case 5:
                $data["progressText"] = sprintf("Conversion succeeded");
                $this->removeTask();
                $data["message"] = "Task removed in server";
                break;
        }

        if ($this->step < self::maxStep) {
            $data["nextStepUrl"] = self::url . ($this->step + 1);
        }

        $data["stepNumber"] = $this->step + 1;
        $data["maxStep"] = self::maxStep;
        return $data;
    }

    protected function connectServer()
    {
        $err = Manager::isAccessible($info);
        if ($err) {
            $this->sendError($err);
        }
        return $info;
    }

    protected function sendError($err)
    {
        $e = new Exception("Te check error");
        $e->setUserMessage($err);
        throw $e;
    }

    protected function initTe()
    {
        $err = Manager::checkParameters();
        if ($err) {
            $this->sendError($err);
        }
    }

    protected function sendTask()
    {
        $info = array();
        $te_name = 'utf8';
        $fkey = '';
        $tmpFile = tempnam(ContextManager::getTmpDir(), '');
        if ($tmpFile === false) {
            $this->sendError("Could not create temporary file.");
        }
        if (file_put_contents($tmpFile, 'hello world.') === false) {
            $this->sendError(sprintf("Error writing content to temporary file '%s'", $tmpFile));
        }

        $key = rand(0, 1000);
        ContextManager::getSession()->register(self::CheckId, $key);

        $callback = sprintf("/api/admin/transformationengine/tests/%d", $key);
        $callurl = Manager::getOpenTeUrl($callback);

        $te = new Client();
        $err = $te->sendTransformation($te_name, $fkey, $tmpFile, $callurl, $info);
        if ($err != '') {
            unlink($tmpFile);
            $this->sendError($err);
        }
        unlink($tmpFile);

        ContextManager::getSession()->register(self::CheckTaskId, $info['tid']);
        return sprintf("Created new task with tid '%s'.", $info['tid']);
    }

    protected function getTaskInfo()
    {

        $tid = ContextManager::getSession()->read(self::CheckTaskId);
        if (!$tid) {
            throw new Exception("No task registered");
        }
        $te = new Client();
        $te->getInfo($tid, $info);
        return $info;
    }



    protected function removeTask()
    {

        $tid = ContextManager::getSession()->read(self::CheckTaskId);
        if (!$tid) {
            throw new Exception("No task registered");
        }
        $te = new Client();
        $err=$te->purgeTransformation($tid);
        if ($err) {
            throw new Exception($err);
        }
    }
}
