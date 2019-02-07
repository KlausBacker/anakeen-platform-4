<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\Ui\UIGetAssetPath;

class FontAwesomeIcon
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $iconsCSSContent = file_get_contents(PUBLIC_DIR."/".UIGetAssetPath::getCssBootstrap());
        $regex = "/\.fa-([a-z0-9\-]+):before/";
        preg_match_all($regex, $iconsCSSContent, $matches);
        return ApiV2Response::withData($response, $matches[1]);
    }
}
