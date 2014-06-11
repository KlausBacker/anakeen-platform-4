<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

use Dcp\ApiUsage\Exception;

class DocumentRender
{
    /**
     * @var RenderConfig
     */
    public $renderConfig = null;
    protected $renderFile = '';
    protected $delimiterStartTag = '[[';
    protected $delimiterEndTag = ']]';
    /**
     * @var \Doc document to use
     */
    protected $document = null;
    
    public function __construct()
    {
    }
    /**
     * Load a  render configuration file
     * @param string $file load render configuration
     * @throws \Dcp\Ui\Exception
     */
    public function loadConfiguration(RenderConfig $renderConfig)
    {
        $this->renderConfig = $renderConfig;
    }
    
    protected function getCSSReferences()
    {
        $s = array();
        $ref = $this->renderConfig->getCssReferences();
        if (!is_array($ref)) {
            throw new Exception("UI0010", gettype($ref));
        }
        foreach ($ref as $cssRef) {
            $s[] = array(
                "cssFile" => $cssRef
            );
        }
        return $s;
    }
    
    protected function getJSReferences()
    {
        $s = array();
        $ref = $this->renderConfig->getJsReferences();
        if (!is_array($ref)) {
            throw new Exception("UI0011", gettype($ref));
        }
        foreach ($ref as $jsRef) {
            $s[] = array(
                "jsFile" => $jsRef
            );
        }
        return $s;
    }

    protected function getRequireReferences()
    {
        $ref = $this->renderConfig->getRequireReference();
        if (!is_array($ref)) {
            throw new Exception("UI0011", gettype($ref));
        }
        return $ref;
    }
    
    protected function getMainTemplate()
    {
        return $this->renderConfig->getdocumentTemplate();
    }
    protected function internalRender()
    {

        $option=array(
            'cache' => DEFAULT_PUBDIR.'/var/cache/mustache',
            'cache_file_mode' => 0600,
            'cache_lambda_templates' => true
        );
        $me = new \Mustache_Engine($option);

        $fl = new MustacheLoaderSection($this->renderConfig->getTemplates() , $this->delimiterStartTag, $this->delimiterEndTag);
        $fl->setDocument($this->document);
        $me->setPartialsLoader($fl);
        $delimiter = sprintf('{{=%s %s=}}', $this->delimiterStartTag, $this->delimiterEndTag);
        $docController = new DocumentTemplateContext($this->document);
        
        $docController->offsetSet("cssReference", function ()
        {
            return $this->getCssReferences();
        });
        
        $docController->offsetSet("renderOptions", function ()
        {
            return JsonHandler::encodeForHTML($this->renderConfig->getOptions());
        });
        $docController->offsetSet("renderMenu", function ()
        {
            return JsonHandler::encodeForHTML($this->renderConfig->getMenu($this->document));
        });
        $docController->offsetSet("jsReference", function ()
        {
            return $this->getJsReferences();
        });
        $docController->offsetSet("requireReference", function () {
            return $this->getRequireReferences();
        });
        
        return $me->render($delimiter . $this->getMainTemplate() , $docController);
    }
    
    public function render(\Doc $doc)
    {
        $this->document = $doc;
        return $this->internalRender();
    }
}
