<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class IntAttributeValue extends FormatAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);
        if ($this->value !== null) {
            $this->value = intval($v);
        }
    }
}
