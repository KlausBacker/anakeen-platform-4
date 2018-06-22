<?php

namespace Anakeen\SmartStructures\Report;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;

class ReportAutocomplete
{
    public static function getReportSortableColumns(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response,
        array $args
    ): SmartAutocompleteResponse {
        $famid = $args["structure"];
        $name = $request->getFilterValue();
        $doc = SEManager::getFamily($famid);
        $tr = array();
        $pattern = preg_quote($name, "/");
        // Properties
        $propList = self::getSortProperties($request, $response, $doc);
        foreach ($propList as $prop) {
            if (($name == "") || (preg_match("/$pattern/i", $prop[1], $m))) {
                $tr[] = $prop;
            }
        }
        // Attributes
        $attrList = $doc->getSortAttributes();
        foreach ($attrList as $attr) {
            if (($name == "") || (preg_match("/$pattern/i", $attr->getLabel(), $m))) {
                $html = sprintf(
                    "<b><i>%s</i></b><br/><span>&nbsp;&nbsp;%s</span>",
                    xml_entity_encode(self::getParentLabel($attr)),
                    xml_entity_encode($attr->getLabel())
                );

                $response->appendEntry(
                    $html,
                    [
                        $attr->id,
                        $attr->getLabel(),
                        $attr->getOption('sortable')
                    ]
                );
            }
        }
        return $response;
    }

    /**
     * Get columns (attribute ir property) that can be used to present of
     * the report's result
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param array                     $args
     * @return SmartAutocompleteResponse
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public static function getReportColumns(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response,
        array $args
    ): SmartAutocompleteResponse {
        $famid = $args["structure"];
        $name = $request->getFilterValue();
        $doc = SEManager::getFamily($famid);

        $pattern = preg_quote($name, "/");
        // Properties
        $propList = array(
            "title" => _("doctitle"),
            "revdate" => _("revdate"),
            "revision" => _("revision"),
            "owner" => _("owner"),
            "state" => _("step"),
            "id" => _("document id")
        );
        foreach ($propList as $propName => $propLabel) {
            if (($name == "") || (preg_match("/$pattern/i", $propLabel, $m))) {
                $propLabel = \Anakeen\Core\Utils\Strings::mb_ucfirst($propLabel);
                $response->appendEntry(
                    $propLabel,
                    [
                        $propName,
                        $propLabel
                    ]
                );
            }
        }
        $relTypes = array(
            "docid",
            "account",
            "thesaurus"
        );
        // Attributes
        $attrList = $doc->getNormalAttributes();
        foreach ($attrList as $attr) {
            if ($attr->type == "array") {
                continue;
            }
            if (($name == "") || (preg_match("/$pattern/i", $attr->getLabel(), $m))) {
                $html = sprintf(
                    "<b><i>%s</i></b><br/><span>&nbsp;&nbsp;%s</span>",
                    xml_entity_encode(self::getParentLabel($attr)),
                    xml_entity_encode($attr->getLabel())
                );
                $response->appendEntry(
                    $html,
                    [
                        $attr->id,
                        $attr->getLabel(),
                        ''
                    ]
                );
                if (in_array($attr->type, $relTypes)) {
                    $html = sprintf(
                        "<b><i>%s</i></b><br/><span>&nbsp;&nbsp;%s <i>(%s)</i></span>",
                        xml_entity_encode(self::getParentLabel($attr)),
                        xml_entity_encode($attr->getLabel()),
                        ___("identifier", "smart report")
                    );
                    $response->appendEntry(
                        $html,
                        [
                            $attr->id,
                            sprintf("%s (%s)", $attr->getLabel(), _("report:docid")),
                            "docid"
                        ]
                    );
                }
            }
        }
        return $response;
    }

    /**
     * @param SmartStructure\NormalAttribute|SmartStructure\FieldSetAttribute $oa
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

    /**
     * Get sortable properties with their default sort order
     *
     *
     * @param SmartAutocompleteRequest  $request
     * @param SmartAutocompleteResponse $response
     * @param SmartStructure            $docfam
     * @return SmartAutocompleteResponse
     */
    protected static function getSortProperties(
        SmartAutocompleteRequest $request,
        SmartAutocompleteResponse $response,
        SmartStructure $docfam
    ) {
        $name = $request->getFilterValue();
        $pattern = preg_quote($name, "/");
        $props = $docfam->getSortProperties();

        foreach ($props as $propName => $config) {
            if ($config['sort'] != 'asc' && $config['sort'] != 'desc') {
                continue;
            }

            switch ($propName) {
                case 'state':
                    if ($docfam->wid <= 0) {
                        /* Excerpt from
                         * http://www.php.net/manual/en/control-structures.switch.php
                         *
                         * "Note that unlike some other languages, the continue statement
                         * applies to switch and acts similar to break. If you have a
                         * switch inside a loop and wish to continue to the next
                         * iteration of the outer loop, use continue 2."
                        */
                        continue 2;
                    }
                    $label = _("state");
                    break;

                case 'title':
                    $label = _("doctitle");
                    break;

                case 'initid':
                    $label = _("createdate");
                    break;

                default:
                    $label = \Anakeen\Core\Internal\SmartElement::$infofields[$propName]['label'];
                    if ($label != '') {
                        $label = _($label);
                    }
            }

            if ($name != "" && !preg_match("/$pattern/i", $label)) {
                continue;
            }

            $response->appendEntry(
                $label,
                [
                    $propName,
                    $label,
                    $config['sort']
                ]
            );
        }
        return $response;
    }
}
