<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Export Document from Folder
 *
 * @author  Anakeen
 * @version $Id: exportfld.php,v 1.44 2009/01/12 13:23:11 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

/**
 * Exportation of documents from folder or searches
 *
 * @param string                        $aflid                       Folder identifier to use if no "id" http vars
 * @param string                        $famid                       Family restriction to filter folder content
 * @param string                        $outputPath                  where put export, if wfile outputPath is a directory
 * @param bool                          $exportInvisibleVisibilities set to true to export invisible attribute also
 *
 * @throws Dcp\Exception
 * @throws Exception
 * @global string                       $fldid                       Http var : folder identifier to export
 * @global string                       $wprof                       Http var : (Y|N) if Y export associated profil also
 * @global string                       $wfile                       Http var : (Y|N) if Y export attached file export format will be tgz
 * @global string                       $wident                      Http var : (Y|N) if Y specid column is set with identifier of document
 * @global string                       $wutf8                       Http var : (Y|N) if Y encoding is utf-8 else iso8859-1
 * @global string                       $wcolumn                     Http var :  if - export preferences are ignored
 * @global string                       $eformat                     Http var :  (I|R|F) I: for reimport, R: Raw data, F: Formatted data
 * @global string                       $selection                   Http var :  JSON document selection object
 * @return void
 */
function exportfld($fldid = "0", $famid = "", $outputPath = "", bool $exportInvisibleVisibilities = false, array $options = [])
{

    $wprof = !empty($options["wprof"]); // With profile access
    $wfile = !empty($options["wfile"]); // With file contents
    $wident = // Profil option type; // With document numeric identifiers
    $fileEncoding = (!empty($options["code"])) ? $options["code"] : "utf8"; // File encoding

    // Profil option type
    $profilType = (!empty($options["wproftype"])) ? $options["wproftype"] : \Dcp\ExportDocument::useAclAccountType;


    $wutf8 = ($fileEncoding !== "iso8859-15");

    $nopref = true; // no preference read
    // Export format "I", "R", "F", "X", "Y"
    $eformat = (!empty($options["eformat"])) ? $options["eformat"] : "I";

    // character to delimiter fields - generaly a comma
    $csvSeparator = (!empty($options["csv-separator"])) ? $options["csv-separator"] : ";";


    $csvEnclosure = (!empty($options["csv-enclosure"])) ? $options["csv-enclosure"] : '"';


    Anakeen\Core\Utils\System::setMaxExecutionTimeTo(3600);
    $exportCollection = new Dcp\ExportCollection();
    $exportCollection->setOutputFormat($eformat);
    $exportCollection->setExportProfil($wprof);
    $exportCollection->setExportDocumentNumericIdentiers($wident);
    $exportCollection->setUseUserColumnParameter(!$nopref);
    $exportCollection->setOutputFileEncoding($wutf8 ? Dcp\ExportCollection::utf8Encoding : Dcp\ExportCollection::latinEncoding);
    $exportCollection->setVerifyAttributeAccess(!$exportInvisibleVisibilities);
    $exportCollection->setProfileAccountType($profilType);


    if (!$fldid) {
        \Anakeen\Core\ContextManager::exitError(___("no export folder specified", "sde"));
    }

    $fld = Anakeen\Core\SEManager::getDocument($fldid);
    if ($famid == "") {
        $famid = GetHttpVars("famid");
    }
    $fname = str_replace(array(
        " ",
        "'"
    ), array(
        "_",
        ""
    ), $fld->getTitle());

    $exportCollection->recordStatus(_("Retrieve documents from database"));

    $s = new SearchDoc("", $famid);
    $s->setObjectReturn(true);
    $s->setOrder("fromid, id");
    $s->useCollection($fld->initid);
    $s->search();


    $exportCollection->setDocumentlist($s->getDocumentList());
    $exportCollection->setExportFiles($wfile);
    //usort($tdoc, "orderbyfromid");
    if ($outputPath) {
        if ($wfile) {
            if (!is_dir($outputPath)) {
                mkdir($outputPath);
            }
            $foutname = $outputPath . "/fdl.zip";
        } else {
            $foutname = $outputPath;
        }
    } else {
        $foutname = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/exportfld");
    }

    if (file_exists($foutname)) {
        \Anakeen\Core\ContextManager::exitError(sprintf("export is not allowed to override existing file %s"), $outputPath);
    }

    $exportCollection->setOutputFilePath($foutname);
    $exportCollection->setCvsSeparator($csvSeparator);
    $exportCollection->setCvsEnclosure($csvEnclosure);

    try {
        $exportCollection->export();
        if (is_file($foutname)) {
            switch ($eformat) {
                case Dcp\ExportCollection::xmlFileOutputFormat:
                    $fname .= ".xml";
                    $fileMime = "text/xml";
                    break;

                case Dcp\ExportCollection::xmlArchiveOutputFormat:
                    $fname .= ".zip";
                    $fileMime = "application/x-zip";
                    break;

                default:
                    if ($wfile) {
                        $fname .= ".zip";
                        $fileMime = "application/x-zip";
                    } else {
                        $fname .= ".csv";
                        $fileMime = "text/csv";
                    }
            }
            $exportCollection->recordStatus(_("Export done"), true);
            if (!$outputPath) {
                Http_DownloadFile($foutname, $fname, $fileMime, false, false, true);
            }
        }
    } catch (Dcp\Exception $e) {
        throw $e;
    }
}



/**
 * Removes content of the directory (not sub directory)
 *
 * @param string $dirname the directory name to remove
 *
 * @return boolean True/False whether the directory was deleted.
 * @deprecated To delete (not used)
 */
function deleteContentDirectory($dirname)
{
    if (!is_dir($dirname)) {
        return false;
    }
    $dcur = realpath($dirname);
    $darr = array();
    $darr[] = $dcur;
    if ($d = opendir($dcur)) {
        while ($f = readdir($d)) {
            if ($f == '.' || $f == '..') {
                continue;
            }
            $f = $dcur . '/' . $f;
            if (is_file($f)) {
                unlink($f);
                $darr[] = $f;
            }
        }
        closedir($d);
    }

    return true;
}
