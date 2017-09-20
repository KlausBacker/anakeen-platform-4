<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class defaultConfigViewRender extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        return parent::getCssReferences();
    }

//    public function getMenu(\Doc $document)
//    {
//        $menu = parent::getMenu($document);
//        $other = new \Dcp\Ui\ListMenu("timer-otherMenu", ___("Autres","ddui timer"));
//        $menu->appendElement($other);
//
//        return $menu;
//    }
}