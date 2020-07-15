<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\SmartAutocomplete;
use Anakeen\SmartElementManager;

class SmartCriteriaComplexAutocomplete
{
    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $autocomplete = new SmartAutocomplete($request, $response);

        // Get filter form input value
        $inputValue = $autocomplete->getFilterValue();

        // Here my data to returns
        $countries = ["France", "Italie", "Espagne"];
        $inputGroup = $autocomplete->getInputValue("extraEntry");
        if (!empty($inputGroup)) {
            if (is_array($inputGroup)) {
                foreach ($inputGroup as $input) {
                    $doc = SmartElementManager::getDocument($input);
                    array_push($countries, $doc->title);
                }
            } else {
                array_push($countries, $inputGroup);
            }
        }

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


