<?php
/**
 * Import Set of documents and files with directories
 */

namespace Anakeen\Exchange;

use Anakeen\Core\SEManager;
use Anakeen\Exception;

class ImportTar
{
    const TAREXTRACT = "/extract/";

    public static function getTarExtractDir($tar)
    {

        $dtar = sprintf("%s/var/upload/", DEFAULT_PUBDIR);

        return $dtar . "/" . \Anakeen\Core\ContextManager::getCurrentUser()->login . self::TAREXTRACT . $tar . "_D";
    }


    /**
     * import a directory files
     *
     * @param string $ldir    local directory path
     * @param int    $dirid   folder id to add new documents
     * @param int    $famid   default family for raw files
     * @param int    $dfldid
     * @param bool   $onlycsv if true only fdl.csv file is imported
     * @param bool   $analyze dry-mode it true
     *
     * @return array
     */
    public static function importDirectory(&$ldir, $dirid = 0, int $famid = 7, int $dfldid = 2, $onlycsv = false, $analyze = false)
    {
        // first see if fdl.csv file
        global $importedFiles;

        $dbaccess = \Anakeen\Core\DbManager::getDbAccess();
        $tr = array();
        if (is_dir($ldir)) {
            if ($handle = opendir($ldir)) {
                $lfamid = 0;
                $lfldid = 0;
                while (false !== ($file = readdir($handle))) {
                    $absfile = str_replace("//", "/", "$ldir/$file");
                    if (is_file($absfile) && ($file == "fdl.csv")) {
                        $tr = ImportTar::analyzeCsv($absfile, $dirid, $lfamid, $lfldid, $analyze);
                    }
                }
                $lfamid=intval($lfamid);
                if ($lfamid > 0) {
                    $famid = $lfamid;
                } // set local default family identifier
                if ($lfldid > 0) {
                    $dfldid = $lfldid;
                } // set local default family folder identifier
                rewinddir($handle);
                /* This is the correct way to loop over the directory. */
                $defaultdoc = SEManager::createDocument($famid);
                if (!$defaultdoc) {
                    \Anakeen\LogManager::warning(sprintf(_("you cannot create this kind [%s] of document"), $famid));
                }
                $fimgattr = null;
                if (($lfamid === 0) && ($famid === 7)) {
                    $defaultimg = SEManager::createDocument("IMAGE");
                    $fimgattr = $defaultimg->GetFirstFileAttributes();
                }
                $newdir = SEManager::createDocument($dfldid);
                if (!$newdir) {
                    \Anakeen\LogManager::warning(sprintf(_("you cannot create this kind [%s] of folder"), $dfldid));
                }
                $ffileattr = $defaultdoc->GetFirstFileAttributes();

                $dir = null;
                if ($dirid > 0) {
                    /**
                     * @var \Anakeen\SmartStructures\Dir\DirHooks $dir
                     */
                    $dir = SEManager::getDocument($dirid);
                }

                $nfile = 0;
                while (false !== ($file = readdir($handle))) {
                    $nfile++;
                    $absfile = str_replace("//", "/", "$ldir/$file");
                    $level = substr_count($absfile, "/");
                    $index = "f$level/$nfile";
                    if (is_file($absfile)) {
                        if (!$onlycsv) { // add also unmarked files
                            if (!isset($importedFiles[$absfile])) {
                                if (!\Anakeen\Core\Utils\Strings::isUTF8($file)) {
                                    $file = utf8_encode($file);
                                }
                                if (!\Anakeen\Core\Utils\Strings::isUTF8($ldir)) {
                                    $ldir = utf8_encode($ldir);
                                }
                                $tr[$index] = array(
                                    "err" => ($defaultdoc) ? "" : sprintf(_("you cannot create this kind [%s] of document"), $famid),
                                    "folderid" => 0,
                                    "foldername" => $ldir,
                                    "filename" => $file,
                                    "title" => "$file",
                                    "specmsg" => "",
                                    "id" => "",
                                    "anaclass" => "fileclass",
                                    "familyid" => 0,
                                    "familyname" => "",
                                    "action" => ""
                                );
                                $err = \Anakeen\Exchange\Utils::addVaultFile($dbaccess, $absfile, $analyze, $vfid);

                                if ($err != "") {
                                    $tr[$index]["err"] = $err;
                                } else {
                                    $fattr = "";
                                    if (($lfamid === 0) && ($famid === 7) && (substr($vfid, 0, 5) == "image")) {
                                        $ddoc = &$defaultimg;
                                        $fattr = $fimgattr->id;
                                    } else {
                                        $ddoc = &$defaultdoc;
                                        if ($ffileattr) {
                                            $fattr = $ffileattr->id;
                                        } else {
                                            $tr[$index]["err"] = "no file attribute";
                                        }
                                    }

                                    $tr[$index]["familyname"] = $ddoc->fromname;
                                    $tr[$index]["familyid"] = $ddoc->fromid;
                                    $tr[$index]["action"] = "To be add";
                                    if (!$analyze) {
                                        $ddoc->Init();
                                        $ddoc->setValue($fattr, $vfid);
                                        $err = $ddoc->store();
                                        if ($err != "") {
                                            $tr[$index]["action"] = "Not added";
                                            $tr[$index]["err"] = $err;
                                        } else {
                                            $ddoc->addHistoryEntry(sprintf("create by import from archive %s", substr(basename($ldir), 0, -2)));
                                            $tr[$index]["action"] = "Added";
                                            $tr[$index]["id"] = $ddoc->id;

                                            if ($dirid > 0) {
                                                $dir->insertDocument($ddoc->id);
                                            }
                                            $tr[$index]["title"] = $ddoc->getTitle();
                                            $tr[$index]["id"] = $ddoc->id;
                                            $tr[$index]["familyid"] = $ddoc->fromid;
                                            $tr[$index]["familyname"] = $ddoc->fromname;
                                        }
                                    }
                                }
                            }
                        }
                    } elseif (is_dir($absfile) && ($file[0] != '.')) {
                        if (!\Anakeen\Core\Utils\Strings::isUTF8($file)) {
                            $file = utf8_encode($file);
                        }
                        if (!\Anakeen\Core\Utils\Strings::isUTF8($ldir)) {
                            $ldir = utf8_encode($ldir);
                        }

                        if ((!$onlycsv) || (!preg_match("/^[0-9]+-.*_D$/i", $file))) {
                            $tr[$index] = array(
                                "err" => ($newdir) ? "" : sprintf(_("you cannot create this kind [%s] of folder"), $dfldid),
                                "folderid" => 0,
                                "foldername" => $ldir,
                                "filename" => $file,
                                "title" => "$file",
                                "specmsg" => "",
                                "id" => "",
                                "anaclass" => "fldclass",
                                "familyid" => $newdir->fromid,
                                "familyname" => $newdir->fromname,
                                "action" => "To be add"
                            );
                            if (!$analyze) {
                                $newdir->Init();
                                $newdir->setTitle($file);
                                $err = $newdir->add();
                                if ($err != "") {
                                    $tr[$index]["action"] = "Not added";
                                } else {
                                    $tr[$index]["action"] = "Added";
                                    $tr[$index]["id"] = $newdir->id;
                                    if ($dirid > 0) {
                                        $dir->insertDocument($newdir->id);
                                    }
                                }
                            }
                        }
                        $itr = self::importDirectory($absfile, $newdir->id, $famid, $dfldid, $onlycsv, $analyze);
                        $tr = array_merge($tr, $itr);
                    }
                }
                closedir($handle);
                return $tr;
            }
        } else {
            $err = sprintf("cannot open local directory %s", $ldir);
            return array(
                "err" => $err
            );
        }
        return array();
    }

