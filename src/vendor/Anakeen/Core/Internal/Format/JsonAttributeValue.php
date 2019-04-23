<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class JsonAttributeValue extends StandardAttributeValue
{

    public function __construct(\Anakeen\Core\SmartStructure\NormalAttribute $oa, $v)
    {
        parent::__construct($oa, $v);

        $this->displayValue = json_encode(json_decode($v), JSON_PRETTY_PRINT);
    }
}
