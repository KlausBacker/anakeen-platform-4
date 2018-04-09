<?php

namespace Dcp\AttributeValue;

class Exception extends \Dcp\Exception
{
    public $originalError = "";
    public $attributeId = "";
    public $index = -1;
}
