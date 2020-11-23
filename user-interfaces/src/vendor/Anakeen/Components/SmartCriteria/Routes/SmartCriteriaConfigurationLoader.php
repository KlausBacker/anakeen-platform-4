<?php
namespace Anakeen\Components\SmartCriteria\Routes;

use Anakeen\Components\SmartCriteria\Exceptions\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartCriteria\SmartCriteriaConfig;
use Anakeen\SmartCriteria\SmartCriteriaConfigurationSingleton;
use Anakeen\SmartCriteria\SmartCriteriaUtils;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

/**
 * Parses initial smart criteria configuration and fetches relevant data
 *
 * Class SmartCriteriaConfigurationLoader
 *
 * @note    Used by route : GET /api/v2/smartcriteria/loadconfiguration
 */
class SmartCriteriaConfigurationLoader
{
    /**
     * @var object the inner configuration of the Smart Criteria
     */
    protected $config;

    /**
     * @var string the default structure of the Smart Criteria
     */
    protected $defaultStructure;

    /**
     * @var array of error messages
     */
    protected $errors = [];

    /**
     * @var array Occurence fields
     */
    protected $counterFields = array();

    /**
     * @var array of id
     */
    protected $idMap = array();

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->config = $request->getParams();
        $this->defaultStructure = array_key_exists(
            "defaultStructure",
            $this->config
        ) ? $this->config["defaultStructure"] : "-1";
        foreach ($this->config["criterias"] as &$criteria) {
            $this->completeCriteria($criteria);
        }

        $data = [
            "configuration" => $this->config,
            "idMap" => $this->idMap,
            "errors" => $this->errors
        ];

