<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Router\ApiV2Response;

/**
 * Class Autocomplete
 * @note Used by route : GET /ui/components/ank-components
 * @package Anakeen\Routes\Ui
 */
class AnkComponents
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $ie11 = false;
        // Check IE 11 browser
        $userAgent = $request->getHeader('HTTP_USER_AGENT')[0];
        $ie11Regex = '/Trident\/7\.0;/';
        if (preg_match($ie11Regex, $userAgent)) {
            $ie11 = true;
        }
        $componentsPath = PUBLIC_DIR.\Dcp\Ui\UIGetAssetPath::getSmartWebComponentsPath(true, $ie11);
        $filename = basename($componentsPath);
        $fileResponse = $response;
        if (!ApiV2Response::matchEtag($request, $filename)) {
            $fileResponse = ApiV2Response::withFile($fileResponse, $componentsPath, $filename, true);
        }


        return ApiV2Response::withEtag($request, $fileResponse, $filename);
    }
}
