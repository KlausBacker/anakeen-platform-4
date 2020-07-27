<?php


namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\Core\ContextManager;
use Anakeen\Core\VaultManager;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\TransformationEngine\Client;
use Anakeen\TransformationEngine\Manager;

define("PREGEXPFILE", "/(?P<mime>[^\|]*)\|(?P<vid>[0-9]*)\|?(?P<name>.*)?/");

class CheckUnitTransformation
{
    const maxStep = 6;
    const url = "/api/admin/transformationengine/check/unit-transformation/";
    const CheckId = "CheckTeConfigurationId";
    const CheckTaskId = "CheckTeTaskId";
    protected $config;
    protected $step;
    protected $inFile = null;
    protected $engine = null;
    protected $clientTe;
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $body = $request->getParsedBody();
        $this->step = intval($args["step"]);
        $this->inFile = $body["file"];
        $this->engine = $body["engine"];
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    protected function doRequest()
    {
        $data = [];
        $sendFile = null;
        switch ($this->step) {
            case 0:
                // Return step numbers
                // Next step is connecting
                $data["progressText"] = sprintf(
                    "Connecting to server \"%s:%s\"",
                    ContextManager::getParameterValue("TE", "TE_HOST"),
                    ContextManager::getParameterValue("TE", "TE_PORT")
                );

                $this->initTe();
                break;
            case 1:
                // Connection the server
                $info = $this->connectServer();
                // Next step is to send file

                $data["message"] = sprintf("Server reached");
                $data["progressText"] = sprintf("Send file to the server");
                $data["version"] = sprintf("%s-%s", $info["version"], $info["release"]);
                break;
            case 2:
                // Send file to the server
                $sendFile = $this->sendTask();
                $data["message"] = sprintf("Create new task with tid '%s'.", $sendFile['tid']);
                $data["tid"] = $sendFile["tid"];
                // Next Step waiting
                $data["progressText"] = sprintf("Waiting for server to complete conversion");
                break;
            case 3:
                // Waiting conversion done
                usleep(500000);
                $info = $this->getTaskInfo();

                $status = $info["status"];
                $data["message"] = sprintf("Status \"%s\"", $status);
                if ($status === Client::TASK_STATE_ERROR) {
                    $this->step = 6;
                    $data["progressText"] = sprintf("Conversion failed");
                    $data["failed"] = Client::TASK_STATE_ERROR;
                }
                if ($status === Client::TASK_STATE_SUCCESS) {
                    $this->step = 6;
                    $data["status"] = Client::TASK_STATE_SUCCESS;
                    $data["progressText"] = sprintf("Conversion succeeded");
                }
                if ($status !== Client::TASK_STATE_SUCCESS && $status !== Client::TASK_STATE_ERROR) {
                    $this->step--;
                    $data["progressText"] = sprintf("Waiting for server to complete conversion");
                }

                break;
            case 4:
                // Waiting callback done
                $data["progressText"] = sprintf("Waiting for callback request");

                $info = $this->getTaskInfo();
                $returns = $info["callreturn"];
                if ($returns) {
                    $parseReturn = json_decode($returns, true);
                    if (!$parseReturn || !isset($parseReturn["data"]["key"])) {
                        throw new Exception(sprintf("Unexpected callback returns : %s", $returns));
                    }

                    $tid = ContextManager::getSession()->read(self::CheckId);
                    if ($parseReturn["data"]["key"] !== $tid) {
                        throw new Exception(sprintf(
                            "Not the good key expected \"%s\" has \"%s\"",
                            $tid,
                            $parseReturn["data"]["key"]
                        ));
                    }
                    $data["progressText"] = sprintf("Deleting conversion results");
                    $data["message"] = "Callback received";
                } else {
                    $this->step--;
                }

                break;
            case 5:
                $data["progressText"] = sprintf("Conversion succeeded");
                break;
        }

        if ($this->step < self::maxStep) {
            $data["nextStepUrl"] = self::url . ($this->step + 1);
        };
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
        $key = rand(0, 1000);
        ContextManager::getSession()->register(self::CheckId, $key);

        $callback = sprintf("/api/admin/transformationengine/tests/%d", $key);
        $callurl = Manager::getOpenTeUrl($callback);

        $this->clientTe = new Client(
            ContextManager::getParameterValue("TE", "TE_HOST"),
            ContextManager::getParameterValue("TE", "TE_PORT")
        );
        $fkey = sprintf("unit-transformation-%s", uniqid());
        $info = array();
        if (preg_match(PREGEXPFILE, $this->inFile["value"], $reg)) {
            $vid = $reg[2];
            $filePath = VaultManager::getFileInfo($vid)->path;
            if (file_exists($filePath)) {
                $err = $this->clientTe->sendTransformation(
                    $this->engine,
                    $fkey,
                    $filePath,
                    $callurl,
                    $info
                );
                if ($err != '') {
                    throw new Exception(sprintf("sendTransformation() returned with error : %s", $err));
                }
            } else {
                throw new Exception(sprintf("file %s does not exist.", $this->inFile["fileName"]));
            }
        }
        ContextManager::getSession()->register(self::CheckTaskId, $info['tid']);
        return $info;
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
}