    public static function analyzeCsv($fdlcsv, $dirid, &$famid, &$dfldid, $analyze, $csvLinebreak = '\n')
    {
        $tr = array();
        $fcsv = fopen($fdlcsv, "r");
        if ($fcsv) {
            $ldir = dirname($fdlcsv);
            $nline = 0;
            $nbdoc = 0;
            $tkeys = [];
            $tcolorder = array();
            $separator = $enclosure = "auto";
            \Anakeen\Exchange\ImportDocumentDescription::detectAutoCsvOptions($fdlcsv, $separator, $enclosure);
            if ($separator == '') {
                $separator = ';';
            }
            if ($enclosure == '') {
                $enclosure = '"';
            }
            while ($data = fgetcsv($fcsv, 0, $separator, $enclosure)) {
                $nline++;
                $level = substr_count($ldir, "/");
                $index = "c$level/$nline";
                $tr[$index] = array(
                    "err" => "",
                    "msg" => "",
                    "specmsg" => "",
                    "folderid" => 0,
                    "foldername" => "",
                    "filename" => "",
                    "title" => "",
                    "id" => "",
                    "values" => array(),
                    "familyid" => 0,
                    "familyname" => "",
                    "action" => "-"
                );
                if ($csvLinebreak) {
                    $data = array_map(function ($v) use ($csvLinebreak) {
                        return str_replace($csvLinebreak, "\n", $v);
                    }, $data);
                }
                switch ($data[0]) {
                    // -----------------------------------

                    case "DFAMID":
                        $famid = $data[1];
                        //print "\n\n change famid to $famid\n";
                        break;
                    // -----------------------------------

                    case "DFLDID":
                        $dfldid = $data[1];
                        //print "\n\n change dfldid to $dfldid\n";
                        break;

                    case "ORDER":
                        if (is_numeric($data[1])) {
                            $orfromid = $data[1];
                        } else {
                            $orfromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
                        }

                        $tcolorder[$orfromid] = \Anakeen\Exchange\Utils::getOrder($data);
                        $tr[$index]["action"] = sprintf(_("new column order %s"), implode(" - ", $tcolorder[$orfromid]));
                        break;

                    case "KEYS":
                        if (is_numeric($data[1])) {
                            $orfromid = $data[1];
                        } else {
                            $orfromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
                        }

                        $tkeys[$orfromid] = \Anakeen\Exchange\Utils::getOrder($data);
                        if (($tkeys[$orfromid][0] == "") || (count($tkeys[$orfromid]) === 0)) {
                            $tr[$index]["err"] = sprintf(_("error in import keys : %s"), implode(" - ", $tkeys[$orfromid]));
                            unset($tkeys[$orfromid]);
                            $tr[$index]["action"] = "ignored";
                        } else {
                            $tr[$index]["action"] = sprintf(_("new import keys : %s"), implode(" - ", $tkeys[$orfromid]));
                        }
                        break;

                    case "DOC":
                        if (is_numeric($data[1])) {
                            $fromid = $data[1];
                        } else {
                            $fromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
                        }
                        if (isset($tkeys[$fromid])) {
                            $tk = $tkeys[$fromid];
                        } else {
                            $tk = array(
                                "title"
                            );
                        }
                        $tr[$index] = \Anakeen\Exchange\Utils::csvAddDoc($data, $dirid, $analyze, $ldir, "update", $tk, array(), $tcolorder[$fromid]);
                        if ($tr[$index]["err"] == "") {
                            $nbdoc++;
                        }

                        break;
                }
            }
            fclose($fcsv);
        }
        return $tr;
    }

