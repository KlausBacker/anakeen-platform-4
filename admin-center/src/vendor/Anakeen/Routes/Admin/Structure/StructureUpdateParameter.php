<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;

/**
 * Update Structure Parameters
 *
 * @note Used by route : PUT /api/v2/admin/smart-structures/{structure}/update/parameter/
 */
class StructureUpdateParameter extends StructureFields
{
    /**
     * @var SmartStructure $structure
     */
    protected $structure = null;

    private $data = [];
    
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initData($request->getParsedBody()["params"], $args);
        // $err = "";
        $err = $this->manageNewParameter();
        if ($err !== "") {
            return $response->withStatus(500, $err)->write($err);
        }
        return $response->withStatus(200);
    }

    protected function initData($dataFromFront, $args)
    {
        foreach (json_decode($dataFromFront) as $key => $value) {
            $this->data[$key] = $value;
            if(is_null($this->structure)) {
                $this->structure = SEManager::getFamily($this->data[$key]->structureId);
            }
        }
    }

    private function manageNewParameter()
    {
        $err = "";
        foreach ($this->data as $parameterId => $parameterData) {
            if($err === "") {
                if ($parameterData->valueType === "no_value") {
                    $err = $this->structure->setParam($parameterData->parameterId, null);
                } elseif ($parameterData->valueType === "value" && $parameterData->value === "") {
                    $err = $this->structure->setParam($parameterData->parameterId, "");
                } else {
                    $err = $this->structure->setParam($parameterData->parameterId, $parameterData->value);
                }

                if($err !== "") {
                    return $err;
                }
                $err = $this->structure->modify();
            }
        }
        return $err;

        // if ($this->data["valueType"] === "no_value") {
        //     $err = $this->data["structure"]->setParam($this->data["parameterId"], null);
        // } elseif ($this->data["valueType"] === "value" && $this->data["value"] === "") {
        //     $err = $this->data["structure"]->setParam($this->data["parameterId"], "");
        // } else {
        //     $err = $this->data["structure"]->setParam($this->data["parameterId"], $this->data["value"]);
        // }

        // if ($err !== "") {
        //     return $err;
        // }
        // return $this->data["structure"]->modify();
    }
}