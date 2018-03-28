<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

class DefaultConfigEditRender extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->commonOption()->setLabelPosition(\Dcp\Ui\CommonRenderOptions::upPosition);
        $options->commonOption()->displayDeleteButton(true);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        return parent::getCssReferences();
    }
}