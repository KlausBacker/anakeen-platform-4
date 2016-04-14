<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Process Class
 * @author Anakeen
 */

require_once 'class/Class.WiffCommon.php';

class Process extends WiffCommon
{
    /**
     * @var string process description xml
     */
    public $xmlStr;
    public $name;
    public $attributes;
    public $label;
    public $help;
    public $type;
    /**
     * @var Phase $phase
     */
    public $phase;
    
    public $errorMessage;
    
    public function __construct($xmlStr = "", $phase)
    {
        
        $this->xmlStr = $xmlStr;
        $this->attributes = array();
        $this->type = "";
        $this->phase = $phase;
        
        $doc = new DOMDocument();
        $ret = $doc->loadXML($this->xmlStr);
        if ($ret === false) {
            return;
        }
        /**
         * @var DOMElement $node
         */
        $node = $doc->childNodes->item(0);
        if ($node === null) {
            return;
        }
        
        $this->name = $node->nodeName;
        foreach ($node->attributes as $attr) {
            $this->attributes[$attr->name] = $attr->value;
        }
        $elmt = $node->getElementsByTagName('label')->item(0);
        $this->label = isset($elmt) ? $elmt->nodeValue : '';
        $elmt = $node->getElementsByTagName('help')->item(0);
        $this->help = isset($elmt) ? $elmt->nodeValue : '';
        if ($this->label == "") {
            $this->label = $this->computeLabel();
        }
        
        return;
    }
    
    private function computeLabel()
    {
        if ($this->name == 'check') {
            if ($this->attributes['type'] == 'syscommand') {
                $label = sprintf('Check system command %s', $this->attributes['command']);
            } elseif ($this->attributes['type'] == 'phpfunction') {
                $label = sprintf('Check php function %s', $this->attributes['function']);
            } elseif ($this->attributes['type'] == 'phpclass') {
                $label = sprintf('Check php class %s', $this->attributes['class']);
            } elseif ($this->attributes['type'] == 'pearmodule') {
                $label = sprintf('Check pear module %s%s', $this->attributes['class'], isset($this->attributes['include']) ? sprintf(' in %s', $this->attributes['include']) : '');
            } elseif ($this->attributes['type'] == 'apachemodule') {
                $label = sprintf('Check apache module %s', $this->attributes['module']);
            } else {
                $label = sprintf("Check %s", $this->attributes['type']);
            }
        } elseif ($this->name == 'process') {
            $label = sprintf('Process %s', $this->attributes['command']);
        } elseif ($this->name == 'download') {
            $label = sprintf('Download %s', $this->attributes['href']);
        } else {
            $label = sprintf("<unknwon>");
        }
        
        return $label;
    }
    /**
     * Execute process
     * Use getErrorMessage() to retrieve error
     * @return boolean success
     */
    public function execute()
    {
        require_once ('class/Class.WIFF.php');
        require_once ('lib/Lib.Wcontrol.php');

        putenv("WIFF_CONTEXT_NAME=" . $this->phase->module->getContext()->name);
        putenv("WIFF_CONTEXT_ROOT=" . $this->phase->module->getContext()->root);
        
        $cwd = getcwd();
        
        $ret = chdir($this->phase->module->getContext()->root);
        if ($ret === false) {
            return array(
                'ret' => false,
                'output' => sprintf("Could not chdir to %s.", $this->phase->module->getContext()->root)
            );
        }
        
        $result = wcontrol_eval_process($this);
        
        chdir($cwd);
        
        if (!$result['ret']) {
            $this->log(LOG_ERR, $result['output']);
        }
        
        return $result;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getAttribute($attrName)
    {
        if (array_key_exists($attrName, $this->attributes)) {
            return $this->attributes[$attrName];
        }
        return "";
    }

}
