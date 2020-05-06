<?php
namespace Anakeen\Components\SearchCriteria\Routes;

use Anakeen\Components\SearchCriteria\Exceptions\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Search\SearchCriteria\SearchCriteriaUtils;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

/**
 * Get criteria information from collection and field/property
 *
 * Class Criteria
 *
 * @note    Used by route : GET /api/v2/searchcriteria/{collection}/{field}
 */
class SmartCriteriaDataProcessor
{
    private $collection = "";
    private $field = "";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        if (!isset($args["collection"])) {
            throw new Exception("SEARCHCRITERIA0001");
        }

        if (!isset($args["field"])) {
            throw new Exception("SEARCHCRITERIA0002");
        }

        $this->collection = $args["collection"];
        $this->field = $args["field"];

        if ($this->collection === "property") {
            $operators = SearchCriteriaUtils::getDefaultOperators($this->field, false);
            $defaultOperator = SearchCriteriaUtils::getDefaultOperator($this->field, false);

            $propertyTitle = "Titre";
            if ($this->field === "state") {
                $propertyTitle = "État";
            }
            $data = [
                "operators" => $operators,
                "defaultOperator" => $defaultOperator,
                "title" => $propertyTitle
            ];

            if ($this->field === "state") {
                $isStateList = $request->getParam("statelist");
                if (!$isStateList) {
                    $structure = $request->getParam("structure");
                    $workflow = $request->getParam("workflow");
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
                    $data["enumItems"] = $states;
                }
            }
        } elseif ($this->collection === "virtual") {
            $operators = SearchCriteriaUtils::getDefaultOperators($this->field, false);
            $defaultOperator = SearchCriteriaUtils::getDefaultOperator($this->field, false);
            $data = [
                "operators" => $operators,
                "defaultOperator" => $defaultOperator,
            ];
        } else {
            $structureRef = $this->getReferenceStructure($this->collection);
            if (!empty($structureRef)) {
                $structField = $structureRef->getAttribute($this->field);
                if (!empty($structField)) {
                    $fieldMultiple = $structField->isMultiple();
                    $operators = SearchCriteriaUtils::getDefaultOperators($structField->type, $fieldMultiple, $structField->getLabel());
                    $defaultOperator = SearchCriteriaUtils::getDefaultOperator($structField->type, $fieldMultiple);
                    $type = $structField->type;
                    $data = [
                        "field" => $structField->id,
                        "multiple" => $fieldMultiple,
                        "smartType" => $structField->type,
                        "title" => $structField->getLabel(),
                        "operators" => $operators,
                        "defaultOperator" => $defaultOperator,
                    ];
                    if (in_array($type, array("docid", "account"))) {
                        $data["typeFormat"] = $structField->format;
                    } elseif ($type === "enum") {
                        $phpFunc = $structField->phpfunc;
                        $enumItems = array();
                        if (!empty($phpFunc)) {
                            $splitEnum = explode(',', $structField->phpfunc);
                            error_log(print_r($structField->phpfunc, true));
                            $enumItems = array_map(function ($rawEnum) {
                                $splitValue = explode('|', $rawEnum);
                                return [
                                    "key" => $splitValue[0],
                                    "label" => $splitValue [1]
                                ];
                            }, $splitEnum);
                        }
                        $data["enumItems"] = $enumItems;
                    }
                } else {
                    $e = new Exception("SEARCHCRITERIA0004", $this->field, $structureRef->name);
                    $e->setHttpStatus(404, "Smart Field " . $this->field . " not found in " . $structureRef->name);
                    throw $e;
                }
            } else {
                $e = new Exception("SEARCHCRITERIA0005", $this->field);
                $e->setHttpStatus(500, "Cannot resolved smart field " . $this->field);
                throw $e;
            }
        }


        return ApiV2Response::withData($response, $data);
    }

    protected function getReferenceStructure($structureIdentifier)
    {
        $structureRef = SEManager::getFamily($structureIdentifier);
        if (empty($structureRef)) {
            throw new Exception("SEARCHCRITERIA0003", $structureIdentifier);
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
}
