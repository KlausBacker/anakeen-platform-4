<?php

namespace Anakeen\Exchange;

class ExportXml
{

    /**
     * Exportation as xml of documents from folder or searches
     *
     *
     * @param string    $aflid
     * @param string    $famid      restrict to specific family for folder
     * @param \SearchDoc $specSearch use this search instead folder
     * @param string    $outputFile put result into this file instead download it
     * @param string    $eformat    X : zip (xml inside), Y: global xml file
     * @param string    $wident
     * @param bool      $toDownload
     *
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     * @throws \Anakeen\Exception
     * @throws \Dcp\SearchDoc\Exception
     * @global string   $fldid      Http var : folder identifier to export
     * @global string   $wfile      Http var : (Y|N) if Y export attached file export format will be tgz
     * @global string   $flat       Http var : (Y|N) if Y specid column is set with identifier of document
     * @global string   $eformat    Http var :  (X|Y) I:  Y: only one xml, X: zip by document with files
     * @global string   $log        Http var :  log file output
     * @global string   $selection  Http var :  JSON document selection object
     *
     */
    public static function exportxmlfld(
        $aflid = "0",
        $famid = "",
        \SearchDoc $specSearch = null,
        $outputFile = '',
        $eformat = "",
        $wident = 'Y',
        $toDownload = true
    ) {
        \Anakeen\Core\Utils\System::setMaxExecutionTimeTo(3600); // 60 minutes
        $dbaccess = \Anakeen\Core\DbManager::getDbAccess();
        $fldid = $aflid;
        $wfile = false; // with files
        $wident = (substr(strtolower($wident), 0, 1) == "y"); // with numeric identifier
        $flat = false; // flat xml
        if (!$eformat) {
            $eformat = "X";
        } // export format
        $log = false; // log file
        $configxml = false;
        $flog = false;
        if ($log) {
            $flog = fopen($log, "w");
            if (!$flog) {
                self::exportExit(sprintf(_("cannot write log in %s"), $log));
            }
            fputs($flog, sprintf("EXPORT BEGIN OK : %s\n", \Anakeen\Core\Internal\SmartElement::getTimeDate(0, true)));
            fputs($flog, sprintf("EXPORT OPTION FLAT : %s\n", ($flat) ? "yes" : "no"));
            fputs($flog, sprintf("EXPORT OPTION WFILE : %s\n", ($wfile) ? "yes" : "no"));
            fputs($flog, sprintf("EXPORT OPTION CONFIG : %s\n", ($configxml) ? "yes" : "no"));
        }
        // constitution options for filter attributes
        $exportAttribute = array();
        if ($configxml) {
            if (!file_exists($configxml)) {
                self::exportExit(sprintf(_("config file %s not found"), $configxml));
            }

            $xml = @simplexml_load_file($configxml);

            if ($xml === false) {
                self::exportExit(sprintf(_("parse error config file %s : %s"), $configxml, print_r(libxml_get_last_error(), true)));
            }
            /**
             * @var \SimpleXmlElement $family
             */
            foreach ($xml->family as $family) {
                $afamid = @current($family->attributes()->name);
                if (!$afamid) {
                    self::exportExit(sprintf(_("Config file %s : family name not set"), $configxml));
                }
                $fam = \Anakeen\Core\SEManager::getFamily($afamid);
                if ((!$fam->isAlive()) || ($fam->doctype != 'C')) {
                    self::exportExit(sprintf(_("Config file %s : family name [%s] not match a know family"), $configxml, $afamid));
                }
                $exportAttribute[$fam->id] = array();
                foreach ($family->attribute as $attribute) {
                    $aid = @current($attribute->attributes()->name);

                    if (!$aid) {
                        self::exportExit(sprintf(_("Config file %s : attribute name not set"), $configxml));
                    }
                    $oa = $fam->getAttribute($aid);
                    if (!$oa) {
                        self::exportExit(sprintf(_("Config file %s : unknow attribute name %s"), $configxml, $aid));
                    }
                    $exportAttribute[$fam->id][$oa->id] = $oa->id;
                    $exportAttribute[$fam->id][$oa->fieldSet->id] = $oa->fieldSet->id;
                }
            }
        }
        // set the export's search
        $exportname = '';
        $fld = null;
        if ($specSearch) {
            $s = $specSearch;
            $s->setObjectReturn();
            $s->reset();
        } else {
            if (!$fldid) {
                self::exportExit(_("no export folder specified"));
            }

            $fld = \Anakeen\Core\SEManager::getDocument($fldid);
            if ($fldid && (!$fld->isAlive())) {
                self::exportExit(sprintf(_("folder/search %s not found"), $fldid));
            }

            $exportname = str_replace(array(
                " ",
                "'",
                '/'
            ), array(
                "_",
                "",
                "-"
            ), $fld->title);
            //$tdoc = getChildDoc($dbaccess, $fldid,"0","ALL",array(),$action->user->id,"TABLE",$famid);
            $s = new \SearchDoc($dbaccess, $famid);
            $s->setObjectReturn();

            $s->dirid = $fld->id;
        }

        $s->search();
        $err = $s->searchError();
        if ($err) {
            self::exportExit($err);
        }

        $foutdir = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/exportxml");
        if (!mkdir($foutdir)) {
            self::exportExit(sprintf("cannot create directory %s", $foutdir));
        }
        //$fname=sprintf("%s/FDL/Layout/fdl.xsd",DEFAULT_PUBDIR);
        //copy($fname,"$foutdir/fdl.xsd");

        $count = 0;
        if ($flog && $fld) {
            fputs($flog, sprintf("EXPORT OPTION ID : %s <%s>\n", $fldid, $fld->getTitle()));
        }

        $c = 0;
        while ($doc = $s->getNextDoc()) {
            //print $doc->exportXml();
            $c++;

            if ($doc->doctype != 'C') {
                $ftitle = str_replace(array(
                    '/',
                    '\\',
                    '?',
                    '*',
                    ':'
                ), '-', $doc->getTitle());
                /*
                 * The file name should not exceed MAX_FILENAME_LEN bytes and, as the string is in UTF-8,
                 * we must take care not to cut in the middle of a multi-byte char.
                */
                $suffix = sprintf("{%d}.xml", $doc->id);
                $maxBytesLen = MAX_FILENAME_LEN - strlen($suffix);
                $fname = sprintf("%s/%s%s", $foutdir, mb_strcut($ftitle, 0, $maxBytesLen, 'UTF-8'), $suffix);

                $err = $doc->exportXml($xml, $wfile, $fname, $wident, $flat, $exportAttribute);
                // file_put_contents($fname,$doc->exportXml($wfile));
                if ($err) {
                    self::exportExit($err);
                }
                $count++;
                if ($flog) {
                    fputs($flog, sprintf("EXPORT DOC OK : <%s> [%d]\n", $doc->getTitle(), $doc->id));
                }
            }
        }

        if ($flog) {
            fputs($flog, sprintf("EXPORT COUNT OK : %d\n", $count));
            fputs($flog, sprintf("EXPORT END OK : %s\n", \Anakeen\Core\Internal\SmartElement::getTimeDate(0, true)));
            fclose($flog);
        }

        if ($eformat == "X") {
            if ($outputFile) {
                $zipfile = $outputFile;
            } else {
                $zipfile = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/xml") . ".zip";
            }
            system(sprintf("cd %s && zip -r %s -- * > /dev/null", escapeshellarg($foutdir), escapeshellarg($zipfile)), $ret);
            if (is_file($zipfile)) {
                system(sprintf("rm -fr %s", $foutdir));
                if ($toDownload) {
                    Http_DownloadFile($zipfile, "$exportname.zip", "application/x-zip", false, false, true);
                }
            } else {
                self::exportExit(_("Zip Archive cannot be created"));
            }
        } elseif ($eformat == "Y") {
            if ($outputFile) {
                $xmlfile = $outputFile;
            } else {
                $xmlfile = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/xml") . ".xml";
            }

            $fh = fopen($xmlfile, 'x');
            if ($fh === false) {
                self::exportExit(sprintf("%s (Error creating file '%s')", _("Xml file cannot be created"), htmlspecialchars($xmlfile)));
            }
            /* Print XML header */
            $xml_head
                = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<documents date="%s" author="%s" name="%s">

EOF;
            $xml_head = sprintf(
                $xml_head,
                htmlspecialchars(strftime("%FT%T")),
                htmlspecialchars(\Anakeen\Core\Account::getDisplayName(\Anakeen\Core\ContextManager::getCurrentUser()->getAccountName())),
                htmlspecialchars($exportname)
            );
            $xml_footer = "</documents>";

            $ret = fwrite($fh, $xml_head);
            if ($ret === false) {
                self::exportExit(sprintf("%s (Error writing to file '%s')", _("Xml file cannot be created"), htmlspecialchars($xmlfile)));
            }
            fflush($fh);
            /* chdir into dir containing the XML files
             * and concatenate them into the output file
            */
            $cwd = getcwd();
            $ret = chdir($foutdir);
            if ($ret === false) {
                self::exportExit(sprintf("%s (Error chdir to '%s')", _("Xml file cannot be created"), htmlspecialchars($foutdir)));
            }

            if ($s->count() > 0) {
                $cmd = sprintf("cat -- *xml | grep -v '<?xml version=\"1.0\" encoding=\"UTF-8\"?>' >> %s", escapeshellarg($xmlfile));
                system($cmd, $ret);
            }

            $ret = chdir($cwd);
            if ($ret === false) {
                self::exportExit(sprintf("%s (Error chdir to '%s')", _("Xml file cannot be created"), htmlspecialchars($cwd)));
            }
            /* Print XML footer */
            $ret = fseek($fh, 0, SEEK_END);
            if ($ret === -1) {
                self::exportExit(sprintf("%s (Error fseek '%s')", _("Xml file cannot be created"), htmlspecialchars($xmlfile)));
            }

            $ret = fwrite($fh, $xml_footer);
            if ($ret === false) {
                self::exportExit(sprintf("%s (Error writing to file '%s')", _("Xml file cannot be created"), htmlspecialchars($xmlfile)));
            }
            fflush($fh);
            fclose($fh);

            if (is_file($xmlfile)) {
                system(sprintf("rm -fr %s", escapeshellarg($foutdir)));

                if (!$outputFile) {
                    Http_DownloadFile($xmlfile, "$exportname.xml", "text/xml", false, false, true);
                }
            } else {
                self::exportExit(_("Xml file cannot be created"));
            }
        }
    }

