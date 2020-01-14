<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\URLUtils;

/**
 * Get Structure info
 *
 * @note Used by route : GET /api/v2/admin/smart-structures/{structure}/defaults/
 */
class StructureDefaultValues extends StructureFields
{
    protected $structureName = "";

    /**
     *
     * @var SmartStructure $structure
     */
    protected $structure = null;


    public function doRequest()
    {
        $dataFields = parent::doRequest();
        $data["properties"] = $dataFields["properties"];
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("admin/smart-structures/%s/defaults/", $this->structureName));
        $data["fields"] = $dataFields["fields"];
        $data["defaultValues"] = $this->getConfigDefaultValues($this->structure, $data["fields"]);

        return $data;
    }

    protected static function getConfigDefaultValues(SmartStructure $structure, $dataFields)
    {
        $tempConfigDefValues = $structure->getOwnDefValues();
        $configDefValues = [];

        foreach ($tempConfigDefValues as $key => $value) {
            $configDefValues[$key] = [];
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (!is_array($v)) {
                        if (is_int($v)) {
                            // Check if the value is a structure or not
                            foreach ($dataFields as $dataFieldValue) {
                                if ($dataFieldValue["id"] === $key) {
                                    if ($dataFieldValue["simpletype"] === "docid") {
                                        error_log(var_export($k, true));
                                        $configDefValues[$key][$k] = [
                                            "displayValue" => SEManager::getTitle($v),
                                            "rawValue" => $v
                                        ];
                                    } else {
                                        $configDefValues[$key][$k] = [
                                            "displayValue" => $v,
                                            "rawValue" => $v
                                        ];
                                    }
                                    continue;
                                }
                            }
                        } else {
                            $configDefValues[$key][$k] = [
                                "displayValue" => $v,
                                "rawValue" => $v
                            ];
                        }
                    } else {
                        $configDefValues[$key][$k] = $v;
                    }
                }
            } else {
                if (is_int($value)) {
                    // Check if the value is a structure or not
                    foreach ($dataFields as $dataFieldValue) {
                        if ($dataFieldValue["id"] === $key) {
                            if ($dataFieldValue["simpletype"] === "docid") {
                                $configDefValues[$key] = SEManager::getTitle($value);
                            } else {
                                $configDefValues[$key] = $value;
                            }
                            continue;
                        }
                    }
                } else {
                    $configDefValues[$key] = $value;
                }
            }
        }

        if ($structure->fromid) {
            $parentStruct = SEManager::getFamily($structure->fromid);
            $configParentValues = $parentStruct->getDefValues();
        } else {
            $configParentValues = [];
        }
        $data = [];
        $element = SEManager::createTemporaryDocument($structure->name, true);

        $formater = new FormatCollection($element);


        $fields = $structure->getNormalAttributes();
        foreach ($fields as $oa) {
            if ($oa->id === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }

            $data[$oa->id]["configurationValue"] = $configDefValues[$oa->id] ?? null;
            if ($structure->fromid) {
                $data[$oa->id]["parentConfigurationValue"] = $configParentValues[$oa->id] ?? null;
            }
            $data[$oa->id]["result"] = json_decode(json_encode($formater->getInfo($oa, $element->getRawValue($oa->id), $element)), true);
        }
        return $data;
    }
}
