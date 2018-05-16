<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class StandardAttributeValue
{
    public $value;
    public $displayValue;

    /**
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oa
     * @param                                              $v
     */
    public function __construct($oa, $v)
    {
        $this->value = ($v === '') ? null : $v;
        $this->displayValue = $v;
    }
}
