<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class EnumAttributeValue extends StandardAttributeValue
{
    public $exists = true;

    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v)
    {
        $this->value = ($v === '') ? null : $v;
        if ($v !== null && $v !== '') {
            $this->displayValue = $oa->getEnumLabel($v);
            $this->exists = $oa->existEnum($v);
        }
    }
}
