<?php

namespace Anakeen\Router;

class Exception extends \Anakeen\Exception
{
    protected $httpStatus = 400;
    protected $httpMessage = "Anakeen Router Exception";
}
