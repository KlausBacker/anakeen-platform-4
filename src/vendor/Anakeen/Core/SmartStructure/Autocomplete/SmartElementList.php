<?php

namespace Anakeen\Core\SmartStructure\Autocomplete;

use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class SmartElementList
{
    protected static $withDiacritic=false;

    /**
     * list of documents of a same family
     *
     * @param string $dbaccess      database specification
     * @param string $famid         family identifier (if 0 any family). It can be internal name
     * @param string $name          string filter on the title
     * @param int    $dirid         identifier of folder for restriction to a folder tree (deprecated)
     * @param array  $filter        additionnals SQL filters
     * @param string $idid          the document id to use (default: id)
     * @param bool   $withDiacritic to search with accent
     *
     * @return
     */
    public static function getSmartElements(SmartAutocompleteRequest $request, SmartAutocompleteResponse $response, $args) :SmartAutocompleteResponse
    {
        $only = false;
        $famid=$args["smartstructure"];
        $name=$request->getFilterValue();
        if ($famid[0] == '-') {
            $only = true;
            $famid = substr($famid, 1);
        }

        if (!is_numeric($famid)) {
            $famName = $famid;
            $famid = \Anakeen\Core\SEManager::getFamilyIdFromName($famName);
            if ($famid <= 0) {
                return sprintf(_("family %s not found"), $famName);
            }
        }
        $s = new \SearchDoc("", $famid); //$famid=-(abs($famid));
        if ($only) {
            $s->only = true;
        }


        if ($name != "" && is_string($name)) {
            if (!self::$withDiacritic) {
                $name = setDiacriticRules(mb_strtolower($name));
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
            $response->setError($s->getError())
            return $response;
        }

        $tr = array();

        foreach ($tinter as $k => $v) {
            $response->appendEntry(xml_entity_encode($v["title"]),
                 [$v[$idid],
                     $v["title"]]);

        }
        return $response;
    }
}