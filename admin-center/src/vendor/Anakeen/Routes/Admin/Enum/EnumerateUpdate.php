<?php


namespace Anakeen\Routes\Admin\Enum;

use Anakeen\Core\DbManager;
use Anakeen\Core\EnumManager;
use Anakeen\Router\ApiV2Response;
use String\sprintf;

class EnumerateUpdate
{
    private $id = '';
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->parseParams($request, $args);
        return ApiV2Response::withData($response, $this->doRequest($request));
    }

    protected function doRequest(\Slim\Http\request $request)
    {
        $modifications = $request->getParsedBody()["data"];

        foreach ($modifications as $updateData) {
            $key = $updateData["key"];
            $label = $updateData["label"];
            $enabled = $updateData["enabled"];
            $type = $updateData["type"];
            switch ($type) {
                case "add":
                    $this->addEnum($key, $label);
                    break;
                case "update":
                    $this->updateEnum($updateData);
                    break;
                case "enable":
                    $this->enableEnum($updateData);
                    break;
            }
        }
    }

    private function parseParams(\Slim\Http\request $request, $args)
    {
        $this->id = $args["id"];
    }

    private function addEnum($key, $label)
    {
        EnumManager::addEnum($this->id, $key, $label);
    }

    private function updateEnum($data)
    {
        error_log("UPDATE ENUM");
    }

    private function enableEnum($data)
    {
        error_log("ENABLE ENUM");
    }
}
