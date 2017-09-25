<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class DefaultConfigViewRender extends \Dcp\Ui\DefaultView
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

}