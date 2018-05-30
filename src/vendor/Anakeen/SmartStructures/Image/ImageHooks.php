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

use Anakeen\SmartHooks;

class ImageHooks extends \Anakeen\SmartElement

{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            return $this->setValue("IMG_TITLE", $this->vault_filename("IMG_FILE"));
        });
    }
}
