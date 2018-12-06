<?php


namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\DestroySmartStructure;
use Anakeen\Router\ApiV2Response;

class DeleteSmartStructure
{
    protected $name = null;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withMessages($response, $this->doRequest($request));
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->name = $request->getParam("name");
    }

    protected function doRequest(\Slim\Http\request $request)
    {
        $smartStructureId = SEManager::getIdFromName($this->name);
        return DestroySmartStructure::destroyFamily($smartStructureId);
    }
}
