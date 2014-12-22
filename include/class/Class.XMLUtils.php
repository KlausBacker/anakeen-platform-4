<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package CONTROL
*/

class XMLUtils
{
    public static function formatLibXMLError(\LibXMLError $err)
    {
        return sprintf("(line %d, column %d, level %d, code %d) %s", $err->line, $err->column, $err->level, $err->code, $err->message);
    }
    public static function getLastXMLError()
    {
        $err = libxml_get_last_error();
        if ($err !== false) {
            return self::formatLibXMLError($err);
        }
        return '';
    }
    /**
     * @param DOMDocument $dom
     * @param string $ns
     * @return bool
     */
    public static function DOMDocumentHaveNamespace(DOMDocument $dom, $ns)
    {
        return ($dom->documentElement->isDefaultNamespace($ns) || ($dom->documentElement->prefix != '' && $dom->documentElement->lookupNamespaceUri($dom->documentElement->prefix) == $ns));
    }
    /**
     * Add default namespace to DOMDocument
     *
     * @param DOMDocument $dom
     * @param string $ns
     * @return DOMDocument
     * @throws Exception
     */
    public static function upgradeDOMDocumentWithNamespace(DOMDocument $dom, $ns)
    {
        /*
         * Create new DOM document in the requested namespace
        */
        $dom2 = new DOMDocument();
        $dom2->preserveWhiteSpace = false;
        $dom2->formatOutput = true;
        $root2 = $dom2->createElementNS($ns, 'module');
        /*
         * Copy root node attributes
        */
        $attrList = $dom->documentElement->attributes;
        for ($i = 0; $i < $attrList->length; $i++) {
            $attr = $attrList->item($i);
            $root2->setAttribute($attr->nodeName, $attr->nodeValue);
        }
        /*
         * Then copy all childs
        */
        $dom2->appendChild($root2);
        $childList = $dom->documentElement->childNodes;
        for ($i = 0; $i < $childList->length; $i++) {
            $import = $dom2->importNode($childList->item($i) , true);
            $root2->appendChild($import);
        }
        $xml = $dom2->saveXML();
        $dom2 = new DOMDocument();
        if ($dom2->loadXML($xml) === false) {
            throw new Exception(self::getLastXMLError());
        }
        return $dom2;
    }
    /**
     * Remove default namespace from DOMDocument
     *
     * @param DOMDocument $dom
     * @param string $ns
     * @return DOMDocument
     * @throws Exception
     */
    public static function removeNamespaceFromDOMDocument(DOMDocument $dom, $ns)
    {
        /*
         * Create a new DOMDocument with a root element
         * having the same xmlns="xxx"
        */
        $dom2 = new DOMDocument();
        $dom2->preserveWhiteSpace = false;
        $dom2->formatOutput = true;
        $root2 = $dom2->createElementNS($ns, 'root');
        /*
         * Import the original root element as a child
        */
        $import = $dom2->importNode($dom->documentElement, true);
        $root2->appendChild($import);
        $dom2->appendChild($root2);
        /*
         * Save back the imported root node, which should be without xmlns="xxx"
        */
        $xml = $dom2->saveXML($import);
        $dom2 = new DOMDocument();
        if ($dom2->loadXML($xml) === false) {
            throw new Exception(XMLUtils::getLastXMLError());
        }
        return $dom2;
    }
    /**
     * Validate some basic elements required for legacy info.xml handling
     *
     * @param DOMDocument $dom
     * @return bool
     * @throws Exception
     */
    public static function isBasicModuleDOMDocument(DOMDocument & $dom, &$err)
    {
        if ($dom->documentElement->tagName != 'module') {
            $err = sprintf("Root node 'module' not found in 'info.xml'.");
            return false;
        }
        if (!$dom->documentElement->hasAttribute('name')) {
            $err = sprintf("Missing attribute 'name' in 'module' node.");
            return false;
        }
        return true;
    }
}
