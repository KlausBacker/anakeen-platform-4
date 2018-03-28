<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Router\Exception;
use Dcp\Core\AutocompleteLib;
use Anakeen\Core\DocManager as DocManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class Autocomplete
 * @note Used by route : POST /api/v2/documents/{docid}/autocomplete/{attrid}
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
    protected $messages=[];

    /**
     * Use Create but it is a GET
     * But data are requested in a $_POST because they are numerous
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return mixed
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->documentId = $args["docid"];
        $this->attributeId = $args["attrid"];


        $this->contentParameters = $request->getParsedBody();

        $data = $this->autocomplete();
        return ApiV2Response::withData($response, $data, $this->messages);
    }


    protected function autocomplete()
    {
        $return = array(
            "success" => true,
            "error" => array(),
            "data" => array()
        );

        try {
            $documentId = $this->documentId;
            $attrId = $this->attributeId;

            $index = $this->contentParameters["index"];

            $err = '';
            if ($documentId !== "0") {
                $doc = DocManager::getDocument($documentId);
                $err = $doc->control("view");
                if ($err) {
                    throw new Exception("Unable to view the document " . $documentId);
                }
            } else {
                $fromid = $this->contentParameters["fromid"];
                $doc = DocManager::getFamily($fromid);
            }

            if (!$doc) {
                throw new Exception(sprintf(___("Document \"%s\" not found ", "ddui"), $documentId));
            }

            $attributeObject = $doc->getAttribute($attrId);
            if (!$attributeObject) {
                throw new Exception(sprintf(___("Attribute \"%s\" not found ", "ddui"), $attrId));
            }
            /**
             * @var \NormalAttribute $attributeObject
             */
            $attributeName = $attributeObject->id;
            $famid = $attributeObject->format;

            if (!$attributeObject->phpfile) {
                // in coherence with editutil.php
                $filter = array(); //no filter by default
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
                        $filter[] = "state='" . pg_escape_string($matches[1]) . "'";
                    }
                }
                //make $filter safe to pass in a string for getResPhpFunc.
                $serializedFilter = serialize($filter);

                if ($attributeObject->type === "thesaurus") {
                    $th = $attributeObject->format;
                    $attributeObject->phpfunc = "getThConcept(D,$th,CT):${attributeName},CT";
                    $attributeObject->phpfile = "thesaurus.php";
                } else {
                    $attributeObject->phpfunc = "lfamily(D,'$famid',CT,0,$serializedFilter,'$idType):${attributeName},CT";
                    $attributeObject->phpfile = "fdl.php";
                }
            }
            //BEWARE SET LOT OF HTTPVAR
            //@todo rewrite this part to be less invasive
            $this->compatOriginalFormPost($this->contentParameters["filter"], $this->contentParameters["attributes"], $attributeObject->id, $index);
            $result = AutocompleteLib::getResPhpFunc($doc, $attributeObject, $rargids, $tselect, $tval, true, $index);
            if (!is_array($result)) {
                if ($result == "") {
                    throw new Exception(sprintf(___("wrong return type when calling function %s\n%s", "ddui"), $attributeObject->phpfunc, $result));
                }
                $err = $result;
                $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
                $message->contentHtml = $result;
                $this->messages[]=$message;
            }
            if (!$err) {
                foreach ($result as $currentResult) {
                    $title = array_shift($currentResult);
                    $values = array();
                    foreach ($rargids as $key => $argName) {
                        if ($argName === "CT") {
                            $values[$attributeObject->id]["displayValue"] = isset($currentResult[$key]) ? $currentResult[$key] : null;
                        } else {
                            if ($argName === "?" || substr($argName, 0, 3) === "CT[") {
                                continue;
                            }
                            $values[strtolower($argName)]["value"] = isset($currentResult[$key]) ? $currentResult[$key] : null;
                            $values[strtolower($argName)]["value"] = $values[strtolower($argName)]["value"] === "" ? null : $values[strtolower($argName)]["value"];

                            $ctKey = array_search("CT[" . $argName . "]", $rargids);
                            if ($ctKey !== false) {
                                $values[strtolower($argName)]["displayValue"] = isset($currentResult[$ctKey]) ? $currentResult[$ctKey] : null;
                            }
                        }
                    }

                    $return["data"][] = array(
                        "title" => $title,
                        "values" => $values
                    );
                }
            }
            if (count($return["data"]) === 0) {
                $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
                $message->type = \Anakeen\Routes\Core\Lib\ApiMessage::MESSAGE;

                if (!empty($this->contentParameters["filter"]["filters"][0]["value"])) {
                    $message->contentHtml = sprintf(___("No matches \"<i>%s</i>\"", "ddui"), htmlspecialchars($this->contentParameters["filter"]["filters"][0]["value"]));
                } else {
                    $message->contentText = ___("No result found", "ddui");
                }
                $this->messages[]=$message;
            }
        } catch (Exception $e) {
            $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $message->type = \Anakeen\Routes\Core\Lib\ApiMessage::ERROR;
            $message->contentText = $e->getMessage();
            $this->messages[]=$message;
        }

        return $return["data"];
    }

    /**
     * Compatibility function to set in global ZONE_ARGS all values sended in request
     * @param array $filters
     * @param array $attributes
     * @param string $currentAid
     * @param int $index
     */
    protected function compatOriginalFormPost($filters, $attributes, $currentAid, $index)
    {
        $this->dduiSetHttpVar("_ct", " ");

        if (is_array($attributes)) {
            foreach ($attributes as $aid => $formatValue) {
                if ($formatValue) {
                    $first = current($formatValue);
                    if (isset($first) && is_array($first)) {
                        $rawValue = array();
                        foreach ($formatValue as $fmtValue) {
                            if (!isset($fmtValue["value"])) {
                                $secondValue = array();
                                foreach ($fmtValue as $fmtValue2) {
                                    $secondValue[] = $fmtValue2["value"];
                                }
                                $rawValue[] = $secondValue;
                            } else {
                                $rawValue[] = $fmtValue["value"];
                            }
                        }
                        $this->dduiSetHttpVar("_$aid", $rawValue);
                    } else {
                        if (isset($formatValue["value"])) {
                            $this->dduiSetHttpVar("_$aid", $formatValue["value"]);
                        }
                    }
                }
            }
        }

        if (is_array($filters)) {
            if (isset($filters["filters"][0]["value"])) {
                $ct = $filters["filters"][0]["value"];
                $this->dduiSetHttpVar("_ct", $ct);
                if ($index >= 0) {
                    $current = getHttpVars("_$currentAid");
                    if (is_array($current)) {
                        $current[$index] = $ct;
                        $this->dduiSetHttpVar("_$currentAid", $current);
                    }
                } else {
                    $this->dduiSetHttpVar("_$currentAid", $ct);
                }
            }
        }
    }

    protected function dduiSetHttpVar($name, $def)
    {
        global $ZONE_ARGS;
        $ZONE_ARGS[$name] = $def;
    }
}
