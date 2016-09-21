<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/enum_choice.php");
use Dcp\HttpApi\V1\DocManager\DocManager;
function autocomplete(Action & $action)
{
    $return = array(
        "success" => true,
        "error" => array() ,
        "data" => array()
    );
    
    try {
        //Usage part
        $usage = new ActionUsage($action);
        $usage->setText("autocomplete result for ddui");
        $documentId = $usage->addRequiredParameter("id", "document identifier");
        $attrId = $usage->addRequiredParameter("attrid", "attribute identifier");
        $fromid = $usage->addRequiredParameter("fromid", "family identifier");
        $index = $usage->addOptionalParameter("index", -1);
        $usage->setStrictMode(false);
        $usage->verify(true);
        
        $err = '';
        if ($documentId !== "0") {
            $doc = DocManager::getDocument($documentId);
            $err = $doc->control("view");
            if ($err) {
                throw new Exception("Unable to view the document " . $documentId);
            }
        } else {
            $doc = DocManager::getFamily($fromid);
        }
        
        if (!$doc) {
            throw new Exception(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
        }
        
        $attributeObject = $doc->getAttribute($attrId);
        if (!$attributeObject) {
            throw new Exception(sprintf(___("Attribute \"%s\" not found ", "ddui") , $attrId));
        }
        /**
         * @var NormalAttribute $attributeObject
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
                $th = $doc->getRawValue("thc_thesaurus");
                $attributeObject->phpfunc = "getThConcept(D,$th,CT):${attributeName},CT";
                $attributeObject->phpfile = "thesaurus.php";
            } else {
                $attributeObject->phpfunc = "lfamily(D,'$famid',CT,0,$serializedFilter,'$idType):${attributeName},CT";
                $attributeObject->phpfile = "fdl.php";
            }
        }
        //BEWARE SET LOT OF HTTPVAR
        //@todo rewrite this part to be less invasive
        compatOriginalFormPost($action->getArgument("filter") , $action->getArgument("attributes") , $attributeObject->id, $index);
        $result = getResPhpFunc($doc, $attributeObject, $rargids, $tselect, $tval, true, $index);
        if (!is_array($result)) {
            if ($result == "") {
                throw new Exception(sprintf(___("wrong return type when calling function %s\n%s", "ddui") , $attributeObject->phpfunc, $result));
            }
            $err = $result;
        }
        if (!$err) {
            foreach ($result as $currentResult) {
                $title = array_shift($currentResult);
                $values = array();
                foreach ($rargids as $key => $argName) {
                    if ($argName === "CT") {
                        $values[$attributeObject->id]["displayValue"] = isset($currentResult[$key]) ? $currentResult[$key] : null;
                    } else {
                        if (substr($argName, 0, 3) === "CT[") {
                            continue;
                        }
                        $values[strtolower($argName)]["value"] = isset($currentResult[$key]) ? $currentResult[$key] : null;
                        $ctKey = array_search("CT[".$argName."]", $rargids);
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
        } else {
            throw new Exception($err);
        }
        
        if (count($return["data"]) === 0) {
            throw new Exception(___("No result found", "ddui"));
        }
    }
    catch(Exception $e) {
        $return["success"] = false;
        $return["error"][] = $e->getMessage();
        unset($return["data"]);
    }
    
    $action->lay->template = json_encode($return);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}

function compatOriginalFormPost($filters, $attributes, $currentAid, $index)
{
    dduiSetHttpVar("_ct", " ");
    
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
                    dduiSetHttpVar("_$aid", $rawValue);
                } else {
                    if (isset($formatValue["value"])) {
                        dduiSetHttpVar("_$aid", $formatValue["value"]);
                    }
                }
            }
        }
    }
    
    if (is_array($filters)) {
        if (isset($filters["filters"][0]["value"])) {
            $ct = $filters["filters"][0]["value"];
            dduiSetHttpVar("_ct", $ct);
            if ($index >= 0) {
                $current = getHttpVars("_$currentAid");
                $current[$index] = $ct;
                dduiSetHttpVar("_$currentAid", $current);
            } else {
                dduiSetHttpVar("_$currentAid", $ct);
            }
        }
    }
}
function dduiSetHttpVar($name, $def)
{
    global $ZONE_ARGS;
    $ZONE_ARGS[$name] = $def;
}
