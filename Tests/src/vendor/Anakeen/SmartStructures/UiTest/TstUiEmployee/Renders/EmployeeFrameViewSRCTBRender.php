<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Tst_ddui_employee as myAttribute;

class EmployeeFrameViewSRCTBRender extends \Dcp\Ui\DefaultView
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);

        EmployeeFrameViewSRCLRRender::setColumn($options, \Dcp\Ui\FrameRenderOptions::topBottomDirection);

        return $options;
    }
}
