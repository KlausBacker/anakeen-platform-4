<?php

namespace Anakeen\Routes\Admin\Structure;

use Anakeen\Core\SEManager;
use Anakeen\Exception;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\ApiV2Response;
use ReflectionMethod;

/**
 * Update Structure Parameters
 *
 * @note Used by route : PUT /api/v2/admin/smart-structures/{structure}/update/parameter/
 */
class StructureUpdateParameter
{
    /**
     * @var SmartStructure $structure
     */
    protected $structure = null;

    private $data = [];

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initData($request, $args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initData($request, $args)
    {
        $this->structure = SEManager::getFamily($args["structure"]);
        if (!$this->structure) {
            throw new Exception("Cannot update parameter. Structure \"%s\" not found", $args["structure"]);
        }
        $this->data = $request->getParsedBody()["params"];

    }

    protected function doRequest()
    {
        $err = "";
        $updatedData = [];
        foreach ($this->data as $parameterData) {
            $fieldId = $parameterData["fieldId"];
            if (!empty($parameterData["toDelete"])) {
                $err = $this->structure->setParam($fieldId, null);
            } else {
                $fieldValue = $parameterData["fieldValue"];

                if (array_key_exists("value", $fieldValue)) {
                    if ($fieldValue["value"] === null) {
                        $fieldValue["value"] = "";
                    } elseif ($fieldValue["value"] === " ") {
                        $fieldValue["value"] = null;
                    }

                    $err = $this->structure->setParam($fieldId, $fieldValue["value"]);
                    $updatedData[$fieldId] = $fieldValue["value"];
                } else {
                    $rawValues = [];
                    foreach ($fieldValue as $ka => $rowValue) {
                        if (array_key_exists("value", $rowValue)) {
                            $rawValues[] = $rowValue["value"];
                        } else {
                            foreach ($rowValue as $rowValueCell) {
                                $rawValues[$ka][] = $rowValueCell["value"];
                            }
                        }
                    }
                    if ($rawValues === null) {
                        $rawValues = [];
                    } elseif ($rawValues === " ") {
                        $rawValues = null;
                    }
                    $err = $this->structure->setParam($fieldId, $rawValues);

                    $updatedData[$fieldId] = $rawValues;
                }
            }

            if ($err !== "") {
                break;
            }
            $err = $this->structure->modify();
            if ($err) {
                break;
            }

        }
        if ($err) {
            $exception = new Exception($err);
            $exception->setUserMessage($err);
            throw $exception;
        }
        return $updatedData;
    }

    private function manageAdvancedValue($parameterId, $advancedValue)
    {
        $oParse = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
        $structureFunction = $oParse->parse($advancedValue);
        $funcError = $oParse->getError();
        if ($funcError === "") {
            try {
                $refMeth = new ReflectionMethod($structureFunction->className, $structureFunction->methodName);
                return $this->structure->setParam($parameterId, $structureFunction->funcCall, false);
            } catch (\ReflectionException $refErr) {
                return $refErr->getMessage();
            }
        } else {
            return $funcError;
        }
    }
}
