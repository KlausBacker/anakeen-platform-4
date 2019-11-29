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
        $data["defaultValues"] = $this->getConfigParameterValues($this->structure);

        $data["fields"] = $dataFields["fields"];

        return $data;
    }

    protected static function getConfigParameterValues(SmartStructure $structure)
    {
        $configParams = $structure->getOwnParams();
        if ($structure->fromid) {
            $parentStruct=SEManager::getFamily($structure->fromid);
            $configParentValues = $parentStruct->getParams();
        } else {
            $configParentValues=[];
        }
        $data = [];
        $element = SEManager::createTemporaryDocument($structure->name, true);

        $formater=new FormatCollection($element);


        $fields = $structure->getNormalAttributes();
        foreach ($fields as $oa) {
            if ($oa->id === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }
            $isMultiple = $oa->isMultiple();


            $data[$oa->id]["configurationValue"]=$configParams[$oa->id]??null;
            if ($structure->fromid) {
                $data[$oa->id]["parentConfigurationValue"] = $configParentValues[$oa->id] ?? null;
            }


            $data[$oa->id]["result"]= json_decode(json_encode($formater->getInfo($oa, $element->getRawValue($oa->id), $element)), true);

            $data[$oa->id]["configurationValue"]=$configParams[$oa->id]??null;
            /*
                "configurationValue" => ,
                "type" => $oa->type,
                "value" => $isMultiple ? $element->getMultipleRawValues($field) : $element->getRawValue($field),
                "displayValue" => $formater->getInfo($oa, $element->getRawValue($field), $element)
            ];*/
        }
        return $data;
    }
}
