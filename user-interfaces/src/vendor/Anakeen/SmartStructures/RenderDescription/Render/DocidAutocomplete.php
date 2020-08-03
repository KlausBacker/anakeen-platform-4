<?php

namespace Anakeen\SmartStructures\RenderDescription\Render;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class DocidAutocomplete
{
    public static function getExamples(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response,
        array $args
    ): SmartAutocompleteResponse {
        $famid = $args["structure"];

        $args["smartstructure"] =  $args["structure"];
        $response = \Anakeen\Core\SmartStructure\Autocomplete\SmartElementList::getSmartElements($request, $response, $args);


        return $response;
    }


}
