<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import documents
 *
 * @author Anakeen
 * @version $Id: import_file.php,v 1.149 2008/11/14 12:40:07 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

require_once("WHAT/Lib.FileMime.php");

define("ALTSEPCHAR", ' --- ');
define("SEPCHAR", ';');

/**
 * Add a document from csv import file
 * @param string $dbaccess database specification
 * @param array $data  data information conform to {@link \Anakeen\Core\Internal\SmartElement::GetImportAttributes()}
 * @param int $dirid default folder id to add new document
 * @param bool $analyze true is want just analyze import file (not really import)
 * @param string $ldir path where to search imported files
 * @param string $policy add|update|keep policy use if similar document
 * @param array $tkey attribute key to search similar documents
 * @param array $prevalues default values for new documents
 * @param array $torder array to describe CSV column attributes
 * @global double Http var : Y if want double title document
 * @return array properties of document added (or analyzed to be added)
 */
function csvAddDoc($dbaccess, $data, $dirid = 0, $analyze = false, $ldir = '', $policy = "add", $tkey = array(
    "title"
), $prevalues = array(), $torder = array())
{
    $o = new ImportSingleDocument();
    if ($tkey) {
        $o->setKey($tkey);
    }
    if ($torder) {
        $o->setOrder($torder);
    }
    $o->analyzeOnly($analyze);
    $o->setPolicy($policy);
    $o->setTargetDirectory($dirid);
    $o->setFilePath($ldir);
    if ($prevalues) {
        $o->setPreValues($prevalues);
    }
    return $o->import($data)->getImportResult();
}

function AddImportLog($msg)
{
    global $action;
    if ($action->lay) {
        $tmsg = $action->lay->GetBlockData("MSG");
        $tmsg[] = array(
            "msg" => $msg
        );
        $action->lay->SetBlockData("MSG", $tmsg);
    } else {
        print "\n$msg";
    }
}
/**
 * @param array $orderdata
 * @return array
 */
function getOrder(array $orderdata)
{
    return array_map("strtolower", array_map("trim", array_slice($orderdata, 4)));
}

function AddVaultFile($dbaccess, $path, $analyze, &$vid)
{
    global $importedFiles;
    
    $err = '';
    $path = preg_replace(':/+:', '/', $path);
    // return same if already imported (case of multi links)
    if (isset($importedFiles[$path])) {
        $vid = $importedFiles[$path];
        return "";
    }
    // $mime=mime_content_type($absfile);
    $mime = getSysMimeFile($path);
    if (!$analyze) {
        $vf = newFreeVaultFile($dbaccess);
        $err = $vf->Store($path, false, $vid);
    }
    if ($err != "") {
        \Anakeen\Core\Utils\System::addWarningMsg($err);
        return $err;
    } else {
        $base = basename($path);
        $importedFiles[$path] = "$mime|$vid|$base";
        $vid = "$mime|$vid|$base";
        
        return "";
    }
    return false;
}
function seemsODS($filename)
{
    if (preg_match('/\.ods$/', $filename)) {
        return true;
    }
    $sys = trim(shell_exec(sprintf("file -bi %s", escapeshellarg($filename))));
    if ($sys == "application/x-zip") {
        return true;
    }
    if ($sys == "application/vnd.oasis.opendocument.spreadsheet") {
        return true;
    }
    return false;
}
/**
 * convert ods file in csv file
 * the csv file must be delete by caller after using it
 * @return string the path to the csv file
 */
function ods2csv($odsfile)
{
    $csvfile = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/csv") . "csv";
    $cmd = sprintf("%s --script=ods2csv --odsfile=%s --csvfile=%s >/dev/null", \Anakeen\Script\ShellManager::getAnkCmd(), escapeshellarg($odsfile), escapeshellarg($csvfile));
    system($cmd, $out);

    if ($out !== 0) {
        throw new \Dcp\Core\Exception(sprintf("Cannot convert to csv file \"%s\"", $odsfile));
    }
    return $csvfile;
}