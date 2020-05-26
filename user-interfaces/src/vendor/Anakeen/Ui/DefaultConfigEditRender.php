<?php

namespace Anakeen\Ui;

class DefaultConfigEditRender extends \Anakeen\Ui\DefaultEdit
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);
        $options->commonOption()->setLabelPosition(\Anakeen\Ui\CommonRenderOptions::upPosition);
        $options->commonOption()->displayDeleteButton(true);
        return $options;
    }
}
