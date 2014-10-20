<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    protected $catalog = null;
    /**
     * @var bool|string
     */
    protected $env_XML_CATALOG_FILES = false;
    const XML_CATALOG_FILES = 'XML_CATALOG_FILES';
    public function __construct($catalog)
    {
        if (!is_file($catalog) || $catalog === false) {
            throw new GeneralException(sprintf("Catalog file '%s' not found.", $catalog));
        }
        if (!is_readable($catalog)) {
            throw new GeneralException(sprintf("Catalog file '%s' is not readable.", $catalog));
        }
        $this->catalog = $catalog;
    }
    private function set_XML_CATALOG_FILES()
    {
        $this->env_XML_CATALOG_FILES = getenv(self::XML_CATALOG_FILES);
        putenv(sprintf('%s=%s', self::XML_CATALOG_FILES, $this->catalog));
    }
    private function restore_XML_CATALOG_FILES()
    {
        if ($this->env_XML_CATALOG_FILES === false) {
            putenv(self::XML_CATALOG_FILES);
        } else {
            putenv(sprintf("%s=%s", self::XML_CATALOG_FILES, $this->env_XML_CATALOG_FILES));
        }
    }
    public function validate($urn)
    {
        $this->set_XML_CATALOG_FILES();
        libxml_clear_errors();
        if ($this->document->schemaValidate($urn) === false) {
            $this->restore_XML_CATALOG_FILES();
            $err = $this->formatLibXMLError(libxml_get_last_error());
            throw new ValidationException($err);
        }
        $this->restore_XML_CATALOG_FILES();
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
