<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\DocManager;

class UnknowAttributeValue extends StandardAttributeValue
{
    /**
     * noAccessAttributeValue constructor.
     * @param string $v
     */
    public function __construct($v)
    {
        $this->value = ($v === '') ? null : $v;
        $this->displayValue = $v;
    }
}
