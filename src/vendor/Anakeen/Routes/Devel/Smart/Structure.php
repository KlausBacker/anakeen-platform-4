<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Router\URLUtils;
use Anakeen\Routes\Core\Lib\DocumentDataFormatter;
use Anakeen\Script\ApiUsage;

/**
 * Get Structure info
 *
 * @note Used by route : GET /api/v2/devel/smart/structures/{structure}
 */
class Structure
{
    protected $structureName="";

    /**
     * @var SmartStructure $structure
     */
    protected $structure=null;
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->structureName = $args["structure"];
        $this->structure= SEManager::getFamily($this->structureName);
        if (empty($this->structure)) {
            $exception = new Exception("DEV0101");
            $exception->setHttpStatus(404, "Structure not found");
            throw $exception;
        }
    }

    public function doRequest()
    {
        $data = [];
        $df = new DocumentDataFormatter($this->structure);
        $df->useDefaultProperties();
        $df->addProperty("profid");
        $data = $df->getData();
        $data["properties"]["cprofid"] = $this->structure->cprofid;
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("devel/smart/structures/%s", $this->structureName));
        return [
            "document" => $data
        ];
    }
}
