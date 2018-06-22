<?php

namespace Anakeen\SmartStructures\Msearch;

use Anakeen\Core\SmartStructure\Autocomplete\SmartElementList;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class MSearchAutocomplete extends SmartElementList
{
    public static function getSmartSearches(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response,
        array $args
    ): SmartAutocompleteResponse {
        $args["smartstructure"] = "SEARCH";
        return self::getSmartElements($request, $response, $args);
    }

    protected static function getSearchConfig(array $args)
    {
        $s = new \SearchDoc("", $args["smartstructure"]);
        $s->addFilter("fromid=5 or fromid=16");
        return $s;
    }
}
