<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Image document
 *
 */
namespace Anakeen\SmartStructures\Image;

class ImageHooks extends \Anakeen\SmartStructures\Document
{

    
    public function postStore()
    {
        return $this->SetValue("IMG_TITLE", $this->vault_filename("IMG_FILE"));
    }
}
