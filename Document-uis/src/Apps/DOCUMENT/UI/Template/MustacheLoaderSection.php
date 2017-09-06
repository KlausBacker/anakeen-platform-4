<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use Mustache_Exception_UnknownTemplateException;

class MustacheLoaderSection implements \Mustache_Loader
{
    
    protected $templateSection = array();
    protected $delimiterStartTag = '[[';
    protected $delimiterEndTag = ']]';
    /***
     * @var \Doc
    */
    protected $document;
    public function __construct(array $tplSectionFile, $delimiterStartTag = '[[', $delimiterEndTag = ']]')
    {
        $this->templateSection = $tplSectionFile;
        $this->delimiterStartTag = $delimiterStartTag;
        $this->delimiterEndTag = $delimiterEndTag;
    }
    
    public function setDocument(\Doc $doc)
    {
        $this->document = $doc;
    }
    
    protected function getTemplates($name)
    {
        
        $templateSection = $this->templateSection;
        
        if ($name !== null) {
            $indexes = explode(".", $name);
            foreach ($indexes as $index) {
                $templateSection = $templateSection[$index];
            }
        }
        
        $this->getTemplatePart($templateSection);
        
        return $templateSection;
    }

    /**
     * Replace file reference by the content
     *
     * @param array $templatePart
     *
     * @throws Exception
     */
    protected function getTemplatePart(array & $templatePart)
    {
        if (!empty($templatePart["content"]) && is_string($templatePart["content"])) {
            $templatePart = $templatePart["content"];
            return;
        }
        if (!empty($templatePart["file"]) && is_string($templatePart["file"])) {
            if (!file_exists($templatePart["file"])) {
                throw new Exception("UI0004", $templatePart["file"]);
            }
            $templatePart = file_get_contents($templatePart["file"]);
            return;
        }
        
        foreach ($templatePart as $index => $content) {
            $this->getTemplatePart($templatePart[$index]);
        }
    }
    /**
     * Load a Template by name.
     *
     * @param string $name
     *
     * @throws \Mustache_Exception_UnknownTemplateException
     * @return string Mustache Template source
     */
    public function load($name)
    {
        $delimiter = sprintf('{{=%s %s=}}', $this->delimiterStartTag, $this->delimiterEndTag);
        if (preg_match('/^templates:(.*)$/', $name, $reg)) {
            $index = $reg[1];
            return $delimiter . JsonHandler::encodeForHTML($this->getTemplates($index));
        }
        if ($name === "templates") {
            // need to revert json encode for mustache
            return $delimiter . preg_replace('/\[\[\\\\\/([a-zA-Z0-9]+)\]\]/', '[[/\1]]', JsonHandler::encodeForHTML($this->getTemplates(null)));
        } else {
            throw new Mustache_Exception_UnknownTemplateException($name);
        }
    }
}
