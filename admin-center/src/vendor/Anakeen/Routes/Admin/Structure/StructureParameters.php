<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\Internal\FormatCollection;
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

        $data["params"] = $this->structure->getParamAttributes();
        return $data;
    }

    protected static function getConfigParameters(SmartStructure $structure)
    {
        $configParams = $structure->getOwnParams();
        if ($structure->fromid) {
            $parentStruct=SEManager::getFamily($structure->fromid);
            $configParentParameters = $parentStruct->getParams();
        } else {
            $configParentParameters=[];
        }
        $data = [];
        $element = SEManager::createTemporaryDocument($structure->name, true);

        $formater=new FormatCollection($element);

        $paramAttributes = $structure->getParamAttributes();
        foreach ($paramAttributes as $oa) {
            if ($oa->id === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }

            $data[$oa->id]["configurationParameter"]=$configParams[$oa->id]??null;
            if ($structure->fromid) {
                $data[$oa->id]["parentConfigurationParameters"] = $configParentParameters[$oa->id] ?? null;
            }
            $data[$oa->id]["result"]= json_decode(json_encode($formater->getInfo($oa, $element->getParamValue($oa->id), $element)), true);
            $data[$oa->id]["configurationParameter"]=$configParams[$oa->id]??null;
        }
        
        return $data;
    }
}
