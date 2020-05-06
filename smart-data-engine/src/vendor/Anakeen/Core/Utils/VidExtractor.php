<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Core\Utils;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\Exception;

/**
 * Class vidExtractor
 *
 * Extract VIDs from documents or families (suitable for updating and maintaining the "docvaultindex" table)
 *
 */
class VidExtractor
{
    /**
     * Get list of distinct VIDs (files vault identifier) from a "raw" document (i.e. a row from a SQL query resultset)
     *
     * @param array $raw            Raw document
     * @param array $fileAttrIdList List of attribute's name of type 'file' (if null, the list will be dynamically created from $raw['id'])
     *
     * @return array
     * @throws Exception
     */
    public static function getVidsFromRawDoc($raw, $fileAttrIdList = null)
    {
        if (!is_array($raw)) {
            throw new Exception('VIDEXTRACTOR0001', gettype($raw));
        }
        if ($fileAttrIdList === null) {
            if (!isset($raw['id'])) {
                throw new Exception('VIDEXTRACTOR0002');
            }
            $fileAttrIdList = array();

            $doc = SEManager::getDocument($raw['id']);
            if (!is_object($doc)) {
                throw new Exception('VIDEXTRACTOR0003', $raw['id']);
            }
            $fileAttrList = $doc->getFileAttributes();
            foreach ($fileAttrList as $attrId => $attr) {
                $fileAttrIdList[] = $attrId;
            }
        }
        $vidList = array();
        /* icon */
        if (!empty($raw['icon']) && ($vid = self::parseVid($raw['icon'])) !== false) {
            $vidList[] = $vid;
        }
        /* file attributes */
        foreach ($fileAttrIdList as $attr) {
            if (empty($raw[$attr])) {
                continue;
            }
            $values = $raw[$attr];

            if ($values[0] === '{') {
                $values=SmartElement::rawValueToArray($values);
            } else {
                $values=[$values];
            }

            foreach ($values as $value) {
                if (($vid = self::parseVid($value)) !== false) {
                    $vidList[] = $vid;
                }
            }
        }
        return $vidList;
    }

    /**
     * Extract VIDs from a family object
     *
     * @param \Anakeen\Core\SmartStructure $docfam
     *
     * @return array
     */
    public static function getVidsFromDocFam(\Anakeen\Core\SmartStructure $docfam)
    {
        $values = array();
        /*
         * Track files from docfam.param and docfam.defval
        */
        foreach (array($docfam->getOwnParams(), $docfam->getOwnDefValues()) as $list) {
            foreach ($list as $aid => $value) {
                if (($oattr = $docfam->getAttribute($aid)) === false) {
                    \Anakeen\LogManager::warning(\ErrorCode::getError('VIDEXTRACTOR0004', $aid, $docfam->name));
                    continue;
                }
                if ($oattr->type !== 'file' && $oattr->type !== 'image') {
                    continue;
                }
                if (is_array($value)) {
                    $values = array_merge($values, $value);
                } else {
                    $values[] = $value;
                }
            }
        }
        /*
         * Track files from icon
        */
        if (isset($docfam->icon)) {
            $values[] = $docfam->icon;
        }
        /*
         * Extract vids from values
        */
        $vids = array();
        foreach ($values as $value) {
            if (($vid = self::parseVid($value)) !== false) {
                $vids[$vid] = $vid;
            }
        }
        return $vids;
    }

    /**
     * Extract VIDs from a document object
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc
     *
     * @return array
     */
    public static function getVidsFromDoc(\Anakeen\Core\Internal\SmartElement $doc)
    {
        $vids = array();

        $attrs=$doc->getNormalAttributes();
        foreach ($attrs as $aid => $oattr) {
            if ($oattr->type === "file" || $oattr->type === "image" ||$oattr->type === "htmltext") {
                if ($oattr->inArray()) {
                    $ta = $doc->getMultipleRawValues($aid);
                } else {
                    $ta = [$doc->getRawValue($aid)];
                }
                switch ($oattr->type) {
                    case "file":
                    case "image":
                        /* Track files from attributes */
                        foreach ($ta as $k => $v) {
                            if (($vid = self::parseVid($v)) !== false) {
                                $vids[$vid] = $vid;
                            }
                        }
                        break;
                    case "htmltext":
                        /* Track images from htmltext */
                        $htmlVids = self::getVidFromHtmltext($ta);
                        foreach ($htmlVids as $htmlVid) {
                            $vids[$htmlVid] = $htmlVid;
                        }
                        break;
                }
            }
        }

        /* Track file from icon */
        if (isset($doc->icon)) {
            if (($vid = self::parseVid($doc->icon)) !== false) {
                $vids[$vid] = $vid;
            }
        }
        return $vids;
    }

    /**
     * Extract vid from img tags
     * @param string[] $htmlvalues
     * @return string[]
     * @throws XDOMDocumentException
     */
    public static function getVidFromHtmltext(array $htmlvalues)
    {
        $vids = [];

        $dom = new XDOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        /**
         * @var \libXMLError[] $libXMLErrors
         */
        $libXMLErrors = array();
        $libXMLOpts = LIBXML_NONET;
        if (defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD')) {
            /*
             * LIBXML_HTML_NOIMPLIED is available in libxml >= 2.7.7
             * LIBXML_HTML_NODEFDTD is available in libxml >= 2.7.8
            */
            $libXMLOpts |= LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;
        }
        foreach ($htmlvalues as $htmlvalue) {
            if (!$htmlvalue) {
                continue;
            }
            /*
             * Add a HTML meta header to setup DOMDocument to UTF-8 encoding and no trailing </body></html>
             * to not interfere with the given $html fragment.
            */
            $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . ($htmlvalue);

            $dom->loadHTML($html, $libXMLOpts, $libXMLErrors);

            $imgs = $dom->documentElement->getElementsByTagName('img');

            foreach ($imgs as $img) {
                /** @var \DOMElement $img */
                $tmpvid = $img->getAttribute("data-vid");
                if ($tmpvid) {
                    $vids[] = $tmpvid;
                }
            }
        }
        return $vids;
    }

    /**
     * Parse and extract VID from string
     *
     * @param $vid
     *
     * @return bool
     */
    public static function parseVid($vid)
    {
        if (!preg_match(PREGEXPFILE, $vid, $m)) {
            return false;
        }
        if (!isset($m[2])) {
            return false;
        }
        if ($m[2] === '') {
            return false;
        }
        return $m[2];
    }
}
