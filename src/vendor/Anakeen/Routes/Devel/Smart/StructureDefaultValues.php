<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\URLUtils;

/**
 * Get Structure info
 *
 * @note Used by route : GET /api/v2/devel/smart/structures/{structure}/fields/
 */
class StructureDefaultValues extends StructureFields
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
        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("devel/smart/structures/%s/defaults/", $this->structureName));
        $data["defaultValues"] = $this->getConfigParameterValues($this->structure);


        $data["fields"] = $dataFields["fields"];
        $parametersRoute = new StructureParameters();
        $parametersRoute->initParameters(["structure" => $this->structure->name]);
        $paramData = $parametersRoute->doRequest();

        $data["parameterFields"] = $paramData["parameterFields"];
        return $data;
    }

    protected static function getConfigParameterValues(SmartStructure $structure)
    {
        $fields = $structure->getOwnDefValues();
        $data = [];

        $element = SEManager::createTemporaryDocument($structure->name);


        foreach ($fields as $field => $value) {
            $oa = $structure->getAttribute($field);
            $isMultiple = $oa->isMultiple();
            $data[$field] = [
                "config" => $value,
                "type" => $oa->usefor === "Q" ? "parameter" : "field",
                "value" => $isMultiple ? $element->getRawValue($field) : $element->getMultipleRawValues($field)
            ];
        }
        return $data;
    }
}
