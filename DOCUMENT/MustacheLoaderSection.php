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
        
        foreach ($tplPart as $index => $content) {
            if ($index === "file" && is_string($content)) {
                $tplPart = file_get_contents($content);
                return;
            } else {
                $this->getTemplatePart($tplPart[$index]);
            }
        }
    }
    /**
     * Load a Template by name.
     *
     *
     * @param string $name
     *
     * @throws Exception
     * @return string Mustache Template source
     */
    public function load($name)
    {
        $delimiter = sprintf('{{=%s %s=}}', $this->delimiterStartTag, $this->delimiterEndTag);
        if (preg_match('/^templates:(.*)$/', $name, $reg)) {
            $index = $reg[1];
            return $delimiter . json_encode($this->getTemplates($index));
        }
        if ($name === "templates") {
            return $delimiter . json_encode($this->getTemplates(null));
        } else {
            throw new Mustache_Exception_UnknownTemplateException($name);
        }
    }
}
