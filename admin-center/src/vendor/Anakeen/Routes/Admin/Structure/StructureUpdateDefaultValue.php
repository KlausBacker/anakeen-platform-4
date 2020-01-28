<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use ReflectionMethod;

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

    private $data = [];

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initData($request->getParsedBody()["params"], $args);
        $err = $this->manageNewDefValue();
        if ($err !== "") {
            $exception = new Exception($err);
            $exception->setHttpStatus(404);
            $exception->setUserMessage($err);
            throw $exception;
        }
        return ApiV2Response::withData($response, $err);
    }

    protected function initData($dataFromFront, $args)
    {
        foreach (json_decode($dataFromFront) as $key => $value) {
            $this->data[$key] = $value;
            if ($key === "value") {
            }
            if (is_null($this->structure) && $key === "structureId") {
                $this->structure = SEManager::getFamily($value);
            }
        }
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
            $err = $this->structure->setDefValue($this->data["fieldId"], "");
        } elseif ($this->data["valueType"] === "value" && $this->data["value"] === "") {
            $err = $this->structure->setDefValue($this->data["fieldId"], "");
        } elseif ($this->data["valueType"] === "advanced_value") {
            $err = $this->manageAdvancedValue($this->data["fieldId"], $this->data["value"]);
        } else {
            $err = $this->structure->setDefValue($this->data["fieldId"], $finalValue);
        }

        if ($err !== "") {
            return $err;
        }
        return $this->structure->modify();
    }

    private function formatArray($arrayToFormat)
    {
        $formattedArray = [];
        for ($i = 0; $i < count($arrayToFormat); $i++) {
            $formattedArray[$i] = [];
            if (is_array($arrayToFormat[$i])) {
                foreach ($arrayToFormat[$i] as $line) {
                    foreach ($line as $key => $value) {
                        $formattedArray[$i][$key] = $value;
                    }
                }
            } else {
                $formattedArray = json_encode($arrayToFormat);
            }
        }
        return $formattedArray;
    }

    private function manageAdvancedValue($defaultValueId, $advancedValue)
    {
        $oParse = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $structureFunction = $oParse->parse($advancedValue);
        $funcError = $oParse->getError();
        if ($funcError === "") {
            try {
                $refMeth = new ReflectionMethod($structureFunction->className, $structureFunction->methodName);
                return $this->structure->setDefValue($defaultValueId, $structureFunction->funcCall);
            } catch (\ReflectionException $refErr) {
                return $refErr->getMessage();
            }
        } else {
            return $funcError;
        }
    }
}
