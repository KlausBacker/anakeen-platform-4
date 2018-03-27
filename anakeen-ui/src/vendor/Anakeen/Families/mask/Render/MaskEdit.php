<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use \Dcp\AttributeIdentifiers\MASK as myAttributes;

class MaskEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        /*$options->arrayAttribute(myAttributes::msk_t_contain)->disableRowAdd(true);
        $options->arrayAttribute(myAttributes::msk_t_contain)->disableRowMove(true);
        $options->arrayAttribute(myAttributes::msk_t_contain)->disableRowDel(true);*/
        return $options;
    }

    /*
    public function getJsReferences(\Doc $document = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $js["dduiMask"] = "uiAssets/Families/mask/maskEdit.js?ws=" . $version;
        return $js;
    }
    */
}
