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

    private $data = [
        "structureId" => "",
        "parameterId" => "",
        "value" => "",
        "valueType" => "",
    ];
    
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initData($request->getParsedBody()["params"], $args);
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
        }
        $this->data["structure"] = SEManager::getFamily($this->data["structureId"]);
    }

    private function manageNewParameter()
    {
        $err = "";
        if ($this->data["valueType"] === "no_value") {
            $err = $this->data["structure"]->setParam($this->data["parameterId"], null);
        } elseif ($this->data["valueType"] === "value" && $this->data["value"] === "") {
            $err = $this->data["structure"]->setParam($this->data["parameterId"], "");
        } else {
            $err = $this->data["structure"]->setParam($this->data["parameterId"], $this->data["value"]);
        }

        if ($err !== "") {
            return $err;
        }
        return $this->data["structure"]->modify();
    }
}