    /**
     * decode characters wihich comes from windows zip
     *
     * @param $s string to decode
     *
     * @return string decoded string
     */
    public static function WNGBdecode($s)
    {
        $td = array(
            144 => "É",
            130 => "é",
            133 => "à",
            135 => "ç",
            138 => "è",
            151 => "ù",
            212 => "È",
            210 => "Ê",
            128 => "Ç",
            183 => "À",
            136 => "ê",
            150 => "û",
            147 => "ô",
            137 => "ë",
            139 => "ï"
        );

        $s2 = $s;
        for ($i = 0; $i < strlen($s); $i++) {
            if (isset($td[ord($s[$i])])) {
                $s2[$i] = $td[ord($s[$i])];
            }
        }
        return $s2;
    }

    /**
     * rename file name which comes from windows zip
     *
     * @param string $ldir directory to decode
     *
     * @return string empty string on success, non-empty string with error message on failure
     */
    public static function WNGBDirRename($ldir)
    {
        $handle = opendir($ldir);
        if ($handle === false) {
            return sprintf(_("Error opening directory '%s'."), $ldir);
        }
        while (false !== ($file = readdir($handle))) {
            if ($file[0] != ".") {
                $afile = "$ldir/$file";

                if (is_file($afile)) {
                    if (rename($afile, "$ldir/" . self::WNGBdecode($file)) === false) {
                        return sprintf(_("Error renaming '%s' to '%s'."), $afile, self::WNGBdecode($file));
                    };
                } elseif (is_dir($afile)) {
                    if (($err = self::WNGBDirRename($afile)) != '') {
                        return $err;
                    }
                }
            }
        }

        closedir($handle);
        if (rename($ldir, self::WNGBdecode($ldir)) === false) {
            return sprintf(_("Error renaming '%s' to '%s'."), $ldir, self::WNGBdecode($ldir));
        }
        return '';
    }