        return ApiV2Response::withData($response, $data);
    }

    protected function getReferenceStructure($structureIdentifier)
    {
        $structureRef = SEManager::getFamily($structureIdentifier);
        if (empty($structureRef)) {
            throw new Exception("SMARTCRITERIAUI0006", $structureIdentifier);
        }
        return $structureRef;
    }

    /**
     * Returns the list of states from workflow document
     * @param WDocHooks $wDoc the Workflow document
     * @return array the list of state with label and color keys
     * @throws \Anakeen\Exception
     */
    protected function getStatesList(WDocHooks $wDoc)
    {
        $states = [];
        if (!empty($wDoc)) {
            foreach ($wDoc->getStates() as $state) {
                $label = $wDoc->getStateLabel($state);
                $color = $wDoc->getColor($state);
                $stateInfo = [
                    "key" => $state,
                    "label" => $label,
                    "color" => $color,
                ];
                array_push($states, $stateInfo);
            }
        }
        return $states;
    }

    /************************** COMPLETION ************************************/

    /**
     * Initializes empty configuration attributes, checks the validity and completes the criteria configuration
     * @param $criteria
     */
    protected function completeCriteria(&$criteria)
    {
        $kind = $criteria["kind"];
        if (empty($kind)) {
            $kind = SmartCriteriaConfig::KIND_FIELD;
        }
        if (!isset($criteria["id"])) {
            $criteria["id"] = $this->getCriteriaId($criteria);
        }

        if (array_search($criteria["id"], $this->idMap) !== false) {
            array_push($this->errors, [
                "type" => "warning",
                "message" => "Error: The title \"" . $criteria["id"] . "\" already exist"
            ]);
        }
        array_push($this->idMap, $criteria["id"]);

        switch ($kind) {
            case SmartCriteriaConfig::KIND_FIELD:
                $this->completeFieldCriteria($criteria);
                break;
            case SmartCriteriaConfig::KIND_VIRTUAL:
                $this->completeVirtualCriteria($criteria);
                break;
            case SmartCriteriaConfig::KIND_PROPERTY:
                $this->completePropertyCriteria($criteria);
                break;
            default:
                $this->completeCustomCriteria($criteria);
                break;
        }
    }

    protected function getCriteriaId($criteria)
    {
        if (array_key_exists($criteria["field"], $this->counterFields) === true) {
            return \sprintf("%s_%s", $criteria["field"], $this->counterFields[$criteria["field"]]++);
        }
        $this->counterFields[$criteria["field"]] = 0;
        return \sprintf("%s_%s", $criteria["field"], $this->counterFields[$criteria["field"]]++);
    }

    protected function completeFieldCriteria(&$criteria)
    {

        /******** NORMALIZE *******/
        $field = $this->commonNormalize($criteria);

        // Check criteria structure
        $structure = "";
        if (key_exists("structure", $criteria)) {
            $structure = $criteria["structure"];
        }
        if (empty($structure)) {
            if (empty($this->defaultStructure)) {
                throw new Exception("SMARTCRITERIAUI0002", print_r($criteria, true));
            } else {
                $structure = $this->defaultStructure;
                $criteria["structure"] = $structure;
            }
        }

        /****** COMPLETE *****/

        // Get field object
        $structureRef = $this->getReferenceStructure($structure);
        $structField = $structureRef->getAttribute($field);
        if (empty($structField)) {
            $e = new Exception("SMARTCRITERIAUI0004", $field, $structureRef->name);
            $e->setHttpStatus(404, "Smart Field " . $field . " not found in " . $structureRef->name);
            throw $e;
        }

        // Set default label
        $defaultLabel = $structField->getLabel();
        if (!array_key_exists("label", $criteria)) {
            $criteria["label"] = $defaultLabel;
        }

        // Set criteria type
        $type = $structField->type;
        if (!array_key_exists("type", $criteria)) {
            $criteria["type"] = $type;
        }
        $type = $criteria["type"];
        if ($type === "htmltext" || $type === "longtext") {
            $criteria["type"] = "text";
        }


        // Multiple Field
        $multipleField = $structField->isMultiple();
        $criteria["multipleField"] = $multipleField;

        //Type specific additions
        if (in_array($type, array("docid", "account"))) {
            $criteria["typeFormat"] = $structField->format;
        } elseif ($type === "enum") {
            $phpFunc = $structField->phpfunc;
            $enumItems = array();
            if (!empty($phpFunc)) {
                $splitEnum = explode(',', $structField->phpfunc);
                $enumItems = array_map(function ($rawEnum) {
                    $splitValue = explode('|', $rawEnum);
                    return [
                            "key" => $splitValue[0],
                            "label" => $splitValue [1]
                        ];
                }, $splitEnum);
            }
            $criteria["enumItems"] = $enumItems;
        }

        //Default Operator
        if (!array_key_exists("default", $criteria)) {
            $defaultOperator = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperator($structField->type, $multipleField);
            $criteria["default"] = [
                "operator" => $defaultOperator
            ];
        } else {
            $default = $criteria["default"];
            if (!array_key_exists("operator", $default)) {
                $defaultOperator = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperator($structField->type, $multipleField);
                $default["operator"] = $defaultOperator;
            }
        }


        //operators
        $availableOperators = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperators(
            $type,
            $multipleField,
            $defaultLabel
        );
        $this->setCriteriaOperators($criteria, $availableOperators);
    }

    protected function completeVirtualCriteria(&$criteria)
    {
        $field = $this->commonNormalize($criteria);

        // Define default label
        if (!array_key_exists("label", $criteria)) {
            $criteria["label"] = $criteria["field"];
        }

        // default operator
        if (!array_key_exists("default", $criteria)) {
            $defaultOperator = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperator($field, false);
            $criteria["default"] = [
                "operator" => $defaultOperator
            ];
        } else {
            $default = $criteria["default"];
            if (!array_key_exists("operator", $default)) {
                $defaultOperator = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperator($field, false);
                $default["operator"] = $defaultOperator;
            }
        }

        // Operators
        $operators = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperators($field, false);
        $this->setCriteriaOperators($criteria, $operators);
    }

    protected function completePropertyCriteria(&$criteria)
    {
        $property = $this->commonNormalize($criteria);

        $isState = false;
        $isStateList = false;
        $workflow ="";
        $structure ="";

        if ($property === SmartCriteriaConfig::TITLE_KIND) {
            $criteria["type"] = "text";
        } elseif ($property === SmartCriteriaConfig::STATE_KIND) {
            $isState = true;
            $criteria["type"] = "enum";
            if (array_key_exists("stateList", $criteria)) {
                $criteria["enumItems"] = $criteria["stateList"];
                $isStateList = true;
            } else {
                if (array_key_exists("stateWorkflow", $criteria)) {
                    $workflow = $criteria["stateWorkflow"];
                } else {
                    if (array_key_exists("stateStructure", $criteria)) {
                        $structure = $criteria["stateStructure"];
                    } else {
                        $structure = $this->defaultStructure;
                    }
                }
            }
        }

        // Default operator
        if (!array_key_exists("default", $criteria)) {
            $defaultOperator = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperator($property, false);
            $criteria["default"] = [
                "operator" => $defaultOperator
            ];
        } else {
            $default = $criteria["default"];
            if (!array_key_exists("operator", $default)) {
                $defaultOperator = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperator($property, false);
                $default["operator"] = $defaultOperator;
            }
        }


        $operators = SmartCriteriaConfigurationSingleton::getInstance()->getDefaultOperators($property, false);
        $propertyTitle = $isState ? ___("State", "SMARTCRITERIAUI") : ___("Title", "SMARTCRITERIAUI");
        if (!array_key_exists("label", $criteria)) {
            $criteria["label"] = $propertyTitle;
        }
        if ($isState) {
            if (!$isStateList) {
                /**
                 * @var WDocHooks $wDoc
                 */
                $wDoc = null;
                if (!empty($workflow)) {
                    $wDoc = SEManager::getDocument($workflow);
                } elseif (!empty($structure)) {
                    $structureElement = $this->getReferenceStructure($structure);
                    $wid = $structureElement->wid;
                    if (!empty($wid)) {
                        $wDoc = SEManager::getDocument($wid);
                    }
                }
                $states = $this->getStatesList($wDoc);
                $criteria["enumItems"] = $states;
            }
        }
        $this->setCriteriaOperators($criteria, $operators);
    }

    /**
     * Common criteria configuration normalization process
     * @param $criteria
     * @return string the field definition of the criteria
     * @throws Exception
     */
    protected function commonNormalize(&$criteria)
    {
        // Check field definition
        $field = $criteria["field"];
        if (empty($field)) {
            throw new Exception("SMARTCRITERIAUI0001", print_r($criteria, true));
        }

        // Check operators
        if (!array_key_exists("operators", $criteria)) {
            $criteria["operators"] = array();
        } else {
            $operators = $criteria["operators"];
            foreach ($operators as $operator) {
                if (!array_key_exists("options", $operator)) {
                    $operator["options"] = [];
                }
            }
        }

        if (array_key_exists("default", $criteria) && array_key_exists("operator", ($default = $criteria["default"])) && !array_key_exists("options", $default)) {
            $criteria["default"]["operator"]["options"] = [];
        }

        $criteria["modifiableOperator"] = (array_key_exists("modifiableOperator", $criteria) && $criteria["modifiableOperator"] === "false") ? false : true;


        return $field;
    }

    protected function setCriteriaOperators(&$criteria, $availableOperators)
    {
        $requiredOperators = array();
        $spuriousOperators = array();
        $correctOperators = array();

        if (array_key_exists("operators", $criteria)) {
            $requiredOperators = $criteria["operators"];
            if (!is_array($requiredOperators)) {
                throw new Exception("SMARTCRITERIAUI0003", print_r($criteria, true));
            }
        }

        if (!empty($requiredOperators)) {
            array_map(function ($reqOp) use ($availableOperators, &$correctOperators, &$spuriousOperators) {
                $isSpurious = true;
                foreach ($availableOperators as $availableOperator) {
                    if (SmartCriteriaUtils::areOperatorsEqual($reqOp, $availableOperator)) {
                        $correctOp = $availableOperator;
                        if (array_key_exists("label", $reqOp)) {
                            $correctOp["label"] = $reqOp["label"];
                        }
                        array_push($correctOperators, $correctOp);
                        $isSpurious = false;
                        break;
                    }
                }
                if ($isSpurious) {
                    array_push($spuriousOperators, $reqOp);
                }
                return $reqOp;
            }, $requiredOperators);
            foreach ($spuriousOperators as $spuriousOperator) {
                array_push($this->errors, [
                    "type" => "warning",
                    "message" => "Error: operator {$spuriousOperator["key"]} is not available and will be ignored"
                ]);
            }
        } else {
            $correctOperators = $availableOperators;
        }
        $criteria["operators"] = $correctOperators;
    }

    /**
     * Function to use in order to override server-side behavior
     * @param $criteria
     */
    protected function completeCustomCriteria(&$criteria)
    {
        throw new Exception("SMARTCRITERIAUI0007", $criteria["kind"]);
    }
}
