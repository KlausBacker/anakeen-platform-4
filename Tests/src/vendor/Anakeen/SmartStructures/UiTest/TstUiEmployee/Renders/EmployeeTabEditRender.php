<?php

namespace Anakeen\SmartStructures\UiTest\TstUiEmployee\Renders;

use \SmartStructure\Attributes\Tst_ddui_employee as myAttribute;

class EmployeeTabEditRender extends \Dcp\Ui\DefaultEdit
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);

        EmployeeTabViewRender::setColumn($options);


        return $options;
    }
}