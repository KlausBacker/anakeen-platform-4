<?php

namespace Anakeen\SmartStructures\RenderDescription\Render;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class FieldsAutocomplete
{
    public static function getFields(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response,
        array $args
    ): SmartAutocompleteResponse {
        $famid = $args["structure"];
        $name = $request->getFilterValue();
        $doc = SEManager::getFamily($famid);
        $tr = array();
        $pattern = preg_quote($name, "/");


        // Attributes
        $attrList = $doc->getAttributes();
        foreach ($attrList as $attr) {
            if ($attr->usefor === "Q" || $attr->id === \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }
            if (($name == "") || (preg_match("/$pattern/i", $attr->getLabel())) || (preg_match("/$pattern/i",
                    $attr->id))) {
                $html = sprintf(
                    "<b><i>%s</i></b><br/><span>&nbsp;&nbsp;%s</span>",
                    \Anakeen\Core\Utils\Strings::xmlEncode(self::getParentLabel($attr)),
                    \Anakeen\Core\Utils\Strings::xmlEncode($attr->getLabel())
                );

                $response->appendEntry(
                    $html,
                    [
                        $attr->id,
                        sprintf("%s %s", self::getParentLabel($attr), $attr->getLabel()),
                    ]
                );
            }
        }
        return $response;
    }


    /**
     * @param SmartStructure\BasicAttribute $oa
     *
     * @return string
     */
    protected static function getParentLabel($oa)
    {
        if ($oa && $oa->fieldSet && $oa->fieldSet->id !== \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
            return self::getParentLabel($oa->fieldSet) . $oa->fieldSet->getLabel() . '/ ';
        }
        return '';
    }
}
