<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package CONTROL
*/

require_once 'class/Class.DOMDocumentCache.php';

class DOMDocumentCacheFactory
{
    private static $cache = array();
    /**
     * @param $filename
     * @param int $options
     * @return bool|DOMDocumentCache
     * @throws Exception
     */
    public static function load($filename, $options = 0)
    {
        $realFilename = realpath($filename);
        $dom = self::cacheGet($realFilename);
        if ($dom !== false) {
            return $dom;
        }
        $dom = new DOMDocumentCache();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if ($dom->load($filename, $options) === false) {
            throw new Exception(self::lastXMLError());
        }
        self::cacheSet($realFilename, $dom);
        return self::cacheGet($realFilename);
    }
    private static function cacheSet($filename, DOMDocument & $dom)
    {
        self::$cache[$filename] = $dom;
    }
    private static function cacheGet($filename)
    {
        if (!isset(self::$cache[$filename])) {
            return false;
        }
        return self::$cache[$filename];
    }
    private static function cacheUnset(DOMDocument & $dom)
    {
        foreach (self::$cache as $filename => $obj) {
            if ($dom === $obj) {
                $dom = self::$cache[$filename];
                unset(self::$cache[$filename]);
                return $dom;
            }
        }
        return false;
    }
    private static function lastXMLError()
    {
        if (($err = libxml_get_last_error()) === false) {
            return '';
        }
        return self::formatLibXMLError($err);
    }
    private static function formatLibXMLError(\LibXMLError $err)
    {
        return sprintf("(line %d, column %d, level %d, code %d) %s", $err->line, $err->column, $err->level, $err->code, $err->message);
    }
}
