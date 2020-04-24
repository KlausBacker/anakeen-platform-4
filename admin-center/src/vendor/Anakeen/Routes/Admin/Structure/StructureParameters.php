<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\URLUtils;

/**
 * Get Structure Parameters
 *
 * @note Used by route : GET /api/v2/admin/smart-structures/{structure}/parameters/
 */
class StructureParameters extends StructureFields
{
    protected $structureName = "";

    /**
     * @var SmartStructure $structure
     */
    protected $structure = null;


    public function doRequest()
    {
        $dataFields = parent::doRequest();
        $data["properties"] = $dataFields["properties"];
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("admin/smart-structures/%s/parameters/", $this->structureName));
        $data["paramsValues"] = $this->getConfigParameters($this->structure);

        $data["params"] = array_values($this->structure->getParamAttributes());
        foreach ($data["params"]  as $k=>$paramData) {
            if ($paramData->access === SmartStructure\BasicAttribute::NONE_ACCESS) {
                unset($data["params"][$k]);
            }
        }
        return $data;
    }

    protected static function getConfigParameters(SmartStructure $structure)
    {
        $configParams = $structure->getOwnParams();
        if ($structure->fromid) {
            $parentStruct = SEManager::getFamily($structure->fromid);
            $configParentParameters = $parentStruct->getParams();
        } else {
            $configParentParameters = [];
        }
        $data = [];
        $element = SEManager::createTemporaryDocument($structure->name, true);

        $formater = new FormatCollection($element);

        $paramAttributes = $structure->getParamAttributes();
        foreach ($paramAttributes as $oa) {
            if ($oa->id === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }

            $value= $element->getFamilyParameterValue($oa->id);


            $data[$oa->id]["configurationParameter"] = $configParams[$oa->id] ?? null;
            $pValue=null;
            if ($structure->fromid) {
                $pValue= $configParentParameters[$oa->id]??null;
                if ($pValue) {
                    $data[$oa->id]["parentConfigurationParameters"]=  json_decode(json_encode($formater->getInfo($oa, $pValue, $element)), true);
                } else {
                    $pValue = null;
                }
            }
            if ($value === '{}' && $oa->isMultiple()) {
                $data[$oa->id]["result"]=[];
            } else {
                $data[$oa->id]["result"] = json_decode(json_encode($formater->getInfo($oa, $value, $element)), true);
            }

            $data[$oa->id]["inArray"]=$oa->inArray();
            if (SmartElement::seemsMethod($data[$oa->id]["configurationParameter"])) {
                if ($data[$oa->id]["configurationParameter"] !== $value) {
                    $data[$oa->id]["computedMethod"] = $data[$oa->id]["configurationParameter"];
                    $data[$oa->id]["isComputed"] = true;
                }
            } elseif (empty($data[$oa->id]["configurationParameter"]) &&
                !empty($pValue) &&
                SmartElement::seemsMethod($pValue)) {
                if ($pValue !== $value) {
                    $data[$oa->id]["computedMethod"] = $pValue;
                    $data[$oa->id]["isComputed"] = true;
                }
            }
        }
        return $data;
    }
}
