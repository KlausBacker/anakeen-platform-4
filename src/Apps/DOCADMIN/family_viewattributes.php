<?php
/*
 * @author Anakeen
 * Display family attributes
*/

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/Lib.Dir.php");

function familyViewAttributes(Action & $action)
{
    $usage = new ActionUsage($action);
    $famid = $usage->addRequiredParameter("id", "Family identifier");
    $usage->verify();
    
    $dbaccess = $action->dbaccess;
    /**
     * @var DocFam $family
     */
    $family = new_Doc($action->dbaccess, $famid);
    if (!$family->isAlive()) {
        $action->exitError(sprintf(___("document %s not found", "docadmin") , $famid));
    } elseif ($family->doctype != 'C') {
        $action->exitError(sprintf(___("document %s is not a family", "docadmin") , $family->getTitle()));
    }
    $action->parent->addJsRef("admin/adminAssets/mustache.js/mustache.min.js");
    $action->parent->addJsRef("legacy/jquery/jquery.js");
    $action->parent->addJsRef("legacy/jquery-ui/js/jquery-ui.js");
    $action->parent->addJsRef("legacy/jquery-dataTables/1.10/js/jquery.dataTables.js");
    $action->parent->addJsRef("DOCADMIN:family_viewattributes.js", true);
    
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("legacy/jquery-dataTables/1.10/css/jquery.dataTables.css");
    $action->parent->addCssRef("legacy/jquery-dataTables/1.10/css/dataTables.jqueryui.css");
    $action->parent->addCssRef("DOCADMIN/Layout/family_viewattributes.css");
    
    $action->lay->set("famIcon", $family->getIcon("", 16));
    $fromids = $family->getFromDoc();
    $parents = $ancestrors = [];
    foreach ($fromids as $parentId) {
        $docParent = new_doc($action->dbaccess, $parentId);
        if ($docParent->id != $family->id) {
            $parents[$docParent->id] = array(
                'id' => $docParent->id,
                "name" => $docParent->name,
                "title" => $docParent->getTitle() ,
                "icon" => $docParent->getIcon("", 16)
            );
            $ancestrors[$docParent->name] = $docParent;
        }
    }
    $ancestrors = array_reverse($ancestrors, true);
    
    $sql = sprintf("select * from docattr where docid in (%s) and usefor != 'Q' and id !~ '^:' order by ordered", implode(',', $fromids));
    $dbAttrs = [];
    simpleQuery($action->dbaccess, $sql, $dbAttrs);
    $sql = sprintf("select id, docid from docattr where docid in (%s) and usefor != 'Q' and id ~ '^:' order by ordered", implode(',', $fromids));
    
    simpleQuery($action->dbaccess, $sql, $dbModAttr);
    
    foreach ($dbAttrs as $k => $v) {
        $dbAttrs[$v["id"]] = $v;
        unset($dbAttrs[$k]);
    }
    $ModPostFix = " [*]";
    $oDocAttr = new DocAttr($dbaccess);
    $oAttrs = $family->getAttributes();
    $family->attributes->orderAttributes(true);
    
    $relativeOrder = 0;
    /**
     * @var NormalAttribute $oa
     */
    foreach ($oAttrs as $oa) {
        // Usefor = 'A' => action attributes (deprecated)
        if ($oa->usefor === "Q") continue;
        if ($oa->getOption("relativeOrder")) {
            $oa->ordered = $oa->getOption("relativeOrder");
            $oa->options = preg_replace("/(relativeOrder=[a-zA-Z0-9_:]+)/", "", $oa->options);
        }
        
        if (empty($dbAttrs[$oa->id])) {
            
            if ($oa->id === "FIELD_HIDDENS") continue;
            $oDocAttr->id = $oa->id;
            $oDocAttr->type = $oa->type;
            $oDocAttr->docid = $oa->docid;
            $oDocAttr->usefor = $oa->usefor;
            $oDocAttr->ordered = $oa->ordered;
            $oDocAttr->visibility = $oa->visibility;
            $oDocAttr->labeltext = $oa->labelText;
            $oDocAttr->abstract = ($oa->isInAbstract) ? "Y" : "N";
            $oDocAttr->title = ($oa->isInTitle) ? "Y" : "N";
            
            $oDocAttr->needed = ($oa->needed) ? "Y" : "N";
            $oDocAttr->frameid = ($oa->fieldSet->id != "FIELD_HIDDENS") ? $oa->fieldSet->id : '';
            
            $oDocAttr->link = $oa->link;
            $oDocAttr->elink = $oa->elink;
            $oDocAttr->options = $oa->options;
            $oDocAttr->phpfile = $oa->phpfile;
            $oDocAttr->phpfunc = $oa->phpfunc;
            $oDocAttr->phpconstraint = $oa->phpconstraint;
            
            $dbAttrs[$oa->id] = $oDocAttr->getValues();
            $dbAttrs[$oa->id]["direct"] = "undirect";
        } else {
            $dbAttrs[$oa->id]["direct"] = "direct";
            $currentType = $oa->type;
            if (!empty($oa->format)) {
                $currentType.= '("' . $oa->format . '")';
            }
            if ($currentType != $dbAttrs[$oa->id]["type"]) {
                $dbAttrs[$oa->id]["type"] = $currentType . $ModPostFix;
            }
            if ($oa->labelText != $dbAttrs[$oa->id]["labeltext"]) {
                
                $dbAttrs[$oa->id]["labeltext"] = $oa->labelText . $ModPostFix;
            }
            if (!empty($oa->ordered) && $oa->ordered != $dbAttrs[$oa->id]["ordered"]) {
                if (preg_match("/relativeOrder=([a-zA-Z0-9_:]+)/", $dbAttrs[$oa->id]["options"], $reg)) {
                    
                    $dbAttrs[$oa->id]["ordered"] = $reg[1];
                    if ($oa->ordered !== $reg[1]) {
                        
                        $dbAttrs[$oa->id]["ordered"] = $oa->ordered . $ModPostFix;
                    }
                    $dbAttrs[$oa->id]["options"] = preg_replace("/(relativeOrder=[a-zA-Z0-9_:]+)/", "", $dbAttrs[$oa->id]["options"]);
                }
            } else {
                if (!empty($oa->ordered) && is_numeric($oa->ordered) && $ancestrors) {
                    /**
                     * @var \Doc $ancestror
                     */
                    foreach ($ancestrors as $ancestror) {
                        $parentOa = $ancestror->getAttribute($oa->id);
                        if ($parentOa && $parentOa->getOption("relativeOrder")) {
                            $dbAttrs[$oa->id]["ordered"] = $parentOa->getOption("relativeOrder");
                            break;
                        }
                    }
                }
            }
            if ($oa->visibility != $dbAttrs[$oa->id]["visibility"]) {
                $dbAttrs[$oa->id]["visibility"] = $oa->visibility . $ModPostFix;
            }
            if ($oa->options != $dbAttrs[$oa->id]["options"]) {
                $dbAttrs[$oa->id]["options"] = $oa->options . $ModPostFix;
            }
            if (!empty($oa->link) && ($oa->link != $dbAttrs[$oa->id]["link"])) {
                $dbAttrs[$oa->id]["link"] = $oa->link . $ModPostFix;
            }
            if (!empty($oa->elink) && ($oa->elink != $dbAttrs[$oa->id]["elink"])) {
                $dbAttrs[$oa->id]["elink"] = $oa->elink . $ModPostFix;
            }
            if (!empty($oa->phpfunc) && ($oa->phpfunc != $dbAttrs[$oa->id]["phpfunc"])) {
                $dbAttrs[$oa->id]["phpfunc"] = $oa->phpfunc . $ModPostFix;
            }
            if (!empty($oa->phpfile) && ($oa->phpfile != $dbAttrs[$oa->id]["phpfile"])) {
                $dbAttrs[$oa->id]["phpfile"] = $oa->phpfile . $ModPostFix;
            }
        }
        
        if (isset($dbAttrs[$oa->id]["type"])) {
            $dbAttrs[$oa->id]["simpletype"] = strtok($dbAttrs[$oa->id]["type"], "(");
            $dbAttrs[$oa->id]["inherit"] = ($dbAttrs[$oa->id]["docid"] !== $family->id) ? "parent" : "self";
            $dbAttrs[$oa->id]["displayOrder"] = $relativeOrder++;
            if ($dbAttrs[$oa->id]["type"] === "menu") {
                $dbAttrs[$oa->id]["displayOrder"]+= 10000000;
            }
            if ($dbAttrs[$oa->id]["type"] === "action") {
                $dbAttrs[$oa->id]["displayOrder"]+= 20000000;
            }
        }
        
        foreach ($dbModAttr as $modAttr) {
            if ($modAttr["id"] === ":" . $oa->id && $modAttr["docid"] == $oa->docid) {
                $dbAttrs[$oa->id]["direct"] = "modattr";
            }
        }
    }
    /*
    foreach ($oAttrs as $aid => $oa) {
        if ($oa->usefor !== "Q" && $oa->type === "frame" && $oa->type === "frame") {
            $dbAttrs[$aid]["displayOrder"] = viewFamilyUtil::getDisplayOrder($aid, $oAttrs);
            $oAttrs[$aid]->ordered = $dbAttrs[$aid]["displayOrder"];
        };
    }
    
    foreach ($oAttrs as $oa) {
        if ($oa->usefor !== "Q" && $oa->type === "tab") {
            $dbAttrs[$oa->id]["displayOrder"] = viewFamilyUtil::getDisplayOrder($oa->id, $oAttrs);
        };
    }*/
    
    unset($dbAttrs["FIELD_HIDDENS"]);
    foreach ($dbAttrs as & $attr) {
        if (($attr["type"] === "tab" || $attr["type"] === "frame")) {
            if (is_numeric($attr["ordered"])) {
                $attr["ordered"] = "";
            }
        };
        
        if (!empty($attr["ordered"]) && !is_numeric($attr["ordered"])) {
            $attr["options"] = preg_replace("/(relativeOrder=[a-zA-Z0-9_:]+)/", "", $attr["options"]);
        }
        $attr["pathId"] = viewFamilyUtil::getPathId($oAttrs[$attr["id"]]);
    }
    
    uasort($dbAttrs, 'viewFamilyUtil::reOrderAttr');
    $action->lay->eSetBlockData("ATTRIBUTES", $dbAttrs);
    
    $selectclass = array();
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, 0, "TABLE");
    foreach ($tclassdoc as $k => $cdoc) {
        $selectclass[$k]["idcdoc"] = $cdoc["id"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        $selectclass[$k]["name"] = $cdoc["name"];
        $selectclass[$k]["selected"] = ($cdoc["id"] === $family->id) ? "selected" : "";
    }
    $action->lay->esetBlockData("PARENTS", $parents);
    $action->lay->setBlockData("SELECTFAMILY", $selectclass);
    $action->lay->set("famid", $family->id);
    $action->lay->set("develMode", $action->getParam("DOCADMIN_DEVEL") === "Y");
}

