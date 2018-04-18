<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\DocManager;

class IntAttributeValue extends FormatAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);
        $this->value = intval($v);
    }
}
