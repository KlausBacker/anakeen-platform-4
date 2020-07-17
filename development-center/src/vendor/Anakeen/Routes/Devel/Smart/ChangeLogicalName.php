<?php


namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Router\ApiV2Response;
use Anakeen\Core\SEManager;
use Anakeen\Exception;

class ChangeLogicalName
{
    protected $initId = null;
    protected $newLogicalName = "";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withMessages($response, $this->doRequest($request));
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $data = $request->getParsedBody();
        $this->newLogicalName = $data["newLogicalName"];
        $this->initId = $args["id"];
    }

    protected function doRequest(\Slim\Http\request $request)
    {
        $smartElement = SEManager::getDocument($this->initId);
        if (!empty($smartElement)) {
            $err = $smartElement->setLogicalName($this->newLogicalName, true);
            if ($err) {
                throw new Exception($err);
            }
            return [];
        }
    }
}
