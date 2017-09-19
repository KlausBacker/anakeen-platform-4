<?php
/*
 * @author Anakeen
 * Display family attributes
*/

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/Lib.Dir.php");

function familyModAttribute(Action & $action)
{
    $usage = new ActionUsage($action);
    $famid = $usage->addRequiredParameter("famid", "Family identifier");
    $attrid = strtolower($usage->addOptionalParameter("id", "attr id"));
    $labeltext = $usage->addOptionalParameter("labeltext", "attr labeltext");
    $title = $usage->addOptionalParameter("title", "attr title");
    $abstract = $usage->addOptionalParameter("abstract", "attr abstract", "", "N");
    $needed = $usage->addOptionalParameter("needed", "attr needed", "", "N");
    $type = $usage->addOptionalParameter("type", "attr type");
    $frameid = $usage->addOptionalParameter("frameid", "attr frameid");
    $ordered = $usage->addOptionalParameter("ordered", "attr ordered", "", "N");
    $visibility = $usage->addOptionalParameter("visibility", "attr visibility");
    $link = $usage->addOptionalParameter("link", "attr link");
    $phpfile = $usage->addOptionalParameter("phpfile", "attr phpfile");
    $phpfunc = $usage->addOptionalParameter("phpfunc", "attr phpfunc");
    $elink = $usage->addOptionalParameter("elink", "attr elink");
    $phpconstraint = $usage->addOptionalParameter("phpconstraint", "attr phpconstraint");
    $options = $usage->addOptionalParameter("options", "attr options");
    $usage->verify();
    
    $dbaccess = $action->dbaccess;
    
    if ($action->getParam("DOCADMIN_DEVEL") !== "Y") {
        
        header('HTTP/1.0 400 Error');
        $action->lay->template = "Action run only if docadmin devel parameters is activated";
        return;
    }
    
    $family = new_Doc($action->dbaccess, $famid);
    if (!$family->isAlive()) {
        $action->exitError(sprintf(___("document %s not found", "docadmin") , $famid));
    } elseif ($family->doctype != 'C') {
        $action->exitError(sprintf(___("document %s is not a family", "docadmin") , $family->getTitle()));
    }
    
    $err = $family->lock(true);
    if (!$err) {
        $err = $family->canEdit();
    }
    if ($err != "") {
        $action->exitError($err);
    }
    
    $oattr = new DocAttr($dbaccess, array(
        $famid,
        ($attrid)
    ));
    $oattr->labeltext = $labeltext;
    $oattr->title = ($title ? $title : "N");
    $oattr->abstract = ($abstract ? $abstract : "N");
    $oattr->needed = ($needed ? $needed : "N");
    $oattr->type = $type;
    $oattr->frameid = $frameid;
    $oattr->ordered = $ordered;
    $oattr->visibility = $visibility;
    $oattr->link = $link;
    $oattr->phpfile = $phpfile;
    $oattr->phpfunc = $phpfunc;
    $oattr->elink = $elink;
    $oattr->phpconstraint = $phpconstraint;
    $oattr->options = $options;
    
    $check = new CheckAttr();
    $check->check(array(
        "ATTR",
        $attrid,
        $frameid,
        $labeltext,
        $title,
        $abstract,
        $type,
        $ordered,
        $visibility,
        $needed,
        $link,
        $phpfile,
        $phpfunc,
        $elink,
        $phpconstraint,
        $options
    ));
    // Primary check
    if (false && $check->hasErrors()) {
        header('HTTP/1.0 400 Incorrect Attribute');
        $action->lay->template = $check->getErrors();
        return;
    }
    $isNewAttribute = (!$oattr->isAffected());
    
    $err = '';
    if ($frameid) {
        if (!$family->getAttribute($frameid)) {
            $err = ErrorCode::getError('ATTR0203', $frameid, $family->name);
        }
    }
    if (!$err) {
        // Use import to create / update attribute
        // All check will be performed
        $fileContent[] = array(
            "BEGIN",
            "",
            "",
            "",
            "",
            $family->name
        );
        $fileContent[] = array(
            "ATTR",
            $attrid,
            $frameid,
            $labeltext,
            $title,
            $abstract,
            $type,
            $ordered,
            $visibility,
            $needed,
            $link,
            $phpfile,
            $phpfunc,
            $elink,
            $phpconstraint,
            $options
        );
        $fileContent[] = array(
            "END"
        );
        $csvDelimiter = ";";
        $csvEnclosure = '"';
        
        $importAttrFile = sprintf("%s/%s.csv", getTmpDir() , uniqid("attr"));
        $fileHandler = fopen($importAttrFile, "w");
        foreach ($fileContent as $line) {
            fputcsv($fileHandler, $line, $csvDelimiter, $csvEnclosure);
        }
        
        fclose($fileHandler);
        
        $family->unLock(true);
        
        $wsh = getWshCmd(false, $action->user->id);
        $cmd = $wsh . sprintf("--api=importDocuments --file=%s --csv-enclosure='%s' --csv-separator='%s' 2>&1", escapeshellarg($importAttrFile) , $csvEnclosure, $csvDelimiter);
        $out = [];
        $err = exec($cmd, $out, $ret);
        
        unlink($importAttrFile);
        
        if ($ret !== 0) {
            header('HTTP/1.0 400 Error');
            
            $errors = [];
            if ($err && strpos($err, "End Of Exception") === - 1) {
                $errors[] = $err;
            }
            foreach ($out as $item) {
                if (preg_match("/^ERROR:(.*)/", $item, $reg)) {
                    $errors[] = $reg[1];
                }
                if (!$errors && preg_match("/{CORE0001} \\[([^\\]]+)\\]/", $item, $reg)) {
                    $errors[] = $reg[1];
                }
            }
            $action->lay->template = implode(", \n", $errors);
            return;
        }
        
        if ($ret === 0 && function_exists("opcache_invalidate")) {
            $genFile = sprintf("%s/FDLGEN/Class.Doc%d.php", DEFAULT_PUBDIR, $family->id);
            opcache_invalidate($genFile, true);
            $genFile = sprintf("%s/FDLGEN/Class.Attrid%d.php", DEFAULT_PUBDIR, $family->id);
            opcache_invalidate($genFile, true);
        }
        
        if ($isNewAttribute) {
            $family->addHistoryEntry(sprintf(___("Add Attribute \"%s\" ", "docadmin") , $attrid) , HISTO_INFO, "MODATTR");
        } else {
            $family->addHistoryEntry(sprintf(___("Modify Attribute \"%s\" ", "docadmin") , $attrid) , HISTO_INFO, "MODATTR");
        }
    } else {
        header('HTTP/1.0 400 Error');
        $action->lay->template = $err;
        return;
    }
}
