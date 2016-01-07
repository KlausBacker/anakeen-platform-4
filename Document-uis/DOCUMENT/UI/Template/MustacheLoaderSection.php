<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
        
        $r = $this->templateSection;
        
        if ($name !== null) {
            $indexes = explode(".", $name);
            foreach ($indexes as $index) {
                $r = $r[$index];
            }
        }
        
        $this->getTemplatePart($r);
        
        return $r;
    }
    
    protected function getTemplatePart(array & $tplPart)
    {
        if (!empty($tplPart["content"]) && is_string($tplPart["content"])) {
            $tplPart = $tplPart["content"];
            return;
        }
        if (!empty($tplPart["file"]) && is_string($tplPart["file"])) {
            $tplPart = file_get_contents($tplPart["file"]);
            return;
        }
        
        foreach ($tplPart as $index => $content) {
            $this->getTemplatePart($tplPart[$index]);
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
