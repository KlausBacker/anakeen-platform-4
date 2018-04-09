<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Utils;

class XDOMDocumentException extends \Exception
{
    public $libXMLErrors = array();
    
    public function __construct($message, &$libXMLErrors = array())
    {
        $this->message = $message;
        if (count($libXMLErrors) <= 0) {
            $libXMLErrors[] = new \libXMLError();
        }
        $this->libXMLErrors = $libXMLErrors;
    }
}

