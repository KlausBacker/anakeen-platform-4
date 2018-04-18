<?php

namespace Dcp\Core;

use Anakeen\Core\Utils\Strings;

class AutocompleteLib
{


    /**
     * @param string           $n
     * @param string           $def
     * @param bool             $whttpvars
     * @param \Anakeen\Core\Internal\SmartElement             $doc
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oa
     *
     * @return array|bool|string
     */
    public static function getFuncVar($n, $def, $whttpvars, &$doc, &$oa)
    {
        if ($whttpvars) {
            return GetHttpVars("_" . strtolower($n), $def);
        }
        $h = GetHttpVars(strtolower($n));
        if ($h) {
            return $h;
        }
        if (!$oa) {
            return ($n);
        }
        if (($oa->repeat) || $oa->inArray()) {
            $r = $doc->getMultipleRawValues($n);
        } else {
            $r = $doc->getRawValue($n);
        }
        if ($r === "") {
            return false;
        }

        return $r;
    }

    public static function getResPhpFunc(
        \Anakeen\Core\Internal\SmartElement & $doc,
        \Anakeen\Core\SmartStructure\NormalAttribute & $oattr,
        &$rargids,
        &$tselect,
        &$tval,
        $whttpvars = true,
        $index = ""
    ) {
        $phpfunc = $oattr->phpfunc;
        $phpfunc = str_replace(array(
            '\)',
            '\('
        ), array(
            '&rparenthesis;',
            '&lparenthesis;'
        ), $phpfunc);
        $oParse = new \ParseFamilyFunction();
        $strucFunc = $oParse->parse($phpfunc);
        if ($strucFunc->getError()) {
            return $strucFunc->getError();
        }

        if (!preg_match('/(.*)\((.*)\)\:(.*)/', $phpfunc, $reg)) {
            return sprintf(
                _("the pluggins function description '%s' is not conform for %s attribut"),
                $phpfunc,
                $oattr->id
            );
        }
        $callfunc = $oParse->functionName;
        if (!function_exists($callfunc)) {
            if (!file_exists(DEFAULT_PUBDIR . "/EXTERNALS/$oattr->phpfile")) {
                return sprintf(_("the external pluggin file %s cannot be read"), $oattr->phpfile);
            } else {
                include_once("EXTERNALS/$oattr->phpfile");
            }
        }
        if (!function_exists($callfunc)) {
            error_log(__METHOD__ . " $callfunc not found from " . $oattr->phpfile);
            return sprintf(_("function '%s' declared in %s is not found"), $callfunc, $oattr->id);
        }
        $rargids = $oParse->outputs; // return args
        $arg = array();
        foreach ($strucFunc->inputs as $k => $inpArg) {
            $v = str_replace(array(
                '&rparenthesis;',
                '&lparenthesis;'
            ), array(
                ')',
                '('
            ), $inpArg->name);
            if ($v != " ") {
                $v = trim($v);
            }

            $unser = @unserialize($v); // try unserial to see if it is object
            if ($unser != "") {
                $arg[$k] = $unser;
            } elseif (substr($v, 0, 1) == '{' && substr($v, -1, 1) == '}') {
                /* Evaluate '{FAM_NAME}' or '{APP_PARAM_NAME}' notation */
                $arg[$k] = self::getAttr(substr($v, 1, -1));
            } elseif ($inpArg->type == "string") {
                $arg[$k] = $v;
            } elseif ($v == "A") {
                global $action;
                $arg[$k] = &$action;
            } elseif ($v == "D") {
                $arg[$k] = $doc->dbaccess;
            } elseif ($v == "I") {
                $arg[$k] = $doc->id;
            } elseif ($v == "WIID") {
                $arg[$k] = getHttpVars("wiid");
            } elseif ($v == "K") {
                $arg[$k] = $index;
            } elseif ($v == "T") {
                $arg[$k] = &$doc;
            } elseif (($v[0] == "'") || ($v[0] == '"')) {
                $lc = substr($v, -1);
                if ($lc == $v[0]) {
                    $arg[$k] = mb_substr($v, 1, -1);
                } else {
                    $arg[$k] = mb_substr($v, 1);
                }
            } elseif ($doc->getPropertyValue($v) !== false) {
                $arg[$k] = $doc->getPropertyValue($v);
            } else {
                // can be values or family parameter
                $a = $doc->GetAttribute($v);

                if ($index === "" || $index === null) {
                    $ta = self::getFuncVar($v, $v, $whttpvars, $doc, $a);
                    if ($ta === false) {
                        return false;
                    }
                    if (is_array($ta)) {
                        unset($ta["__1x_"]); // suppress hidden row because not set yet
                        $arg[$k] = $ta;
                    } else {
                        $arg[$k] = trim($ta);
                    }
                } else {
                    if ($a && ($a->usefor == "Q")) {
                        if (($a->fieldSet->id == $oattr->fieldSet->id)) { // search with index
                            $ta = self::getFuncVar($v, $v, $whttpvars, $doc, $a);
                            if ($ta === false) {
                                return false;
                            }
                            $arg[$k] = trim($ta[$index]);
                        } else {
                            $arg[$k] = $doc->getFamilyParameterValue($v);
                        }
                    } elseif ($a && $a->inArray()) {
                        if (($a->fieldSet->id == $oattr->fieldSet->id)) { // search with index
                            $ta = self::getFuncVar($v, $v, $whttpvars, $doc, $a);
                            if ($ta === false) {
                                return false;
                            }
                            $arg[$k] = trim($ta[$index]);
                        } else {
                            $ta = self::getFuncVar($v, $v, $whttpvars, $doc, $a);
                            if ($ta === false) {
                                return false;
                            }
                            if (is_array($ta)) {
                                unset($ta["-1"]); // suppress hidden row because not set yet
                                $arg[$k] = $ta;
                            } else {
                                $arg[$k] = trim($ta);
                            }
                            $arg[$k] = $ta;
                        }
                    } else {
                        $ta = self::getFuncVar($v, $v, $whttpvars, $doc, $a);
                        if ($ta === false) {
                            return false;
                        }
                        if (is_array($ta)) {
                            if ($index !== "") {
                                $arg[$k] = trim($ta[$index]);
                            } else {
                                $arg[$k] = $ta;
                            }
                        } else {
                            $arg[$k] = trim($ta);
                        }
                    }
                }
                if ($a && ($a->usefor == "Q")) {
                    if (self::getFuncVar($v, false, $whttpvars, $doc, $a) === false) {
                        $arg[$k] = $doc->getFamilyParameterValue($v);
                    }
                }
            }
        }
        try {
            $res = call_user_func_array($callfunc, $arg);
        } catch (Exception $e) {
            $res = $e->getMessage();
        }

        if (is_array($res) && (count($res) > 0)) {
            reset($res);
            foreach ($res as $k => $v) {
                if (!is_array($v)) {
                    $err = \ErrorCode::getError("INH0001", $callfunc, $oattr->id);
                    error_log($err);
                    return $err;
                }
                foreach ($v as $k2 => $v2) {
                    if (!Strings::seemsUTF8($v2)) {
                        $err = \ErrorCode::getError(
                            "INH0002",
                            iconv('ISO-8859-1', "UTF-8//TRANSLIT", $v2),
                            $callfunc,
                            $oattr->id
                        );
                        error_log($err);
                        return $err;
                    }
                    // not for the title
                    if ($k2 > 0) {
                        $res[$k][$k2] = str_replace("\r", "", $v2);
                    }
                }
            }
            $tselect = array();
            $tval = array();
            reset($res);
            $ki = 0;
            foreach ($res as $k => $v) {
                $tselect[$k]["choice"] = $v[0];
                $tselect[$k]["cindex"] = $ki; // numeric index needed
                $tval[$k]["index"] = $ki;
                array_shift($v);

                $tval[$k]["attrv"] = "['" . implode("','", $v) . "']";
                $ki++;
            }
        }

        return $res;
    }

    public static function getAttr($aid)
    {
        $r = \Anakeen\Core\ContextManager::getApplicationParam($aid);
        if ($r == "") {
            $r = \Anakeen\Core\DocManager::getFamilyIdFromName($aid);
        }

        return $r;
    }
}
