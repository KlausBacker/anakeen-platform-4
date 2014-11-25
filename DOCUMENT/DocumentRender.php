<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

use Dcp\ApiUsage\Exception;

use Dcp\HttpApi\V1\DocManager\DocManager;

class DocumentRender
{
    /**
     * @var IRenderConfig
     */
    public $renderConfig = null;
    protected $renderFile = '';
    protected $delimiterStartTag = '[[';
    protected $delimiterEndTag = ']]';
    
    protected $extraKeys = array();
    /**
     * @var \Doc document to use
     */
    protected $document = null;
    
    public function __construct()
    {
    }
    /**
     * Load a  render configuration object
     * @param IRenderConfig $renderConfig load render configuration
     * @throws \Dcp\Ui\Exception
     */
    public function loadConfiguration(IRenderConfig $renderConfig)
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
        
        $option = array(
            'cache' => DEFAULT_PUBDIR . '/var/cache/mustache',
            'cache_file_mode' => 0600,
            'cache_lambda_templates' => true
        );
        $me = new \Mustache_Engine($option);
        /*$fl = new MustacheLoaderSection($this->renderConfig->getTemplates($this->document) , $this->delimiterStartTag, $this->delimiterEndTag);
        $fl->setDocument($this->document);
        $me->setPartialsLoader($fl);*/
        $delimiter = sprintf('{{=%s %s=}}', $this->delimiterStartTag, $this->delimiterEndTag);
        $docController = $this->renderConfig->getContextController($this->document);
        $docController->offsetSet("cssReference", function ()
        {
            return $this->getCssReferences();
        });
        
        $docController->offsetSet("renderOptions", function ()
        {
            return JsonHandler::encodeForHTML($this->renderConfig->getOptions($this->document));
        });
        $docController->offsetSet("renderVisibilities", function ()
        {
            return JsonHandler::encodeForHTML($this->renderConfig->getVisibilities($this->document));
        });
        $docController->offsetSet("renderMenu", function ()
        {
            return JsonHandler::encodeForHTML($this->renderConfig->getMenu($this->document));
        });
        $docController->offsetSet("jsReference", function ()
        {
            return $this->getJsReferences();
        });
        $docController->offsetSet("requireReference", function ()
        {
            return $this->getRequireReferences();
        });
        
        foreach ($this->extraKeys as $key => $data) {
            $docController->offsetSet($key, $data);
        }
        
        return $me->render($delimiter . $this->getMainTemplate() , $docController);
    }
    
    public function set($key, $data)
    {
        $this->extraKeys[$key] = $data;
    }
    
    public function render(\Doc $doc)
    {
        $this->document = $doc;
        
        if ($doc->id > 0) {
            DocManager::cache()->addDocument($doc);
        }
        return $this->internalRender();
    }
}
