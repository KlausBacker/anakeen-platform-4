<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Anakeen\Ui\CommonRenderOptions;
use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeLeftRender extends EmployeeEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        $options->commonOption()->setLabelPosition(CommonRenderOptions::leftPosition);

        return $options;
    }
}
