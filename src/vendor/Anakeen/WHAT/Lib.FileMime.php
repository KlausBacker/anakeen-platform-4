<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Assosiate icon with mime type
 *
 * @author     Anakeen
 * @version    $Id: Lib.FileMime.php,v 1.9 2008/05/06 08:20:43 marc Exp $
 * @package    FDL
 * @subpackage CORE
 */
/**
 */

/**
 * @deprecated use \Anakeen\Core\FileMime::getIconMimeArray
 * @return array
 */
function getIconMimeArray()
{
    return \Anakeen\Core\Utils\FileMime::getIconMimeArray();
}

/**
 * @deprecated use \Anakeen\Core\FileMime::getIconMimeFile
 *
 * @param $sysmime
 *
 * @return string
 */
function getIconMimeFile($sysmime)
{

    return \Anakeen\Core\Utils\FileMime::getIconMimeFile($sysmime);
}

/**
 * return system file mime
 *
 * @param string $f  filename
 * @param string $fn basename of file (can be different of real path)
 *
 * @deprecated  \Anakeen\Core\FileMime::getSysMimeFile
 * return string mime like text/html
 * @return string
 */
function getSysMimeFile($f, $fn = "")
{
    return \Anakeen\Core\Utils\FileMime::getSysMimeFile($f, $fn);
}

/**
 * @param        $f
 * @param string $fn
 *
 * @deprecated  \Anakeen\Core\FileMime::getMimeFile
 * @return bool
 */
function getTextMimeFile($f, $fn = '')
{
    return \Anakeen\Core\Utils\FileMime::getTextMimeFile($f, $fn);
}

/**
 * get current extension from system mime
 *
 * @deprecated \Anakeen\Core\FileMime::getExtension
 *
 * @param string $smime
 *
 * @return string (empty string if no extension found)
 */
function getExtension($smime)
{
    return \Anakeen\Core\Utils\FileMime::getExtension($smime);
}

/**
 * get extension from file name
 *
 * @deprecated \Anakeen\Core\FileMime::getFileExtension
 *
 * @param string $filename
 *
 * @return bool|string
 */
function getFileExtension($filename)
{
    return \Anakeen\Core\Utils\FileMime::getFileExtension($filename);
}

/**
 * get MIME type/text from mime.conf and mime-user.conf files
 *
 * @deprecated use  \Anakeen\Core\FileMime::getMimeFile
 *
 * @param string       $filename
 * @param string $type
 *
 * @return bool
 */
function getMimeFile($filename, $type = 'sys')
{
    return \Anakeen\Core\Utils\FileMime::getMimeFile($filename, $type);
}

/**
 * get number of pages from pdf file
 *
 * @param string $file pdf file path
 *
 * @return int
 */
function getPdfNumberOfPages($file)
{
    $nbpages = 0;
    if (file_exists($file)) {
        $nbpages = intval(trim(shell_exec(sprintf('grep -c "/Type[[:space:]]*/Page\>" %s', escapeshellarg($file)))));
    }
    return $nbpages;
}
