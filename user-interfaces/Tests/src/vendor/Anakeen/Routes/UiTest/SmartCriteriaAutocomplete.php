<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\SmartAutocomplete;

class SmartCriteriaAutocomplete
{
    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $autocomplete = new SmartAutocomplete($request, $response);

        // Get filter form input value
        $inputValue = $autocomplete->getFilterValue();

        // Here my data to returns
        $countries = ["France", "Italie", "Espagne"];

        $autoCompleteData = [];
        foreach ($countries as $country) {
            if (!$inputValue || preg_match(sprintf("/%s/i", preg_quote($inputValue, "/")), $country) > 0) {
                // Return only if match input
                $autoCompleteData[] = [
                    "Country" => $country
                ];
            }
        }
        $autocomplete->setEntryData($autoCompleteData);
        $autocomplete->setEntryLabel("<p>{{Country}}</p>");

        return $autocomplete->getResponse();
    }
}
