<?php

namespace Anakeen\SmartStructures\HubConfiguration;

use Anakeen\Core\ContextManager;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class HubConfigurationAutocomplete
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

    public static function getIcons(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $args) : SmartAutocompleteResponse
    {
        // TODO Autocomplete to display icons to the user
        return $response;
    }
}
