<?php

namespace Anakeen\Fullsearch;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Exception;

class SearchSmartElementIndex
{
    /**
     * get raw text to index for a specific Smart Field
     * @param SmartElement $se Smart Element to update
     * @param NormalAttribute $oa Smart (for a structure)
     * @return string the raw text to index
     * @throws \Anakeen\Database\Exception
     * @throws Exception
     */
    public static function getSmartFieldIndex(SmartElement $se, NormalAttribute $oa)
    {
        $rawValue = $se->getRawValue($oa->id);
        if (!$rawValue) {
            return "";
        }

        $data = [];
        switch ($oa->type) {
            case "timestamp":
            case "date":
                $dateFormat = "%A %d %B %Y %m";
                if ($oa->type === "timestamp") {
                    $dateFormat .= " %H:%M:%S";
                }
                if ($oa->isMultiple() === false) {
                    $data[] = strftime($dateFormat, strtotime($rawValue));
                } else {
                    $rawValues = $se->getMultipleRawValues($oa->id);
                    foreach ($rawValues as $rawValue) {
                        $data[] = strftime($dateFormat, strtotime($rawValue));
                    }
                }
                break;
            case "enum":
                if ($oa->isMultiple() === false) {
                    $data[] = str_replace("/", " ", $oa->getEnumLabel($rawValue));
                } else {
                    $rawValues = \Anakeen\Core\Utils\Postgres::stringToFlatArray($rawValue);
                    foreach ($rawValues as $rawValue) {
                        $data[] = str_replace(
                            "/",
                            " ",
                            $oa->getEnumLabel($rawValue)
                        );
                    }
                }
                break;
            case "account":
            case "docid":
                $docRevOption = $oa->getOption("docrev", "latest");

                if ($oa->isMultiple() === false) {
                    $data[] = \DocTitle::getRelationTitle(
                        $rawValue,
                        $docRevOption === "latest",
                        $se,
                        $docRevOption
                    );
                } else {
                    $rawValues = \Anakeen\Core\Utils\Postgres::stringToFlatArray($rawValue);
                    foreach ($rawValues as $rawValue) {
                        $data[] = \DocTitle::getRelationTitle(
                            $rawValue,
                            $docRevOption === "latest",
                            $se,
                            $docRevOption
                        );
                    }
                }
                break;
            case 'file':
                if ($oa->isMultiple() === false) {
                    $rawValues = [$rawValue];
                } else {
                    $rawValues = $se->getMultipleRawValues($oa->id);
                }
                foreach ($rawValues as $rawFileValue) {
                    $filename = $se->vaultFilenameFromvalue($rawFileValue);
                    $basename = preg_replace("/\\p{P}/", " ", $filename);
                    $data[] = sprintf("%s (%s)", $filename, $basename);
                }

                break;
            default:
                if ($oa->isMultiple() === false) {
                    $data[] = $rawValue;
                } else {
                    $data[] = implode(", ", $se->getMultipleRawValues($oa->id));
                }
        }

        return implode(", ", $data);
    }
}
