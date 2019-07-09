<?php
/*
 * @author Anakeen
 * @package CONTROL
*/

namespace XMLSchemaCatalogValidator;

class Exception extends \Exception
{
}
class GeneralException extends Exception
{
}
class ValidationException extends Exception
{
}

class Validator
{
    /**
     * @var \DOMDocument
     */
    protected $document = null;
    /**
     * @var string
     */
    protected $catalogFile = null;
    /**
     * @var array
     */
    protected $catalog = array();
    /**
     * @param string $catalog
     * @throws GeneralException
     */
    public function __construct($catalog)
    {
        if (!is_file($catalog) || $catalog === false) {
            throw new GeneralException(sprintf("Catalog file '%s' not found.", $catalog));
        }
        if (!is_readable($catalog)) {
            throw new GeneralException(sprintf("Catalog file '%s' is not readable.", $catalog));
        }
        $this->catalogFile = $catalog;
        $this->initializeCatalog();
    }
    private function initializeCatalog()
    {
        $catalogDir = realpath(dirname($this->catalogFile));
        if ($catalogDir === false) {
            throw new GeneralException(sprintf("Could not get catalog directory from catalog file '%s'.", $this->catalogFile));
        }
        $dom = new \DOMDocument();
        libxml_clear_errors();
        if ($dom->load($this->catalogFile) === false) {
            $err = $this->formatLibXMLError(libxml_get_last_error());
            throw new GeneralException("Error loading catalog file '%s': %s", $this->catalogFile, $err);
        }
        $xpath = new \DOMXpath($dom);
        $xpath->registerNamespace('ns', 'urn:oasis:names:tc:entity:xmlns:xml:catalog');
        $nodeList = $xpath->query('/ns:catalog/ns:system');
        /**
         * @var \DOMElement $node
         */
        foreach ($nodeList as $node) {
            $this->catalog[$node->getAttribute('systemId') ] = $catalogDir . DIRECTORY_SEPARATOR . $node->getAttribute('uri');
        }
    }
    private function external_entity_loader($public, $system, $context)
    {
        if (isset($this->catalog[$system])) {
            return $this->catalog[$system];
        }
        if (is_file($system)) {
            return $system;
        }
        return null;
    }
    public function validate($urn)
    {
        libxml_set_external_entity_loader(array(
            $this,
            'external_entity_loader'
        ));
        libxml_clear_errors();
        if ($this->document->schemaValidate($urn) === false) {
            $err = $this->formatLibXMLError(libxml_get_last_error());
            throw new ValidationException($err);
        }
        return true;
    }
    public function loadData($xmlData)
    {
        $dom = new \DOMDocument();
        libxml_clear_errors();
        if ($dom->loadXML($xmlData) === false) {
            $err = $this->formatLibXMLError(libxml_get_last_error());
            throw new GeneralException(sprintf("Error loading XML data: %s", $err));
        }
    }
    public function loadFile($file)
    {
        $dom = new \DOMDocument();
        libxml_clear_errors();
        if ($dom->load($file) === false) {
            $err = $this->formatLibXMLError(libxml_get_last_error());
            throw new GeneralException(sprintf("Error loading XML file '%s': %s", $file, $err));
        }
        $this->document = $dom;
        return $this;
    }
    public function loadDOMDocument(\DOMDocument & $dom)
    {
        $this->document = $dom;
        return $this;
    }
    private function formatLibXMLError($err)
    {
        if ($err === false) {
            return '';
        }
        return sprintf("(line %d, column %d, level %d, code %d) %s", $err->line, $err->column, $err->level, $err->code, $err->message);
    }
}
