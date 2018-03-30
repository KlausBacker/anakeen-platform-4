<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * document to attach file
 */
namespace Anakeen\SmartStructures\File;

class FileHooks extends \Anakeen\SmartStructures\Document
{
    public function postStore()
    {
        $filename = $this->vault_filename("FI_FILE");
        /* to not view extension file
        $pos = strrpos($filename , ".");
        if ($pos !== false) {
        $filename=substr($filename,0,$pos);
        }
        */
        if ($this->getRawValue("FI_TITLEW") == "") {
            $this->SetValue("FI_TITLE", $filename);
        } else {
            $this->SetValue("FI_TITLE", $this->getRawValue("FI_TITLEW"));
        }
    }
}
