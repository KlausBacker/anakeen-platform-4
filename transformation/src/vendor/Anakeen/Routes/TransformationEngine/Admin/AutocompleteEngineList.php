<?php


namespace Anakeen\Routes\TransformationEngine\Admin;

use Anakeen\SmartAutocomplete;

class AutocompleteEngineList
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $autocomplete = new SmartAutocomplete($request, $response);

        (new \Anakeen\TransformationEngine\Client)->retrieveEngines($engineList);

        $autoCompleteData = [];
        if ($engineList) {
            foreach ($engineList as $engine) {
                $autoCompleteData[] = array(
                    "name" => $engine["name"]
                );
            }
        }
        $autocomplete->setEntryData($this->uniqueArray($autoCompleteData, "name"));
        $autocomplete->setEntryLabel("<span>{{name}}</span>");

        return $autocomplete->getResponse();
    }

    public function uniqueArray($my_array, $key)
    {
        $result = array();
        $i = 0;
        $key_array = array();

        foreach ($my_array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $result[$i] = $val;
            }
            $i++;
        }
        return $result;
    }
}
