<?php

namespace Anakeen\SmartStructures\FieldAccessLayer;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Core\SmartStructure\FieldAccessManager;
use Anakeen\Core\SmartStructure\FieldSetAttribute;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class FieldAccessLayerAutoComplete
{
    public static function getFieldAccess(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response,
        array $args
    ): SmartAutocompleteResponse {
        $famid = $args["structure"];
        if (!$famid) {
            $response->setError(___("Need select the structure first", "smart fieldaccesslayer"));
            return $response;
        }
        $name = $request->getFilterValue();
        $doc = SEManager::getFamily($famid);
        $pattern = preg_quote($name, "/");

        // Attributes
        $attrList = $doc->getAttributes();
        foreach ($attrList as $attr) {
            if ($attr->id === \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD || $attr->usefor === "Q") {
                continue;
            }

            if (($name == "") ||
                (preg_match("/$pattern/i", $attr->getLabel(), $m)) ||
                (preg_match("/$pattern/i", $attr->id, $m))
            ) {
                $response->appendEntry(
                    self::getDisplayLabel($attr),
                    [
                        $attr->id,
                        FieldAccessManager::getTextAccess($attr->access)
                    ]
                );
            }
        }
        return $response;
    }

    protected static function getDisplayLabel(BasicAttribute $attr)
    {
        $parentLabel = self::getParentLabel($attr);
        $htmlLabel = '';
        if ($parentLabel) {
            $htmlLabel = sprintf("<b><i>%s</i></b>", xml_entity_encode($parentLabel));
        } else {
            if (is_a($attr, FieldSetAttribute::class)) {
                $htmlLabel = sprintf("<b>Set (%s)</b>", xml_entity_encode($attr->type));
            }
        }
        $html = sprintf(
            "%s <code>[%s]</code><br/><span>&nbsp;&nbsp;%s</span>",
            $htmlLabel,
            xml_entity_encode($attr->id),
            xml_entity_encode($attr->getLabel())
        );

        return $html;
    }

    /**
     * @param BasicAttribute $oa
     *
     * @return string
     */
    protected static function getParentLabel($oa)
    {
        if ($oa && $oa->fieldSet && $oa->fieldSet->id != \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
            return self::getParentLabel($oa->fieldSet) . $oa->fieldSet->getLabel() . '/';
        }
        return '';
    }
}
