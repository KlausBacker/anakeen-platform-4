<?php

namespace Anakeen\Routes\Admin\Structure;

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
        $data["defaultValues"] = $this->getConfigParameterValues($this->structure);


        $data["fields"] = $dataFields["fields"];

        return $data;
    }

    protected static function getConfigParameterValues(SmartStructure $structure)
    {
        $configDefValues = $structure->getOwnDefValues();
        $data = [];

        $element = SEManager::createTemporaryDocument($structure->name, true);

        $fields = $structure->getAttributes();
        foreach ($fields as $field => $oa) {
            if ($field === SmartStructure\Attributes::HIDDENFIELD) {
                continue;
            }
            $isMultiple = $oa->isMultiple();
            $data[$field] = [
                "config" => $configDefValues[$field]??"",
                "type" => $oa->type,
                "value" => $isMultiple ? $element->getRawValue($field) : $element->getMultipleRawValues($field)
            ];
        }
        return $data;
    }
}
