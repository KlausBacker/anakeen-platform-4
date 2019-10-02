<?php


namespace Anakeen\Routes\Admin\Enum;

use Anakeen\Core\DbManager;
use Anakeen\Core\EnumManager;
use Anakeen\Router\ApiV2Response;
use String\sprintf;

class EnumerateData
{
    private $id = '';
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->parseParams($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function doRequest()
    {
        $data = EnumManager::getEnums($this->id);
        $result = array();

        foreach ($data as $enumEntry) {
            $temp["key"] = $enumEntry["key"];
            $temp["label"] = $enumEntry["label"];
            array_push($result, $temp);
        }
        
        return $result;
    }

    private function parseParams(\Slim\Http\request $request, $args)
    {
        $this->id = $args["id"];
    }
}
