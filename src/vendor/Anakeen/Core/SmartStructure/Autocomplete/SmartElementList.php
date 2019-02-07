<?php

namespace Anakeen\Core\SmartStructure\Autocomplete;

use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class SmartElementList
{
    protected static $withDiacritic = false;

    /**
     * list of Smart Element of a same Smart Structure
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param                           $args
     * @return SmartAutocompleteResponse
     * @throws \Anakeen\Database\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    public static function getSmartElements(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $args): SmartAutocompleteResponse
    {
        $famid = $args["smartstructure"];
        if (!empty($args["revised"])) {
            $idid = "id";
        } else {
            $idid = "initid";
        }
        $name = $request->getFilterValue();
        if ($famid[0] == '-') {
            $args["only"] = "true";
            $famid = substr($famid, 1);
        }

        if (!is_numeric($famid)) {
            $famName = $famid;
            $famid = \Anakeen\Core\SEManager::getFamilyIdFromName($famName);
            if ($famid <= 0) {
                return $response->setError(sprintf(___("Smart Structure \"%s\" not found", "autocomplete"), $famName));
            }
        }
        $args["smartstructure"]=$famid;
        $s = static::getSearchConfig($args);


        if (!empty($args["filter"])) {
            $s->addFilter($args["filter"]);
        }
        if ($name != "" && is_string($name)) {
            if (!self::$withDiacritic) {
                $name = self::setDiacriticRules(mb_strtolower($name));
            }
            $s->addFilter("title ~* '%s'", $name);
        }
        $s->setSlice(100);

        $s->returnsOnly(array(
            "title",
            $idid
        ));
        $tinter = $s->search();
        if ($s->getError()) {
            return $response->setError($s->getError());
        }

        foreach ($tinter as $k => $v) {
            $response->appendEntry(
                \Anakeen\Core\Utils\Strings::xmlEncode($v["title"]),
                [
                    [
                        "value" => $v[$idid],
                        "displayValue" => $v["title"]
                    ]
                ]
            );
        }
        return $response;
    }

    protected static function getSearchConfig(array $args)
    {
        $famid = $args["smartstructure"];
        $s = new \SearchDoc("", $famid);
        if (!empty($args["only"])) {
            $s->only = true;
        }

        return $s;
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
    public static function setDiacriticRules(
        $text
    ) {
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
