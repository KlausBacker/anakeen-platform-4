<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class FormatAttributeValue extends StandardAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v)
    {
        $this->value = ($v === '') ? null : $v;
        if ($oa->format) {
            $this->displayValue = sprintf($oa->format, $v);
        } else {
            $this->displayValue = $v;
        }
    }
}
