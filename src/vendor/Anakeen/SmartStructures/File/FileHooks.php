<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * document to attach file
 */

namespace Anakeen\SmartStructures\File;

use Anakeen\SmartHooks;

class FileHooks extends \Anakeen\SmartElement

{
    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            $filename = $this->vault_filename("FI_FILE");

            if ($this->getRawValue("FI_TITLEW") == "") {
                $this->SetValue("FI_TITLE", $filename);
            } else {
                $this->SetValue("FI_TITLE", $this->getRawValue("FI_TITLEW"));
            }
        });
    }
}