    protected static function exportExit($err)
    {
        throw new \Anakeen\Exception($err);
    }

    /**
     * Exportation of documents from folder or searches
     *
     * @param string  $fldid                       Folder identifier to use if no "id" http vars
     * @param string  $famid                       Family restriction to filter folder content
     * @param string  $outputPath                  where put export, if wfile outputPath is a directory
     * @param bool    $exportInvisibleVisibilities set to true to export invisible attribute also
     *
     * @param array   $options
     *
     * @return void
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Exception
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     * @throws \Dcp\SearchDoc\Exception
     * @global string $wprof                       Http var : (Y|N) if Y export associated profil also
     * @global string $wfile                       Http var : (Y|N) if Y export attached file export format will be tgz
     * @global string $wident                      Http var : (Y|N) if Y specid column is set with identifier of document
     * @global string $wutf8                       Http var : (Y|N) if Y encoding is utf-8 else iso8859-1
     * @global string $wcolumn                     Http var :  if - export preferences are ignored
     * @global string $eformat                     Http var :  (I|R|F) I: for reimport, R: Raw data, F: Formatted data
     * @global string $selection                   Http var :  JSON document selection object
     */
    public static function exportfld($fldid = "0", $famid = "", $outputPath = "", bool $exportInvisibleVisibilities = false, array $options = [])
    {

        $wprof = !empty($options["wprof"]); // With profile access
        $wfile = !empty($options["wfile"]); // With file contents
        $wident
            = // Profil option type; // With document numeric identifiers
        $fileEncoding = (!empty($options["code"])) ? $options["code"] : "utf8"; // File encoding

        // Profil option type
        $profilType = (!empty($options["wproftype"])) ? $options["wproftype"] : \Anakeen\Exchange\ExportDocument::useAclAccountType;


        $wutf8 = ($fileEncoding !== "iso8859-15");

        $nopref = true; // no preference read
        // Export format "I", "R", "F", "X", "Y"
        $eformat = (!empty($options["eformat"])) ? $options["eformat"] : "I";

        // character to delimiter fields - generaly a comma
        $csvSeparator = (!empty($options["csv-separator"])) ? $options["csv-separator"] : ";";


        $csvEnclosure = (!empty($options["csv-enclosure"])) ? $options["csv-enclosure"] : '"';


        \Anakeen\Core\Utils\System::setMaxExecutionTimeTo(3600);
        $exportCollection = new \Anakeen\Core\ExportCollection();
        $exportCollection->setOutputFormat($eformat);
        $exportCollection->setExportProfil($wprof);
        $exportCollection->setExportDocumentNumericIdentiers($wident);
        $exportCollection->setUseUserColumnParameter(!$nopref);
        $exportCollection->setOutputFileEncoding($wutf8 ? \Anakeen\Core\ExportCollection::utf8Encoding : \Anakeen\Core\ExportCollection::latinEncoding);
        $exportCollection->setVerifyAttributeAccess(!$exportInvisibleVisibilities);
        $exportCollection->setProfileAccountType($profilType);


        if (!$fldid) {
            \Anakeen\Core\ContextManager::exitError(___("no export folder specified", "sde"));
        }

        $fld = \Anakeen\Core\SEManager::getDocument($fldid);
        if ($famid == "") {
            $famid = $_REQUEST["famid"] ?? '';
        }
        $fname = str_replace(array(
            " ",
            "'"
        ), array(
            "_",
            ""
        ), $fld->getTitle());

        $exportCollection->recordStatus(_("Retrieve documents from database"));

        $s = new \SearchDoc("", $famid);
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
            \Anakeen\Core\ContextManager::exitError(sprintf("export is not allowed to override existing file %s", $outputPath));
        }

        $exportCollection->setOutputFilePath($foutname);
        $exportCollection->setCvsSeparator($csvSeparator);
        $exportCollection->setCvsEnclosure($csvEnclosure);

        try {
            $exportCollection->export();
            if (is_file($foutname)) {
                switch ($eformat) {
                    case \Anakeen\Core\ExportCollection::xmlFileOutputFormat:
                        $fname .= ".xml";
                        $fileMime = "text/xml";
                        break;

                    case \Anakeen\Core\ExportCollection::xmlArchiveOutputFormat:
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
        } catch (\Anakeen\Exception $e) {
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
    public static function deleteContentDirectory($dirname)
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
}
