<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use ReflectionMethod;
use ReflectionException;

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
        $err = $this->manageNewParameter();
        if ($err !== "") {
            error_log($err);
            return $response->withStatus(500, $err)->write($err);
        }
        return $response->withStatus(200);
    }

    protected function initData($dataFromFront, $args)
    {
        error_log(var_export(json_decode($dataFromFront), true));
        foreach (json_decode($dataFromFront) as $key => $value) {
            $this->data[$key] = $value;
            if (is_null($this->structure)) {
                $this->structure = SEManager::getFamily($this->data[$key]->structureId);
            }
        }
    }
    private function manageNewParameter()
    {
        $err = "";
        foreach ($this->data as $parameterData) {
            if ($err === "") {
                if ($parameterData->valueType === "no_value") {
                    $err = $this->structure->setParam($parameterData->parameterId, null);
                } elseif ($parameterData->valueType === "value" && $parameterData->value === "") {
                    $err = $this->structure->setParam($parameterData->parameterId, "");
                } elseif ($parameterData->valueType === "advanced_value") {
                    $err = $this->manageAdvancedValue($parameterData->parameterId, $parameterData->value);
                } else {
                    error_log(gettype($parameterData->value));
                    error_log($parameterData->value);
                    $err = $this->structure->setParam($parameterData->parameterId, $parameterData->value);
                }

                if ($err !== "") {
                    return $err;
                }
                $err = $this->structure->modify();
            }
        }
        return $err;
    }
    private function manageAdvancedValue($parameterId, $advancedValue)
    {
        $oParse = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $structureFunction = $oParse->parse($advancedValue);
        $funcError = $oParse->getError();
        if ($funcError === "") {
            try {
                $refMeth = new ReflectionMethod($structureFunction->className, $structureFunction->methodName);
                return $this->structure->setParam($parameterId, $structureFunction->funcCall);
            } catch (\ReflectionException $refErr) {
                return $refErr->getMessage();
            }
        } else {
            return $funcError;
        }
    }
}
