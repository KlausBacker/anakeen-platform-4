<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Core\SmartStructure\BasicAttribute;
use Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Router\Exception;
use Anakeen\SmartAutocompleteRequest;
use Anakeen\SmartAutocompleteResponse;
use Anakeen\SmartElementManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class Autocomplete
 *
 * @note    Used by route : POST /api/v2/smart-elements/{docid}/autocomplete/{attrid}
 * @package Anakeen\Routes\Ui
 */
class Autocomplete
{
    protected $documentId;
    protected $attributeId;
    protected $contentParameters;
    /**
     * @var \Anakeen\Routes\Core\Lib\ApiMessage[]
     */
    protected $messages = [];
    /**
     * @var \Slim\Http\request
     */
    protected $httpRequest;
    /**
     * @var NormalAttribute
     */
    protected $currentField;
    /**
     * @var \Anakeen\Core\SmartStructure|\Anakeen\SmartElement
     */
    protected $currentElement;

    /**
     * Use Create but it is a GET
     * But data are requested in a $_POST because they are numerous
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return mixed
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->documentId = $args["docid"];
        $this->attributeId = $args["attrid"];
        $this->httpRequest = $request;


        $this->contentParameters = $request->getParsedBody();

        $data = $this->autocomplete();
        return ApiV2Response::withData($response, $data, $this->messages);
    }


    protected function autocomplete()
    {
        $documentId = $this->documentId;
        $attrId = $this->attributeId;


        if ($documentId < 0 && $this->contentParameters["fieldInfo"]) {
            $fieldInfo = $this->contentParameters["fieldInfo"];
            $attributeObject = new NormalAttribute(
                $fieldInfo["id"],
                $this->contentParameters["fromid"],
                $fieldInfo["label"]??"",
                $fieldInfo["type"],
                $fieldInfo["typeFormat"]??"",
                false,
                1,
                "",
                BasicAttribute::READWRITE_ACCESS
            );
            if (isset($fieldInfo["options"])) {
                foreach ($fieldInfo["options"] as $optName => $optValue) {
                    $attributeObject->setOption($optName, $optValue);
                }
            }
        } else {
            if ($documentId !== "0") {
                $this->currentElement = SmartElementManager::getDocument($documentId);
            } else {
                $fromid = $this->contentParameters["fromid"];
                $this->currentElement = SEManager::getFamily($fromid);
            }

            if (!$this->currentElement) {
                throw new Exception(sprintf(___("Document \"%s\" not found ", "ddui"), $documentId));
            }
            $attributeObject = $this->currentElement->getAttribute($attrId);
        }

        if (!$attributeObject) {
            throw new Exception(sprintf(___("Attribute \"%s\" not found ", "ddui"), $attrId));
        }

        if ($attributeObject->properties && $attributeObject->properties->autocomplete) {
            return $this->standardAutocomplete($attributeObject);
        } elseif (!$attributeObject->phpfile) {
            return $this->defaultAutocomplete($attributeObject);
        } else {
            throw new Exception(sprintf("Legacy autocomplete not supported for \"%s\" field ", $attrId));
        }
    }

    protected function defaultAutocomplete(NormalAttribute $attributeObject)
    {
        switch ($attributeObject->type) {
            case "docid":
                return $this->docidAutocomplete($attributeObject);
            case "account":
                return $this->accountAutocomplete($attributeObject);
            default:
                $response = new SmartAutocompleteResponse();
                $response->setError(sprintf(
                    ___("Incompatible type \"%s\" for autocomplete", "autocomplete"),
                    $attributeObject->type
                ));
                return $response;
        }
    }

    protected function docidAutocomplete(NormalAttribute $attributeObject)
    {
        $parse = new ParseFamilyMethod();
        $parse->className = \Anakeen\Core\SmartStructure\Autocomplete\SmartElementList::class;
        $parse->methodName = "getSmartElements";
        $parse->outputs = [$attributeObject->id];

        $idType = "initid"; //if there's no docrev option (or it's present but not fixed), use initid to have the latest.
        $docrev = $attributeObject->getOption("docrev", "latest");
        if ($docrev === "fixed") {
            $idType = "id";
        } elseif ($docrev !== "latest") {
            $idType = "id";
            //if $docrev is neither fixed nor latest it should be state=...
            //if not, we'll just ignore the option
            $matches = array();
            if (preg_match('/^state\(([a-zA-Z0-9_:-]+)\)/', $docrev, $matches)) {
                $filter = "state='" . pg_escape_string($matches[1]) . "'";
                $parse->inputs["filter"] = new \Anakeen\Core\SmartStructure\Callables\InputArgument($filter, "string");
            }
        }

        $parse->inputs["smartstructure"] = new \Anakeen\Core\SmartStructure\Callables\InputArgument(
            $attributeObject->format,
            "string"
        );
        if ($idType !== "initid") {
            $parse->inputs["revised"] = new \Anakeen\Core\SmartStructure\Callables\InputArgument(true, "string");
        }

        return $this->callAutocomplete($parse);
    }

    protected function accountAutocomplete(NormalAttribute $attributeObject)
    {
        $parse = new ParseFamilyMethod();
        $parse->className = \Anakeen\Core\SmartStructure\Autocomplete\AccountList::class;
        $parse->methodName = "getAccounts";
        $parse->outputs = [$attributeObject->id];

        $parse->inputs["smartstructure"] = new \Anakeen\Core\SmartStructure\Callables\InputArgument(
            $attributeObject->format,
            "string"
        );

        $options = $attributeObject->getOptions();
        foreach ($options as $k => $v) {
            $parse->inputs[$k] = new \Anakeen\Core\SmartStructure\Callables\InputArgument($v, "string");
        }

        return $this->callAutocomplete($parse);
    }

    protected function standardAutocomplete(NormalAttribute $attributeObject)
    {
        $parse = new ParseFamilyMethod();
        $parse->parse($attributeObject->properties->autocomplete);
        $this->currentField = $attributeObject;

        return $this->callAutocomplete($parse);
    }

    protected function callAutocomplete(ParseFamilyMethod $parse)
    {
        $return = array(
            "error" => "",
            "data" => array()
        );

        try {
            if ($parse->methodName === "__invoke") {
                $className = $parse->className;
                $callable = new $className();
            } else {
                $callable = sprintf("%s::%s", $parse->className, $parse->methodName);
            }

            $request = new SmartAutocompleteRequest();
            $request->setHttpRequest($this->httpRequest);

            $response = new SmartAutocompleteResponse();
            $response->setOutputs($parse->outputs);
            $args = $this->getArgs($parse);

            $response = call_user_func($callable, $request, $response, $args);
            if ($response === null) {
                throw new Exception("Autocomplete error. Cannot call method :" . $callable);
            }

            if (!is_a($response, SmartAutocompleteResponse::class)) {
                throw new Exception("Autocomplete error. Must return SmartAutocompleteResponse object :" . $callable);
            }

            /**
             * @var SmartAutocompleteResponse $response
             */
            $return["error"] = $response->getError();
            $return["data"] = $response->getData();
            if ($return["error"]) {
                $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
                $message->contentHtml = $return["error"];
                $this->messages[] = $message;
            } elseif (count($return["data"]) === 0) {
                $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
                $message->type = \Anakeen\Routes\Core\Lib\ApiMessage::MESSAGE;

                if (!empty($this->contentParameters["filter"]["filters"][0]["value"])) {
                    $message->contentHtml = sprintf(
                        ___("No matches \"<i>%s</i>\"", "ddui"),
                        htmlspecialchars($this->contentParameters["filter"]["filters"][0]["value"])
                    );
                } else {
                    $message->contentText = ___("No result found", "ddui");
                }
                $this->messages[] = $message;
            }
        } catch (Exception $e) {
            $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $message->type = \Anakeen\Routes\Core\Lib\ApiMessage::ERROR;
            $message->contentText = $e->getMessage();
            $this->messages[] = $message;
        }

        return $return["data"];
    }

    protected function getArgs(ParseFamilyMethod $strucFunc)
    {
        $args = [];
        foreach ($strucFunc->inputs as $k => $inpArg) {
            if ($inpArg->type === "string") {
                $args[$k] = $inpArg->name;
            } else {
                $args[$k] = $this->getInputValue($inpArg->name);
            }
        }
        return $args;
    }

    protected function getInputValue($name)
    {
        $index = -1;
        if (isset($this->contentParameters["index"])) {
            $field = $this->currentElement->getAttribute($name);
            if ($field && $field->fieldSet->id === $this->currentField->fieldSet->id) {
                $index = intval($this->contentParameters["index"]);
            }
        }
        $attributes = $this->contentParameters["attributes"]??[];
        $attrName = strtolower($name);

        if (isset($attributes[$attrName])) {
            if ($index < 0) {
                if (isset($attributes[$attrName][0])) {
                    // It is a real multiple values
                    return array_map(function ($data) {
                        if (array_key_exists("value", $data)) {
                            return $data["value"];
                        } else {
                            if (isset($data[0])) {
                                return array_map(function ($subdata) {
                                    if (array_key_exists("value", $subdata)) {
                                        return $subdata["value"];
                                    } else {
                                        return $subdata;
                                    }
                                }, $data);
                            }
                            return $data;
                        }
                    }, $attributes[$attrName]);
                } else {
                    return $attributes[$attrName]["value"];
                }
            } else {
                if (isset($attributes[$attrName][$index])) {
                    return $attributes[$attrName][$index]["value"];
                } else {
                    return $attributes[$attrName]["value"];
                }
            }
        }
        throw new \Anakeen\Exception(sprintf("No find attribute argument \"%s\" for autocomplete", $name));
    }

    protected function dduiSetHttpVar($name, $def)
    {
        global $ZONE_ARGS;
        $ZONE_ARGS[$name] = $def;
    }
}
