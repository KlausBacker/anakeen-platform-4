<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generate Php Document Classes
 *
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes


$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Generate Php Document Classes");
$docid = $usage->addOptionalParameter("docid", "special docid", null, 0);
$usage->verify();

if (($docid !== 0) && (!is_numeric($docid))) {
    $odocid = $docid;
    $docid = \Anakeen\Core\DocManager::getFamilyIdFromName($docid);
    if (!$docid) {
        print sprintf(_("family %s not found") . "\n", $odocid);
        exit(1);
    }
}

$query = new \Anakeen\Core\Internal\QueryDb("", \DocFam::class);
$query->AddQuery("doctype='C'");
$query->order_by = "id";

ini_set("memory_limit", -1);

if ($docid > 0) {
    $query->AddQuery("id=$docid");
    $tid = $query->Query(0, 0, "TABLE");
} else {
    // sort id by dependance
    $table1 = $query->Query(0, 0, "TABLE");
    $tid = array();
    pushfam(0, $tid, $table1);
}
if ($query->nb > 0) {
    $pubdir = DEFAULT_PUBDIR;
    if ($query->nb > 1) {
        $tii = array(
            1,
            2,
            3,
            4,
            5,
            6,
            20,
            21
        );
        foreach ($tii as $ii) {
            if (isset($tid[$ii])) {
                updateDoc($tid[$ii]);
                unset($tid[$ii]);
            }
        }
    }
    // workflow at the end
    foreach ($tid as $k => $v) {
        if (strstr($v["usefor"], 'W')) {
            updateDoc($v);
            /**
             * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
             */
            $wdoc = Anakeen\Core\DocManager::createDocument($v["id"]);
            $wdoc->CreateProfileAttribute(); // add special attribute for workflow
            \Dcp\FamilyImport::activateTrigger("", $v["id"]);
        }
    }
    foreach ($tid as $k => $v) {
        if (strstr($v["usefor"], 'W') === false) {
            updateDoc($v);
        }
    }
}
function updateDoc($v)
{
    try {
        $err = \Dcp\FamilyImport::buildFamilyFilesAndTables("", $v, true);
        if ($err) {
            error_log($err);
        }
    } catch (\Dcp\Exception $e) {
        print "\nERROR:" . $v["id"] . "[" . $v["title"] . "(" . $v["name"] . ")]\n";
        error_log($e->getMessage());
    }
}

// recursive sort by fromid
function pushfam($fromid, &$tid, $tfam)
{
    foreach ($tfam as $k => $v) {
        if ($v["fromid"] == $fromid) {
            $tid[$v["id"]] = $v;

            pushfam($v["id"], $tid, $tfam);
        }
    }
}
