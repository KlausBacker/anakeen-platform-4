<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeFrameEditSRCLRRender extends \Anakeen\Ui\DefaultEdit
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        EmployeeFrameViewSRCLRRender::setColumn($options);

        return $options;
    }
}
