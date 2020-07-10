<?php

namespace Anakeen\SmartCriteria;

class Exception extends \Anakeen\Exception
{
    protected $httpStatus = 400;
    protected $httpMessage = "Anakeen Smart Criteria Exception";
}
