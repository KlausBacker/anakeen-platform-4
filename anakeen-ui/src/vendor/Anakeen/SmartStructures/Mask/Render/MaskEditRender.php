<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Mask\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Mask as myAttributes;

class MaskEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        /*$options->arrayAttribute(myAttributes::msk_t_contain)->disableRowAdd(true);
        $options->arrayAttribute(myAttributes::msk_t_contain)->disableRowMove(true);
        $options->arrayAttribute(myAttributes::msk_t_contain)->disableRowDel(true);*/
        return $options;
    }

    /*
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js["dduiMask"] = "uiAssets/Families/mask/maskEdit.js?ws=" . $version;
        return $js;
    }
    */
}