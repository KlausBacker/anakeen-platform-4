<?php

namespace Anakeen\Core\SmartStructure\Autocomplete;

use Anakeen\Core\SEManager;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;
use Dcp\Db\Exception;

class SmartStructureList
{

    /**
     * sub set of Smart Structure
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param                           $args
     * @return SmartAutocompleteResponse
     * @throws \Dcp\Db\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    public static function getSmartStructures(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $args): SmartAutocompleteResponse
    {
        if (empty($args["subset"])) {
            $args["smartstructure"]=-1;
            return SmartElementList::getSmartElements($request, $response, $args);
        }
        $subset = $args["subset"];
        $cdoc = SEManager::getFamily($subset);

        if (! $cdoc) {
            throw new Exception(sprintf("Unknow SmartStructure \"%s\"", $subset));
        }
        $tinter = $cdoc->getChildFam();
        $tinter[] = get_object_vars($cdoc);

        $name = strtolower($request->getFilterValue());
        $pattern_name = preg_quote($name, "/");
        foreach ($tinter as $v) {
            $ftitle = \Anakeen\Core\SmartStructure::getLangTitle($v);
            if (($name == "") || (preg_match("/$pattern_name/i", $ftitle, $reg))) {
                $response->appendEntry(
                    xml_entity_encode($ftitle),
                    [
                        [
                            "value" => $v["id"],
                            "displayValue" => $ftitle
                        ]
                    ]
                );
            }
        }
        return $response;
    }

    /**
     * create preg rule to search without diacritic
     *
     * @see lfamily
     *
     * @param string $text
     *
     * @return string rule for preg
     */
    public static function setDiacriticRules($text)
    {
        $dias = array(
            "a|à|á|â|ã|ä|å",
            "o|ò|ó|ô|õ|ö|ø",
            "e|è|é|ê|ë",
            "c|ç",
            "i|ì|í|î|ï",
            "u|ù|ú|û|ü",
            "y|ÿ",
            "n|ñ"
        );
        foreach ($dias as $dia) {
            $text = preg_replace("/[" . str_replace("|", "", $dia) . "]/u", "[$dia]", $text);
        }
        return $text;
    }
}
