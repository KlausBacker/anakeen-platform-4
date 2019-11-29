<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;

/**
 * Update Structure Default Value
 *
 * @note Used by route : PUT /api/v2/admin/smart-structures/{structure}/update/
 */
class StructureUpdateDefaultValue extends StructureFields
{
    /**
     * @var SmartStructure $structure
     */
    protected $structure = null;

    private $data = [
        "structureId" => "",
        "fieldId" => "",
        "value" => "",
        "valueType" => "",
    ];
    
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request->getParsedBody()["params"], $args);
        $err = $this->manageNewDefValue();
        if ($err !== "") {
            return $response->withStatus(500, $err)->write($err);
        }
        return $response->withStatus(200);
    }

    protected function initParameters($dataFromFront, $args)
    {
        foreach (json_decode($dataFromFront) as $key => $value) {
            $this->data[$key] = $value;
        }
        $this->data["structure"] = SEManager::getFamily($this->data["structureId"]);
    }

    private function manageNewDefValue()
    {
        $err = "";
        if ($this->data["valueType"] === "no_value") {
            $err = $this->data["structure"]->setDefValue($this->data["fieldId"], null);
        } elseif ($this->data["valueType"] === "value" && $this->data["value"] === "") {
            error_log("EMPTY_STRING");
            $err = $this->data["structure"]->setDefValue($this->data["fieldId"], "");
        } else {
            error_log("OTHER");
            $err = $this->data["structure"]->setDefValue($this->data["fieldId"], $this->data["value"] /* ["1st", "2nd"] */);
        }

        if ($err !== "") {
            return $err;
        }
        return $this->data["structure"]->modify();
        ;
    }
}