class viewFamilyUtil
{
    /**
     * @param BasicAttribute $oa
     *
     * @return string
     */
    public static function getPathId($oa)
    {
        if ($oa->fieldSet && $oa->fieldSet->id !== "FIELD_HIDDENS") {
            return self::getPathId($oa->fieldSet) . " > " . $oa->id;
        } else {
            return $oa->id;
        }
    }
    
    public static function getDisplayOrder($aid, $oAttrs)
    {
        $order = - 1;
        /**
         * @var NormalAttribute $oa
         */
        foreach ($oAttrs as $oa) {
            if ($oa->fieldSet && $oa->fieldSet->id === $aid) {
                if ($oa->ordered > 0) {
                    if ($order === - 1) {
                        $order = $oa->ordered;
                    } else {
                        $order = min($oa->ordered, $order);
                    }
                }
            };
        }
        
        return $order - 0.5;
    }
    /**
     * use to usort attributes
     *
     * @param BasicAttribute $a
     * @param BasicAttribute $b
     *
     * @return int
     */
    public static function reOrderAttr($a, $b)
    {
        if ($a["displayOrder"] == $b["displayOrder"]) {
            return 0;
        }
        if ($a["displayOrder"] > $b["displayOrder"]) {
            return 1;
        }
        return -1;
    }
    
    function getAttributeProfunder($aid, array $attrs)
    {
        if (!$aid) {
            return 0;
        }
        $oa = $attrs[$aid];
        if (!$oa["frameid"]) {
            return 0;
        }
        
        return 1 + $this->getAttributeProfunder($oa["frameid"], $attrs);
    }
}
