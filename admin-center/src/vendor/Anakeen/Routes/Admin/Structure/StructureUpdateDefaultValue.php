<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;

/**
 * Update Structure Default Value
 *
 * @note Used by route : PUT /api/v2/admin/smart-structures/{structure}/update/default/
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
        $this->initData($request->getParsedBody()["params"], $args);
        $err = $this->manageNewDefValue();
        if ($err !== "") {
            return $response->withStatus(500, $err)->write($err);
        }
        return $response->withStatus(200);
    }

    protected function initData($dataFromFront, $args)
    {
        foreach (json_decode($dataFromFront) as $key => $value) {
            $this->data[$key] = $value;
        }
        $this->data["structure"] = SEManager::getFamily($this->data["structureId"]);
    }

    private function manageNewDefValue()
    {
        if (is_array($this->data["value"])) {
            $finalValue = $this->formatArray($this->data["value"]);
        } else {
            $finalValue = $this->data["value"];
        }

        $err = "";
        if ($this->data["valueType"] === "no_value") {
            $err = $this->data["structure"]->setDefValue($this->data["fieldId"], "NULL");
        } elseif ($this->data["valueType"] === "value" && $this->data["value"] === "") {
            $err = $this->data["structure"]->setDefValue($this->data["fieldId"], "");
        } else {
            $err = $this->data["structure"]->setDefValue($this->data["fieldId"], $finalValue);
        }

        if ($err !== "") {
            return $err;
        }
        return $this->data["structure"]->modify();
    }

    private function formatArray($arrayToFormat)
    {
        $formattedArray = [];
        for ($i=0; $i < count($arrayToFormat); $i++) {
            $formattedArray[$i] = [];
            foreach ($arrayToFormat[$i] as $line) {
                foreach ($line as $key => $value) {
                    $formattedArray[$i][$key] = $value;
                }
            }
        }
        return $formattedArray;
    }
}