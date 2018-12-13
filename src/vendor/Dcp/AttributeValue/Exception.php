<?php

namespace Dcp\AttributeValue;

class Exception extends \Anakeen\Exception
{
    public $originalError = "";
    public $attributeId = "";
    public $index = -1;
}
