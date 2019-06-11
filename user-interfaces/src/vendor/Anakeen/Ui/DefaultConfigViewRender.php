<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use Anakeen\Ui\RenderOptions;

class DefaultConfigViewRender extends \Anakeen\Ui\DefaultView
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->commonOption()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::upPosition);
        return $options;
    }

    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        return parent::getCssReferences();
    }
}
