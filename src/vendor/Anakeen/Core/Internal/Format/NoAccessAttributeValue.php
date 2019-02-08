<?php

namespace Anakeen\Core\Internal\Format;

use \Anakeen\Core\SEManager;

class NoAccessAttributeValue extends StandardAttributeValue
{
    public $visible = true;

    /**
     * noAccessAttributeValue constructor.
     * @param string $v
     */
    public function __construct($v)
    {
        $this->value = '';
        $this->displayValue = $v;
    }
}
