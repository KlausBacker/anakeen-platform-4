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
use SmartStructure\Fields\Image as ImageFields;

class ImageHooks extends \Anakeen\SmartElement
{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            return $this->setValue("IMG_TITLE", $this->vaultFilename(ImageFields::img_file));
        });

        /*
        $this->getHooks()->addListener(SmartHooks::PRESTORE, function () {
            $this->icon = $this->getRawValue(ImageFields::img_file);
        });
        */
    }
}
