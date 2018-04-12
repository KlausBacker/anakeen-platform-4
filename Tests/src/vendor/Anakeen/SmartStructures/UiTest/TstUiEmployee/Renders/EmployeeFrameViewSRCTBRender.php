<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use \SmartStructure\Attributes\Tst_ddui_employee as myAttribute;

class EmployeeFrameViewSRCTBRender extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        EmployeeFrameViewSRCLRRender::setColumn($options, \Dcp\Ui\FrameRenderOptions::topBottomDirection);

        return $options;
    }
}
