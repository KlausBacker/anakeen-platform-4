<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation\HubInstanciationAutocomplete;

use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class HubInstanciationAutocomplete
{

    public static function getManifests(SmartAutoCompleteRequest $request, SmartAutocompleteResponse $response, $args): SmartAutocompleteResponse
    {
        $userValue = $request->getFilterValue();
        $manifestPath = PUBLIC_DIR . "/Anakeen/manifest";
        if (is_dir($manifestPath)) {
            $files = scandir($manifestPath);
            if ($files) {
                foreach ($files as $filename) {
                    if ($filename !== "." && $filename !== ".." && is_dir($manifestPath . "/" . $filename)) {
                        if ($userValue === "" || preg_match("/$userValue/", $filename)) {
                            $response->appendEntry($filename,
                                [
                                    $filename
                                ]);
                        }
                    }
                }
            }
        }

        return $response;
    }
}