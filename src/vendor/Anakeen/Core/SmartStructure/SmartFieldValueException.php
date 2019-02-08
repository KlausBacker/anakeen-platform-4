<?php

namespace Anakeen\Core\SmartStructure;

class SmartFieldValueException extends \Anakeen\Exception
{
    public $originalError = "";
    public $attributeId = "";
    public $index = -1;
}