    public static function extractTar($tar, $untardir)
    {
        $tar = realpath($tar);
        $mime = trim(shell_exec(sprintf("file -b %s", escapeshellarg($tar))));
        $mime = substr($mime, 0, strpos($mime, " "));

        $err = '';
        try {
            switch ($mime) {
                case "gzip":
                case "application/x-compressed-tar":
                case "application/x-gzip":
                    exec(sprintf("rm -rf %s 2>&1", escapeshellarg($untardir)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error deleting directory '%s': %s"), $untardir, join("\n", $output)));
                    }
                    exec(sprintf("mkdir -p %s 2>&1", escapeshellarg($untardir)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error creating directory '%s': %s"), $untardir, join("\n", $output)));
                    }
                    exec(sprintf("tar -C %s -zxf %s 2>&1", escapeshellarg($untardir), escapeshellarg($tar)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error extracting archive '%s' in '%s': %s"), $tar, $untardir, join("\n", $output)));
                    }
                    break;

                case "bzip2":
                    exec(sprintf("rm -rf %s 2>&1", escapeshellarg($untardir)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error deleting directory '%s': %s"), $untardir, join("\n", $output)));
                    }
                    exec(sprintf("mkdir -p %s 2>&1", escapeshellarg($untardir)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error creating directory '%s': %s"), $untardir, join("\n", $output)));
                    }
                    exec(sprintf("tar -C %s -jxf %s 2>&1", escapeshellarg($untardir), escapeshellarg($tar)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error extracting archive '%s' in '%s': %s"), $tar, $untardir, join("\n", $output)));
                    }
                    break;

                case "Zip":
                case "application/x-zip-compressed":
                case "application/x-zip":
                    exec(sprintf("rm -rf %s 2>&1", escapeshellarg($untardir)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error deleting directory '%s': %s"), $untardir, join("\n", $output)));
                    }
                    exec(sprintf("mkdir -p %s 2>&1", escapeshellarg($untardir)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error creating directory '%s': %s"), $untardir, join("\n", $output)));
                    }
                    exec(sprintf("unzip -d %s %s 2>&1", escapeshellarg($untardir), escapeshellarg($tar)), $output, $status);
                    if ($status !== 0) {
                        throw new Exception(sprintf(_("Error extracting archive '%s' in '%s': %s"), $tar, $untardir, join("\n", $output)));
                    }
                    break;

                default:
                    throw new Exception(sprintf(_("Unsupported archive format '%s' for archive '%s'."), $mime, $tar));
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
        }
        return $err;
    }


    public static function hasfdlpointcsv($dir)
    {
        $found = file_exists("$dir/fdl.csv");
        if (!$found) {
            if ($handle = opendir($dir)) {
                while ((!$found) && (false !== ($file = readdir($handle)))) {
                    if (is_dir("$dir/$file")) {
                        $found = file_exists("$dir/$file/fdl.csv");
                    }
                }
                closedir($handle);
            }
        }
        return $found;
    }
}
