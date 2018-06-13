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
        $componentsPath = PUBLIC_DIR.\Dcp\Ui\UIGetAssetPath::getSmartWebComponentsPath();
        $filename = basename($componentsPath);
        $fileResponse = $response;
        if (!ApiV2Response::matchEtag($request, $filename)) {
            $fileResponse = ApiV2Response::withFile($fileResponse, $componentsPath, $filename, true);
        }

        return ApiV2Response::withEtag($request, $fileResponse, $filename);
    }
}
