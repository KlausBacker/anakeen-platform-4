<?php

namespace Anakeen\Hub\SmartStructures\HubInstanciation\HubInstanciationAutocompletion;

use Anakeen\Core\ContextManager;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class HubInstanciationAutocompletion
{
    public static function getLanguages(SmartAutoCompleteRequest $request, SmartAutocompleteResponse $response, $args) : SmartAutocompleteResponse
    {
        $filter = preg_quote($request->getFilterValue(), "/");
        $locales = ContextManager::getLocales();

        foreach ($locales as $locale) {
            if (($filter == "") || (preg_match("/$filter/i", $locale["label"], $m))) {
                $response->appendEntry(
                    $locale["label"],
                    [
                        $locale["label"],
                        $locale["culture"]
                    ]
                );
            }
        }

        return $response;
    }
}