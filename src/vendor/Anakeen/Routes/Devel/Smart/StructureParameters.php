<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\Settings;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\URLUtils;

/**
 * Get Structure info
 *
 * @note Used by route : GET /api/v2/devel/smart/structures/{structure}/fields/
 */
class StructureParameters extends StructureFields
{
    protected $sqlFilter = "usefor = 'Q'";

    public function doRequest()
    {
        $data = parent::doRequest();

        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . sprintf("devel/smart/structures/%s/parameters/", $this->structureName));
        $data["parameterValues"] = $this->getConfigParameterValues($this->structure);
        $data["parameterFields"] = $data["fields"];
        unset($data["fields"]);

        return $data;
    }

    protected static function getConfigParameterValues(SmartStructure $structure)
    {
        $fields = $structure->getOwnParams();
        $data = [];
        foreach ($fields as $field => $value) {
            $data[$field] = [
                "config" => $value,
                "value" => $structure->getFamilyParameterValue($field)
            ];
        }
        return $data;
    }


    protected function checkAttribute(SmartStructure\BasicAttribute $oa)
    {
        return $oa->usefor === "Q" && $oa->type !== "menu";
    }
}
