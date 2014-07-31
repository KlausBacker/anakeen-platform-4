<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/enum_choice.php");
function autocomplete(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setText("get submenu document");
    
    $documentId = $usage->addRequiredParameter("id", "document identifier");
    $attrId = $usage->addRequiredParameter("attrid", "attribute identifier");
    $index = $usage->addOptionalParameter("index", -1);
    $usage->setStrictMode(false);
    $usage->verify();
    
    $doc = Dcp\DocManager::getDocument($documentId);
    
    if (!$doc) {
        $action->exitError(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
    }
    $err = $doc->control("view");
    if ($err) {
        $action->exitError($err);
    }
    
    $oattr = $doc->getAttribute($attrId);
    if (!$oattr) {
        $action->exitError(sprintf(___("Attribute \"%s\" not found ", "ddui") , $attrId));
    }
    
    if ($oattr->type == "docid" || $oattr->type == "account") {
        /**
         * @var NormalAttribute $oattr
         */
    }
    $aname = $oattr->id;
    $famid = $oattr->format;
    
    if (!$oattr->phpfile) {
        // in coherence with editutil.php
        $filter = array(); //no filter by default
        $sfilter = '';
        $idid = "initid"; //if there's no docrev option (or it's present but not fixed), use initid to have the latest.
        $docrev = $oattr->getOption("docrev");
        if ($docrev == "fixed") {
            $idid = "id";
        } elseif ($docrev != "latest") {
            //if $docrev is neither fixed nor latest it should be state=...
            //if not, we'll just ignore the option
            $matches = array();
            if (preg_match('/^state\(([a-zA-Z0-9_:-]+)\)/', $docrev, $matches)) {
                $filter[] = "state='" . pg_escape_string($matches[1]) . "'";
            }
        }
        //make $filter safe to pass in a string for getResPhpFunc.
        if (count($filter) == 0) $sfilter = serialize($filter);
        $oattr->phpfunc = "lfamily(D,'$famid',CT,0,$sfilter,'$idid):${aname},CT";
        $oattr->phpfile = "fdl.php";
    }
    compatOriginalFormPost($action->getArgument("filter") , $action->getArgument("attributes") , $oattr->id);
    //print_r($oattr->phpfunc);
    $res = getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval, true, $index);
    if (!is_array($res)) {
        if ($res == "") {
            $res = sprintf(___("wrong return type when calling function %s\n%s", "ddui") , $oattr->phpfunc, $res);
        }
        $err = $res;
    }
    $info = array();
    if (!$err) {
        foreach ($res as $aResult) {
            $title = array_shift($aResult);
            $values = array();
            foreach ($rargids as $k => $argName) {
                if ($argName === "CT") {
                    $values[$oattr->id]["displayValue"] = isset($aResult[$k])?$aResult[$k]:null;
                } else {
                    $values[strtolower($argName)]["value"] = isset($aResult[$k])?$aResult[$k]:null;
                }
            }
            
            $info[] = array(
                "title" => $title,
                "values" => $values
            );
        }
    }
    
    if (count($info) === 0) {
        $info[] = array(
            "title" => "",
            "error" => $err ? $err : ___("No result found", "ddui")
        );
    }
    $action->lay->template = json_encode($info);
    $action->lay->noparse = true;
    header('Content-Type: application/json');
}

function compatOriginalFormPost($filters, $attributes, $currentAid)
{
    setHttpVar("_ct", " ");
    if (is_array($attributes)) {
        
        foreach ($attributes as $aid => $formatValue) {
            if (isset($formatValue[0]) && is_array($formatValue[0])) {
            } else {
                setHttpVar("_$aid", $formatValue["value"]);
            }
        }
    }
    
    if (is_array($filters)) {
        if (!empty($filters["filters"][0]["value"])) {
            
            setHttpVar("_ct", $filters["filters"][0]["value"]);
            setHttpVar("_$currentAid", $filters["filters"][0]["value"]);
        }
    }
}
