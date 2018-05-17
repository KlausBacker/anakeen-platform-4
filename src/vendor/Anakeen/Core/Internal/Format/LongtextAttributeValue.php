<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class LongtextAttributeValue extends FormatAttributeValue
{
    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v, $multipleLongtextCr = "\n")
    {
        if ($oa->inArray()) {
            $v = str_replace("<BR>", $multipleLongtextCr, $v);
        }
        parent::__construct($oa, $v);
    }
}
