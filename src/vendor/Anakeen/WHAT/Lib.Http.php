<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Set of usefull HTTP functions
 *
 * @author Anakeen
 * @version $Id: Lib.Http.php,v 1.38 2008/11/28 12:48:06 eric Exp $
 * @package FDL
 * @subpackage CORE
 */


/**
 * return value of an http parameter
 * @param string $name parameter key
 * @param string $def default value if parameter is not set
 * @param string $scope The scope for the search of the value ('zone' for $ZONE_ARGS, 'get' for $_GET, 'post' for $_POST and 'all' for searching in all)
 * @return string
 */
function getHttpVars($name, $def = "", $scope = "all")
{
    global $_GET, $_POST, $ZONE_ARGS;

    if (($scope == "all" || $scope == "zone") && isset($ZONE_ARGS[$name])) {
        // try zone args first : it is set be Layout::execute for a zone
        return ($ZONE_ARGS[$name]);
    }
    if (($scope == "all" || $scope == "get") && isset($_GET[$name])) {
        return $_GET[$name];
    }
    if (($scope == "all" || $scope == "post") && isset($_POST[$name])) {
        return $_POST[$name];
    }

    return ($def);
}


function SetHttpVar($name, $def)
{
    global $ZONE_ARGS;
    if ($def == "") {
        unset($ZONE_ARGS[$name]);
    } else {
        $ZONE_ARGS[$name] = $def;
    }
}

function GetMimeType($ext)
{
    $mimes = file("/etc/mime.types");
    foreach ($mimes as $v) {
        if (substr($v, 0, 1) == "#") {
            continue;
        }
        $tab = preg_split('/\s+/', $v);
        if ((isset($tab[1])) && ($tab[1] == $ext)) {
            return ($tab[0]);
        }
    }
    return ("text/any");
}

function GetExt($mime_type)
{
    $mimes = file("/etc/mime.types");
    foreach ($mimes as $v) {
        if (substr($v, 0, 1) == "#") {
            continue;
        }
        $tab = preg_split('\s+/', $v);
        if ((isset($tab[0])) && ($tab[0] == $mime_type)) {
            if (isset($tab[1])) {
                return ($tab[1]);
            } else {
                return ("");
            }
        }
    }
    return ("");
}

/**
 * Send a response with the content of a local file to be downloaded by the client
 *
 * No output should be generated on stdout after calling this function.
 *
 * @param string $filename pathname of the file that will be sent to the client (e.g. "/tmp/foo.pdf")
 * @param string $name the basename of the file (e.g. "foo.pdf")
 * @param string $mime_type the Content-Type MIME type of the response (e.g. "application/pdf")
 * @param bool $inline Send the data with inline Content-Disposition (default = FALSE)
 * @param bool $cache Instruct clients and/or proxies to cache the response for 24h (default = TRUE)
 * @param bool $deleteafter Delete the $filename file when done (default = FALSE)
 * @return void
 */
function Http_DownloadFile($filename, $name, $mime_type = '', $inline = false, $cache = true, $deleteafter = false)
{
    require_once 'FDL/Class.FileMimeConfig.php';

    if (!file_exists($filename)) {
        printf(_("file not found : %s"), $filename);
        return;
    }

    if (php_sapi_name() !== 'cli') {
        // Double quote not supported by all browsers - replace by minus
        $name = str_replace('"', '-', $name);
        $uName = iconv("UTF-8", "ASCII//TRANSLIT", $name);
        $name = rawurlencode($name);
        $fileMimeConfig = new \Dcp\FileMimeConfig();
        if ($inline && !$fileMimeConfig->isInlineAllowed($mime_type)) {
            /* Override requested inline mode as it is forbidden */
            $inline = false;
        }
        if (!$inline) {
            header("Content-Disposition: attachment;filename=\"$uName\";filename*=UTF-8''$name;");
        } else {
            header("Content-Disposition: inline;filename=\"$uName\";filename*=UTF-8''$name;");
        }

        if ($cache) {
            $duration = 24 * 3600;
            header("Cache-Control: private, max-age=$duration"); // use cache client (one hour) for speed optimsation
            header("Expires: " . gmdate("D, d M Y H:i:s T\n", time() + $duration)); // for mozilla
        } else {
            header("Cache-Control: private");
        }
        header("Pragma: "); // HTTP 1.0
        if ($inline && substr($mime_type, 0, 4) == "text" && substr($mime_type, 0, 9) != "text/html" && substr($mime_type, 0, 8) != "text/xml") {
            $mime_type = preg_replace("_text/([^;]*)_", "text/plain", $mime_type);
        }

        header("Content-type: " . $mime_type);
        header("X-Content-Type-Options: nosniff");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($filename));
        $buflen = ob_get_length();
        if ($buflen !== false && $buflen > 0) {
            ob_clean();
        }
        flush();
    }
    readfile($filename);
    if ($deleteafter) {
        unlink($filename);
    }
    exit;
}


